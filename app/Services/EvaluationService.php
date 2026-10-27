<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\DailyEvaluationScore;
use App\Models\DailyTaskSheet;
use App\Models\Employee;
use App\Models\EvaluationCriterion;
use App\Models\EvaluationSheet;
use App\Models\HrSetting;
use App\Models\MonthlyEvaluation;
use App\Models\ScheduleDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EvaluationService
{
    /**
     * Génère ou synchronise l'évaluation mensuelle d'un collaborateur pour un mois et une année donnés.
     */
    public function generateForEmployee(Employee $employee, int $year, int $month, ?int $evaluatorId = null): MonthlyEvaluation
    {
        $settings = HrSetting::current();
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $today = today();

        // Si le mois demandé est le mois en cours, limiter les calculs jusqu'à aujourd'hui
        $effectiveEnd = $endDate->greaterThan($today) ? $today : $endDate;

        // Récupération ou création de l'évaluation mensuelle
        $evaluation = MonthlyEvaluation::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'year' => $year,
                'month' => $month,
            ],
            [
                'is_trial' => MonthlyEvaluation::isTrialMonth($year, $month),
                'evaluation_date' => Carbon::createFromDate($year, $month, min(28, (int) $settings->evaluation_day_of_month)),
                'status' => 'en_cours',
                'workflow_step' => 'en_evaluation',
                'evaluator_id' => $evaluatorId,
            ]
        );

        $currentDate = $startDate->copy();
        $workingDaysCount = 0;

        while ($currentDate->lessThanOrEqualTo($effectiveEnd)) {
            $dateStr = $currentDate->toDateString();

            // 1. Détermination si c'est un jour ouvré selon le planning
            $scheduleDay = ScheduleDay::whereHas('workSchedule', function ($q) use ($employee) {
                $q->where('employee_id', $employee->id);
            })->whereDate('date', $dateStr)->first();

            $isWorkingDay = $scheduleDay ? (bool) $scheduleDay->is_working_day : $settings->isStandardWorkingDay($currentDate->dayOfWeekIso);

            if ($isWorkingDay) {
                $workingDaysCount++;
            }

            // 2. Pointage & Ponctualité
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $dateStr)
                ->first();

            $punctualityScore = 0.00;
            $punctualityNotes = 'Non pointé';
            if ($attendance && $attendance->isCheckedIn()) {
                $punctualityScore = (float) $attendance->punctuality_score;
                $punctualityNotes = $attendance->notes ?: ($attendance->status === 'present' ? 'À l\'heure' : 'Retard');
            } elseif (! $isWorkingDay) {
                $punctualityScore = 0.00;
                $punctualityNotes = 'Jour de repos';
            }

            // 3. Tâches validées
            $taskSheet = DailyTaskSheet::where('employee_id', $employee->id)
                ->whereDate('date', $dateStr)
                ->first();

            $tasksAssigned = 0;
            $tasksValidated = 0;
            $tasksScore = 0.00;

            if ($taskSheet && is_array($taskSheet->tasks) && count($taskSheet->tasks) > 0) {
                $tasksAssigned = count($taskSheet->tasks);
                foreach ($taskSheet->tasks as $t) {
                    if (($t['validation_status'] ?? '') === 'valide') {
                        $tasksValidated++;
                    }
                }
                $rate = $tasksAssigned > 0 ? ($tasksValidated / $tasksAssigned) : 0;
                $tasksScore = round(((float) $settings->weight_tasks) * $rate, 3);
            }

            // 4. Enregistrement ou mise à jour du score journalier
            $dailyScore = DailyEvaluationScore::firstOrNew([
                'employee_id' => $employee->id,
                'date' => $dateStr,
            ]);

            $dailyScore->fill([
                'monthly_evaluation_id' => $evaluation->id,
                'is_working_day' => $isWorkingDay,
                'punctuality_score' => $punctualityScore,
                'punctuality_notes' => $punctualityNotes,
                'tasks_assigned_count' => $tasksAssigned,
                'tasks_validated_count' => $tasksValidated,
                'tasks_score' => $tasksScore,
                'teamwork_score' => $dailyScore->teamwork_score ?? 0.00,
            ]);
            $dailyScore->save();

            $currentDate->addDay();
        }

        $evaluation->update([
            'total_working_days' => $workingDaysCount,
        ]);

        // 5. Calcul de la note du mois selon le référentiel (A1 à partir des tâches validées, autres critères depuis les fiches)
        $evaluation->recalculateScores();

        return $evaluation;
    }

    /**
     * Génère la campagne d'évaluation pour l'ensemble des employés actifs.
     */
    public function generateAllForMonth(int $year, int $month, ?int $evaluatorId = null): int
    {
        $employees = Employee::where('status', 'actif')->get();
        $count = 0;

        foreach ($employees as $employee) {
            $this->generateForEmployee($employee, $year, $month, $evaluatorId);
            $count++;
        }

        return $count;
    }

    /**
     * Attribue la note d'esprit d'équipe (0.00 à 0.10) avec justification obligatoire si faible (<= 0.03).
     */
    public function setDailyTeamworkScore(
        DailyEvaluationScore $dailyScore,
        float $score,
        ?string $comment = null,
        ?int $evaluatorId = null
    ): DailyEvaluationScore {
        $settings = HrSetting::current();
        $maxTeamwork = (float) $settings->weight_teamwork;

        if ($score < 0 || $score > $maxTeamwork) {
            throw new InvalidArgumentException("La note d'esprit d'équipe doit être comprise entre 0.00 et {$maxTeamwork}.");
        }

        if ($score <= 0.03 && empty(trim($comment ?? ''))) {
            throw new InvalidArgumentException('Un commentaire explicatif est obligatoire pour toute note d\'esprit d\'équipe inférieure ou égale à 0.03.');
        }

        $dailyScore->update([
            'teamwork_score' => $score,
            'teamwork_comment' => $comment,
            'evaluated_by' => $evaluatorId ?: auth()->id(),
        ]);

        if ($dailyScore->monthlyEvaluation) {
            $dailyScore->monthlyEvaluation->recalculateScores();
        }

        return $dailyScore;
    }

    /**
     * Retourne les statistiques consolidées pour le Dashboard RH (barème /20).
     */
    public function getCompanyDashboardStats(int $year, int $month): array
    {
        $totalActiveEmployees = Employee::where('status', 'actif')->count();
        $evaluations = MonthlyEvaluation::with(['employee'])
            ->where('year', $year)
            ->where('month', $month)
            ->get();

        $evaluatedCount = $evaluations->count();
        $completedCount = $evaluations->where('status', 'valide')->count();
        $pendingCount = $evaluations->where('status', 'en_cours')->count();
        $lockedCount = $evaluations->where('is_locked', true)->count();

        // Les moyennes ne portent que sur les membres déjà notés
        $evaluations = $evaluations->filter(fn (MonthlyEvaluation $e) => $e->isScored())->values();

        $averageScore20 = $evaluations->isNotEmpty()
            ? round((float) $evaluations->avg(fn ($e) => (float) ($e->final_score_20 ?: ($e->final_score_30 / 1.5))), 2)
            : 0.00;

        $averageScore30 = $evaluations->isNotEmpty()
            ? round((float) $evaluations->avg('final_score_30'), 2)
            : 0.00;

        // Moyenne par département / service
        $byDepartment = $evaluations->groupBy(function ($eval) {
            return $eval->employee->department ?: 'Général';
        })->map(function (Collection $deptEvals) {
            $avg20 = round((float) $deptEvals->avg(fn ($e) => (float) ($e->final_score_20 ?: ($e->final_score_30 / 1.5))), 2);
            $avg30 = round((float) $deptEvals->avg('final_score_30'), 2);

            return [
                'count' => $deptEvals->count(),
                'avg_score' => $avg20,
                'avg_score_20' => $avg20,
                'avg_score_30' => $avg30,
                'highest' => round((float) $deptEvals->max(fn ($e) => (float) ($e->final_score_20 ?: ($e->final_score_30 / 1.5))), 2),
                'lowest' => round((float) $deptEvals->min(fn ($e) => (float) ($e->final_score_20 ?: ($e->final_score_30 / 1.5))), 2),
            ];
        });

        // Employés avec progression positive vs mois précédent
        $progressingEmployees = $evaluations->where('score_progress', '>', 0)
            ->sortByDesc('score_progress')
            ->values();

        // Membres sous la moyenne (plan de progrès du référentiel) ou en forte baisse
        $needsFollowUpEmployees = $evaluations->filter(function (MonthlyEvaluation $eval) {
            return $eval->isScored()
                && ((float) $eval->final_score_20 < 10.00 || (float) $eval->score_progress <= -1.50);
        })->sortBy(fn ($e) => (float) ($e->final_score_20 ?: ($e->final_score_30 / 1.5)))->values();

        // Évolution des moyennes générales sur les 6 derniers mois (Graphique de performance)
        $historicalTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::createFromDate($year, $month, 1)->subMonths($i);
            $histYear = $dt->year;
            $histMonth = $dt->month;

            $monthEvals = MonthlyEvaluation::where('year', $histYear)
                ->where('month', $histMonth)
                ->whereNotNull('appreciation')
                ->get();

            $avg20 = $monthEvals->isNotEmpty()
                ? round((float) $monthEvals->avg(fn ($e) => (float) ($e->final_score_20 ?: ($e->final_score_30 / 1.5))), 2)
                : 0.00;

            $historicalTrend[] = [
                'label' => $dt->translatedFormat('M y'),
                'avg' => $avg20,
                'avg_20' => $avg20,
                'avg_30' => round((float) ($monthEvals->avg('final_score_30') ?: 0.00), 2),
                'average' => $avg20,
            ];
        }

        return [
            'totalActiveEmployees' => $totalActiveEmployees,
            'evaluatedCount' => $evaluatedCount,
            'completedCount' => $completedCount,
            'pendingCount' => $pendingCount,
            'lockedCount' => $lockedCount,
            'averageScore' => $averageScore20,
            'averageScore20' => $averageScore20,
            'averageScore30' => $averageScore30,
            'companyAverage' => $averageScore20,
            'byDepartment' => $byDepartment,
            'departmentAverages' => $byDepartment,
            'progressingEmployees' => $progressingEmployees,
            'progressingCount' => $progressingEmployees->count(),
            'needsFollowUpEmployees' => $needsFollowUpEmployees,
            'followUpCount' => $needsFollowUpEmployees->count(),
            'historicalTrend' => $historicalTrend,
        ];
    }

    /**
     * Classement du mois, membre du mois et récompense du trimestre selon le référentiel.
     *
     * @return array{topOverall: Collection<int, MonthlyEvaluation>, topProgression: Collection<int, MonthlyEvaluation>, memberOfTheMonth: ?MonthlyEvaluation, quarter: array{label: string, rewarded: Collection<int, array{employee: Employee, average: float, months: int}>}}
     */
    public function getInternalRanking(int $year, int $month, ?string $department = null): array
    {
        $query = MonthlyEvaluation::with(['employee'])
            ->where('year', $year)
            ->where('month', $month)
            ->whereNotNull('appreciation');

        if ($department) {
            $query->whereHas('employee', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }

        $evaluations = $query->get()->sortByDesc(fn (MonthlyEvaluation $e) => (float) $e->final_score_20)->values();

        return [
            'topOverall' => $evaluations->take(10)->values(),
            'topProgression' => $evaluations->where('score_progress', '>', 0)->sortByDesc('score_progress')->take(5)->values(),
            'memberOfTheMonth' => $this->memberOfTheMonth($year, $month),
            'quarter' => $this->quarterlyRewards($year, $month),
        ];
    }

    /**
     * Membre du mois : la meilleure note du mois, si elle est d'au moins 14/20 (aucun titre pendant le mois d'essai).
     */
    public function memberOfTheMonth(int $year, int $month): ?MonthlyEvaluation
    {
        if (MonthlyEvaluation::isTrialMonth($year, $month)) {
            return null;
        }

        return MonthlyEvaluation::with('employee')
            ->where('year', $year)
            ->where('month', $month)
            ->whereNotNull('appreciation')
            ->where('final_score_20', '>=', 14)
            ->orderByDesc('final_score_20')
            ->first();
    }

    /**
     * Mois du trimestre d'évaluation contenant le mois donné. Les trimestres suivent le calendrier du référentiel :
     * le premier va de novembre 2026 à janvier 2027, puis février–avril, mai–juillet et août–octobre.
     *
     * @return list<array{year: int, month: int}>
     */
    public function quarterMonths(int $year, int $month): array
    {
        $monthsSinceNovember = ($month - 11 + 12) % 12;
        $start = Carbon::createFromDate($month >= 11 ? $year : $year - 1, 11, 1)
            ->addMonths($monthsSinceNovember - $monthsSinceNovember % 3);

        return collect(range(0, 2))
            ->map(fn (int $offset) => ['year' => $start->copy()->addMonths($offset)->year, 'month' => $start->copy()->addMonths($offset)->month])
            ->all();
    }

    /**
     * Récompense du trimestre : les 3 meilleures moyennes du trimestre, d'au moins 12/20 (mois d'essai exclu).
     *
     * @return array{label: string, rewarded: Collection<int, array{employee: Employee, average: float, months: int}>}
     */
    public function quarterlyRewards(int $year, int $month): array
    {
        $months = $this->quarterMonths($year, $month);

        $evaluations = MonthlyEvaluation::with('employee')
            ->where('is_trial', false)
            ->whereNotNull('appreciation')
            ->where(function ($query) use ($months) {
                foreach ($months as $period) {
                    $query->orWhere(fn ($q) => $q->where('year', $period['year'])->where('month', $period['month']));
                }
            })
            ->get();

        $rewarded = $evaluations->groupBy('employee_id')
            ->map(fn (Collection $memberEvaluations) => [
                'employee' => $memberEvaluations->first()->employee,
                'average' => round((float) $memberEvaluations->avg(fn (MonthlyEvaluation $e) => (float) $e->final_score_20), 2),
                'months' => $memberEvaluations->count(),
            ])
            ->filter(fn (array $row) => $row['average'] >= 12)
            ->sortByDesc('average')
            ->take(3)
            ->values();

        $first = Carbon::createFromDate($months[0]['year'], $months[0]['month'], 1);
        $last = Carbon::createFromDate($months[2]['year'], $months[2]['month'], 1);

        return [
            'label' => ucfirst($first->translatedFormat('F Y')).' – '.$last->translatedFormat('F Y'),
            'rewarded' => $rewarded,
        ];
    }

    /**
     * Enregistre la fiche d'un évaluateur (notes de 1 à 5, null = non concerné) puis recalcule la note du mois.
     *
     * @param  array<int, array{score?: int|string|null, observation?: string|null}>  $scores  Notes indexées par identifiant de critère
     */
    public function saveSheet(MonthlyEvaluation $evaluation, User $evaluator, string $evaluatorGroup, array $scores): EvaluationSheet
    {
        $sheet = DB::transaction(function () use ($evaluation, $evaluator, $evaluatorGroup, $scores) {
            $sheet = EvaluationSheet::updateOrCreate(
                [
                    'monthly_evaluation_id' => $evaluation->id,
                    'evaluator_id' => $evaluator->id,
                    'evaluator_group' => $evaluatorGroup,
                ],
                ['submitted_at' => now()]
            );

            $criteria = EvaluationCriterion::referential()->get()
                ->filter(fn (EvaluationCriterion $criterion) => $criterion->isRatableBy($evaluatorGroup));

            foreach ($criteria as $criterion) {
                $score = $scores[$criterion->id]['score'] ?? null;

                $sheet->scores()->updateOrCreate(
                    ['evaluation_criterion_id' => $criterion->id],
                    [
                        'score' => $score === null || $score === '' ? null : (int) $score,
                        'observation' => $scores[$criterion->id]['observation'] ?? null,
                    ]
                );
            }

            return $sheet;
        });

        $evaluation->recalculateScores();

        return $sheet;
    }
}
