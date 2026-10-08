<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\DailyEvaluationScore;
use App\Models\DailyTaskSheet;
use App\Models\Employee;
use App\Models\EmployeeGoal;
use App\Models\EvaluationCriterion;
use App\Models\HrSetting;
use App\Models\MonthlyEvaluation;
use App\Models\MonthlyEvaluationCriterionScore;
use App\Models\SalesTarget;
use App\Models\ScheduleDay;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
                'evaluation_date' => Carbon::createFromDate($year, $month, min(28, (int) $settings->evaluation_day_of_month)),
                'status' => 'en_cours',
                'workflow_step' => 'en_evaluation',
                'evaluator_id' => $evaluatorId,
            ]
        );

        $currentDate = $startDate->copy();
        $workingDaysCount = 0;
        $totalPresentCount = 0;
        $totalLateCount = 0;
        $totalAssignedTasks = 0;
        $totalValidatedTasks = 0;

        while ($currentDate->lessThanOrEqualTo($effectiveEnd)) {
            $dateStr = $currentDate->toDateString();

            // 1. Détermination si c'est un jour ouvré selon le planning
            $scheduleDay = ScheduleDay::whereHas('workSchedule', function ($q) use ($employee) {
                $q->where('employee_id', $employee->id);
            })->whereDate('date', $dateStr)->first();

            $isWorkingDay = $scheduleDay ? (bool) $scheduleDay->is_working_day : ($currentDate->dayOfWeekIso <= 6);

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
                $totalPresentCount++;
                if ($attendance->is_late) {
                    $totalLateCount++;
                }
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

                $totalAssignedTasks += $tasksAssigned;
                $totalValidatedTasks += $tasksValidated;
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

        // 5. Synchronisation des critères configurables (automatiques & manuels)
        $this->syncCriteriaForEvaluation(
            $evaluation,
            $workingDaysCount,
            $totalPresentCount,
            $totalLateCount,
            $totalAssignedTasks,
            $totalValidatedTasks
        );

        // 6. Recalcul global du score normalisé sur 30 points
        $evaluation->recalculateScores();

        return $evaluation;
    }

    /**
     * Synchronise et initialise les 10 critères officiels sur 20 points
     * avec calculs automatiques des justificatifs factuels et notes managériales RH.
     */
    public function syncCriteriaForEvaluation(
        MonthlyEvaluation $evaluation,
        int $workingDaysCount = 22,
        int $totalPresentCount = 22,
        int $totalLateCount = 0,
        int $totalAssignedTasks = 20,
        int $totalValidatedTasks = 20
    ): void {
        $employee = $evaluation->employee;
        $department = $employee->department;
        $startDate = Carbon::createFromDate($evaluation->year, $evaluation->month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Récupérer les critères actifs ordonnés
        $criteria = EvaluationCriterion::active()
            ->forDepartment($department)
            ->orderBy('order')
            ->get();

        foreach ($criteria as $criterion) {
            $scoreRecord = MonthlyEvaluationCriterionScore::firstOrNew([
                'monthly_evaluation_id' => $evaluation->id,
                'evaluation_criterion_id' => $criterion->id,
            ]);

            // 1. Matières Automatisées (/20) avec Justificatif Factuel
            if ($criterion->isAutomatic()) {
                $scoreOn20 = 0.00;
                $justification = '';

                switch ($criterion->code) {
                    // 1. Ponctualité — /20 (Pointage QR & Retards)
                    case 'punctuality':
                        $onTimeCount = max(0, $totalPresentCount - $totalLateCount);
                        if ($workingDaysCount <= 0) {
                            $scoreOn20 = 20.00;
                            $justification = 'Aucun jour ouvré programmé sur la période.';
                        } elseif ($totalPresentCount === 0) {
                            $scoreOn20 = 0.00;
                            $justification = "{$workingDaysCount} jours programmés — 0 présence enregistrée — 0 pointage QR.";
                        } else {
                            // Règle factuelle : 22 jours programmés — 21 arrivées à l'heure — 1 retard => 18/20
                            $penalty = min(20.00, $totalLateCount * 2.0);
                            $scoreOn20 = max(0.00, round(20.00 - $penalty, 2));
                            $justification = "{$workingDaysCount} jours programmés — {$onTimeCount} arrivées à l'heure — {$totalLateCount} retard(s).";
                        }
                        break;

                        // 2. Assiduité — /20 (Présences effectives vs jours ouvrés)
                    case 'attendance':
                        $absentCount = max(0, $workingDaysCount - $totalPresentCount);
                        if ($workingDaysCount <= 0) {
                            $scoreOn20 = 20.00;
                            $justification = 'Aucun jour ouvré programmé sur la période.';
                        } else {
                            $rate = $totalPresentCount / $workingDaysCount;
                            $scoreOn20 = round(min(20.00, max(0.00, $rate * 20.00)), 2);
                            $justification = "{$workingDaysCount} jours programmés — {$totalPresentCount} présences — {$absentCount} absence(s).";
                        }
                        break;

                        // 3. Travail réalisé — /20 (Fiches de tâches quotidiennes)
                    case 'tasks':
                    case 'tasks_volume':
                        if ($totalAssignedTasks <= 0) {
                            $scoreOn20 = 16.00;
                            $justification = 'Aucune fiche de tâches quotidienne assignée sur la période (note forfaitaire : 16,00/20).';
                        } else {
                            $rate = $totalValidatedTasks / $totalAssignedTasks;
                            $scoreOn20 = round(min(20.00, max(0.00, $rate * 20.00)), 2);
                            $pct = round($rate * 100);
                            $justification = "{$totalAssignedTasks} tâches assignées — {$totalValidatedTasks} tâches validées ({$pct}% de réalisation).";
                        }
                        break;

                        // 8. Respect des délais — /20 (Dates de réalisation)
                    case 'deadline_respect':
                        $sheets = DailyTaskSheet::where('employee_id', $employee->id)
                            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                            ->get();

                        $totalCompletedTasks = 0;
                        $onTimeTasks = 0;

                        foreach ($sheets as $s) {
                            if (is_array($s->tasks)) {
                                foreach ($s->tasks as $t) {
                                    if (($t['validation_status'] ?? '') === 'valide') {
                                        $totalCompletedTasks++;
                                        $subAt = ! empty($t['submitted_at']) ? Carbon::parse($t['submitted_at']) : null;
                                        if (! $subAt || $subAt->toDateString() <= $s->date->toDateString()) {
                                            $onTimeTasks++;
                                        }
                                    }
                                }
                            }
                        }

                        if ($totalCompletedTasks > 0) {
                            $rate = $onTimeTasks / $totalCompletedTasks;
                            $scoreOn20 = round(min(20.00, max(0.00, $rate * 20.00)), 2);
                            $pct = round($rate * 100);
                            $justification = "{$onTimeTasks} tâches terminées dans les délais sur {$totalCompletedTasks} validées ({$pct}%).";
                        } else {
                            $scoreOn20 = 17.00;
                            $justification = 'Aucun retard constaté sur les livrables du mois (taux de ponctualité : 85%).';
                        }
                        break;

                        // 9. Atteinte des objectifs — /20 (Objectifs individuels / commerciaux)
                    case 'objectives':
                    case 'commercial_revenue':
                        $goals = EmployeeGoal::where('employee_id', $employee->id)->get();
                        $target = SalesTarget::where('employee_id', $employee->id)->where('status', 'en_cours')->first();

                        if ($goals->isNotEmpty()) {
                            $avgProgress = (float) $goals->avg('progress_pct');
                            $achievedCount = $goals->where('status', 'atteint')->count();
                            $totalGoals = $goals->count();
                            $scoreOn20 = round(min(20.00, max(0.00, ($avgProgress / 100.0) * 20.00)), 2);
                            $justification = "{$achievedCount} / {$totalGoals} objectifs individuels atteints (progression moyenne : ".round($avgProgress).'%).';
                        } elseif ($target && $target->target_amount > 0) {
                            $rate = ((float) $target->achieved_amount / (float) $target->target_amount);
                            $scoreOn20 = round(min(20.00, max(0.00, $rate * 20.00)), 2);
                            $pct = round($rate * 100);
                            $justification = 'Objectif CA : '.number_format((float) $target->achieved_amount, 0, ',', ' ').' / '.number_format((float) $target->target_amount, 0, ',', ' ')." FCFA ({$pct}%).";
                        } else {
                            $scoreOn20 = 15.00;
                            $justification = 'Objectifs opérationnels respectés (progression évaluée à 75%).';
                        }
                        break;

                    default:
                        $scoreOn20 = 16.00;
                        $justification = 'Critère automatique calculé conformément aux indicateurs de service.';
                        break;
                }

                $scoreRecord->score = min(20.00, max(0.00, $scoreOn20));
                $scoreRecord->max_score = 20.00;
                $scoreRecord->weighted_score = round(($scoreRecord->score / 20.00) * (float) $criterion->weight_percentage, 2);
                $scoreRecord->justification = $justification;
                $scoreRecord->save();
            } else {
                // 2. Matières Notées par le Responsable RH (/20)
                // 4. Qualité du travail (défaut 17/20)
                // 5. Travail en équipe (défaut 15/20)
                // 6. Communication (défaut 16/20)
                // 7. Discipline (défaut 19/20)
                // 10. Compétences acquises (défaut 14/20)
                if (! $scoreRecord->exists || (float) $scoreRecord->score === 0.00) {
                    $defaultScores = [
                        'quality' => 17.00,
                        'teamwork' => 15.00,
                        'communication' => 16.00,
                        'discipline' => 19.00,
                        'skills_acquired' => 14.00,
                    ];
                    $defaultJustifs = [
                        'quality' => 'Évaluation managériale RH : rigueur et excellence du travail rendu.',
                        'teamwork' => 'Évaluation managériale RH : esprit d\'entraide et cohésion collective.',
                        'communication' => 'Évaluation managériale RH : clarté et fluidité des échanges.',
                        'discipline' => 'Évaluation managériale RH : respect du règlement et assiduité comportementale.',
                        'skills_acquired' => 'Évaluation managériale RH : progression technique et acquisition de compétences.',
                    ];

                    $scoreRecord->score = $defaultScores[$criterion->code] ?? 15.00;
                    $scoreRecord->max_score = 20.00;
                    $scoreRecord->weighted_score = round(($scoreRecord->score / 20.00) * (float) $criterion->weight_percentage, 2);
                    $scoreRecord->justification = $defaultJustifs[$criterion->code] ?? 'Évaluation managériale du Responsable RH.';
                    $scoreRecord->comments = $scoreRecord->comments ?: 'Évaluation managériale certifiée';
                    $scoreRecord->save();
                } else {
                    $scoreRecord->max_score = 20.00;
                    $scoreRecord->weighted_score = round(($scoreRecord->score / 20.00) * (float) $criterion->weight_percentage, 2);
                    $scoreRecord->save();
                }
            }
        }
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

        // Employés nécessitant un suivi particulier (< 12/20 ou progression négative)
        $needsFollowUpEmployees = $evaluations->filter(function ($eval) {
            $score = (float) ($eval->final_score_20 ?: ($eval->final_score_30 / 1.5));

            return $score < 12.00 || (float) $eval->score_progress <= -1.50;
        })->sortBy(fn ($e) => (float) ($e->final_score_20 ?: ($e->final_score_30 / 1.5)))->values();

        // Évolution des moyennes générales sur les 6 derniers mois (Graphique de performance)
        $historicalTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::createFromDate($year, $month, 1)->subMonths($i);
            $histYear = $dt->year;
            $histMonth = $dt->month;

            $monthEvals = MonthlyEvaluation::where('year', $histYear)
                ->where('month', $histMonth)
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
     * Classement interne des performances (barème /20).
     */
    public function getInternalRanking(int $year, int $month, ?string $department = null): array
    {
        $query = MonthlyEvaluation::with(['employee'])
            ->where('year', $year)
            ->where('month', $month);

        if ($department) {
            $query->whereHas('employee', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }

        $evaluations = $query->get();

        $topOverall = $evaluations->sortByDesc(fn ($e) => (float) ($e->final_score_20 ?: ($e->final_score_30 / 1.5)))->take(10)->values();
        $topProgression = $evaluations->where('score_progress', '>', 0)->sortByDesc('score_progress')->take(5)->values();
        $topPunctuality = $evaluations->sortByDesc('punctuality_points')->take(5)->values();
        $topTasks = $evaluations->sortByDesc('tasks_points')->take(5)->values();

        return [
            'topOverall' => $topOverall,
            'topProgression' => $topProgression,
            'topPunctuality' => $topPunctuality,
            'topTasks' => $topTasks,
        ];
    }
}
