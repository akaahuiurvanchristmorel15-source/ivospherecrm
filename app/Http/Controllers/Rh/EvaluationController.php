<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\DailyEvaluationScore;
use App\Models\Employee;
use App\Models\HrSetting;
use App\Models\MonthlyEvaluation;
use App\Models\MonthlyEvaluationCriterionScore;
use App\Services\ActivityLogger;
use App\Services\EvaluationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvaluationController extends Controller
{
    public function __construct(
        protected EvaluationService $evaluationService
    ) {}

    /**
     * Tableau de bord RH de la campagne d'évaluation (Point 1).
     */
    public function index(Request $request): View
    {
        $settings = HrSetting::current();
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $tab = $request->input('tab', 'all');

        $query = MonthlyEvaluation::with(['employee', 'evaluator'])
            ->where('year', $year)
            ->where('month', $month);

        if ($request->filled('department')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($tab === 'progressing') {
            $query->where('score_progress', '>', 0);
        } elseif ($tab === 'follow_up') {
            $query->where(function ($q) {
                $q->where('final_score_30', '<', 18)->orWhere('score_progress', '<=', -2.00);
            });
        } elseif ($tab === 'locked') {
            $query->where('is_locked', true);
        }

        $evaluations = $query->orderBy('final_score_30', 'desc')->paginate(20)->withQueryString();
        $departments = Employee::select('department')->whereNotNull('department')->distinct()->pluck('department');

        // Statistiques consolidées du tableau de bord (Moyenne générale, par service, progression, alertes)
        $stats = $this->evaluationService->getCompanyDashboardStats($year, $month);

        $monthNames = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];

        return view('rh.evaluations.index', compact(
            'evaluations',
            'year',
            'month',
            'tab',
            'monthNames',
            'departments',
            'settings',
            'stats'
        ));
    }

    /**
     * Fiche d'évaluation détaillée d'un collaborateur (Point 7).
     */
    public function show(MonthlyEvaluation $evaluation): View
    {
        $evaluation->load([
            'employee.goals',
            'employee.improvementPlans',
            'evaluator',
            'lockedByUser',
            'dailyScores',
            'criteriaScores.criterion',
            'goals',
            'improvementPlans',
        ]);

        $settings = HrSetting::current();
        $employee = $evaluation->employee;

        // Si les scores détaillés par critère n'ont pas encore été synchronisés ou incomplets, synchroniser
        if ($evaluation->criteriaScores->count() < 10) {
            $workingDays = (int) $evaluation->total_working_days ?: 22;
            $presentCount = $evaluation->dailyScores->where('punctuality_score', '>', 0)->count();
            $lateCount = $evaluation->dailyScores->filter(fn ($s) => str_contains(strtolower($s->punctuality_notes ?? ''), 'retard'))->count();
            $totalAssigned = (int) $evaluation->dailyScores->sum('tasks_assigned_count');
            $totalValidated = (int) $evaluation->dailyScores->sum('tasks_validated_count');

            $this->evaluationService->syncCriteriaForEvaluation(
                $evaluation,
                $workingDays,
                $presentCount,
                $lateCount,
                $totalAssigned,
                $totalValidated
            );
            $evaluation->recalculateScores();
            $evaluation->load('criteriaScores.criterion');
        }

        // Taux de performance clés pour le récapitulatif
        $workingDays = max(1, (int) $evaluation->total_working_days);
        $presentDays = $evaluation->dailyScores->where('punctuality_score', '>', 0)->count();
        $attendanceRate = round(($presentDays / $workingDays) * 100);

        $punctualityRate = $presentDays > 0
            ? round((($presentDays - $evaluation->dailyScores->filter(fn ($s) => str_contains(strtolower($s->punctuality_notes ?? ''), 'retard'))->count()) / $presentDays) * 100)
            : 100;

        $assignedTasks = (int) $evaluation->dailyScores->sum('tasks_assigned_count');
        $validatedTasks = (int) $evaluation->dailyScores->sum('tasks_validated_count');
        $tasksRate = $assignedTasks > 0 ? round(($validatedTasks / $assignedTasks) * 100) : 100;

        // Historique récent des 6 derniers mois pour la courbe de progression
        $evaluationHistory = MonthlyEvaluation::where('employee_id', $employee->id)
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->take(6)
            ->get();

        return view('rh.evaluations.show', compact(
            'evaluation',
            'employee',
            'settings',
            'attendanceRate',
            'punctualityRate',
            'tasksRate',
            'evaluationHistory'
        ));
    }

    /**
     * Enregistrement des critères manuels, appréciations et progression du workflow (Point 4 & 11).
     */
    public function update(Request $request, MonthlyEvaluation $evaluation): RedirectResponse
    {
        // Contrôle de sécurité : interdiction de modifier une évaluation verrouillée
        if ($evaluation->is_locked && ! auth()->user()->hasRole('administrateur', 'responsable_rh')) {
            return back()->with('error', 'Cette évaluation est formellement verrouillée. Contactez la Direction RH pour toute rectification.');
        }

        $validated = $request->validate([
            'manager_comment' => ['nullable', 'string', 'max:2000'],
            'strengths' => ['nullable', 'string', 'max:2000'],
            'weaknesses' => ['nullable', 'string', 'max:2000'],
            'recommendations' => ['nullable', 'string', 'max:2000'],
            'future_goals' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:en_cours,valide,cloture'],
            'workflow_step' => ['nullable', 'in:brouillon,en_evaluation,validation_manager,valide_rh,verrouille'],
            'criteria' => ['nullable', 'array'],
            'criteria.*.score' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'criteria.*.comments' => ['nullable', 'string', 'max:500'],
        ]);

        // Mise à jour des scores des critères manuels (/20)
        if (! empty($validated['criteria'])) {
            foreach ($validated['criteria'] as $criterionId => $data) {
                $scoreItem = MonthlyEvaluationCriterionScore::firstOrNew([
                    'monthly_evaluation_id' => $evaluation->id,
                    'evaluation_criterion_id' => $criterionId,
                ]);

                if (isset($data['score'])) {
                    $scoreItem->score = min(20.00, max(0.00, (float) $data['score']));
                    $scoreItem->max_score = 20.00;
                    $scoreItem->comments = $data['comments'] ?? $scoreItem->comments;
                    $scoreItem->save();
                }
            }
        }

        $evaluation->update([
            'manager_comment' => $validated['manager_comment'] ?? $evaluation->manager_comment,
            'strengths' => $validated['strengths'] ?? $evaluation->strengths,
            'weaknesses' => $validated['weaknesses'] ?? $evaluation->weaknesses,
            'recommendations' => $validated['recommendations'] ?? $evaluation->recommendations,
            'future_goals' => $validated['future_goals'] ?? $evaluation->future_goals,
            'status' => $validated['status'],
            'workflow_step' => $validated['workflow_step'] ?? $evaluation->workflow_step,
            'evaluator_id' => auth()->id(),
        ]);

        $evaluation->recalculateScores();

        ActivityLogger::log(
            'saisie_evaluation_mensuelle',
            "Évaluation de {$evaluation->employee->full_name} ({$evaluation->month_name} {$evaluation->year}) actualisée (Moyenne : {$evaluation->final_score_20}/20 — {$evaluation->appreciation})",
            $evaluation
        );

        return back()->with('success', "Évaluation de {$evaluation->employee->full_name} enregistrée avec succès (Moyenne : {$evaluation->final_score_20} / 20 — {$evaluation->appreciation}).");
    }

    /**
     * Verrouillage officiel de l'évaluation (Point 16 - Sécurité & Intégrité).
     */
    public function lock(MonthlyEvaluation $evaluation): RedirectResponse
    {
        if (! auth()->user()->hasRole('administrateur', 'responsable_rh', 'responsable')) {
            abort(403, 'Permission refusée.');
        }

        $evaluation->lock(auth()->id());

        ActivityLogger::log(
            'verrouillage_evaluation',
            "Évaluation de {$evaluation->employee->full_name} ({$evaluation->month_name} {$evaluation->year}) clôturée et verrouillée.",
            $evaluation
        );

        return back()->with('success', "L'évaluation a été verrouillée avec succès.");
    }

    /**
     * Déverrouillage exceptionnel pour correction autorisée.
     */
    public function unlock(MonthlyEvaluation $evaluation): RedirectResponse
    {
        if (! auth()->user()->hasRole('administrateur', 'responsable_rh')) {
            abort(403, 'Seul un Administrateur ou Responsable RH peut déverrouiller une évaluation.');
        }

        $evaluation->unlock();

        ActivityLogger::log(
            'deverrouillage_evaluation',
            "Évaluation de {$evaluation->employee->full_name} déverrouillée pour révision.",
            $evaluation
        );

        return back()->with('success', "L'évaluation a été déverrouillée pour révision.");
    }

    /**
     * Signature électronique interne (Manager ou Collaborateur - Point 11 & 16).
     */
    public function sign(Request $request, MonthlyEvaluation $evaluation): RedirectResponse
    {
        $validated = $request->validate([
            'signer_type' => ['required', 'in:manager,employee'],
            'signature_name' => ['required', 'string', 'max:255'],
        ]);

        if ($validated['signer_type'] === 'manager') {
            $evaluation->update([
                'manager_signature' => $validated['signature_name'],
                'manager_signed_at' => now(),
            ]);
            $msg = 'Signature du manager apposée avec succès.';
        } else {
            $evaluation->update([
                'employee_signature' => $validated['signature_name'],
                'employee_signed_at' => now(),
            ]);
            $msg = 'Émargement collaborateur enregistré avec succès.';
        }

        ActivityLogger::log(
            'signature_evaluation',
            "Signature {$validated['signer_type']} apposée pour {$evaluation->employee->full_name} par {$validated['signature_name']}",
            $evaluation
        );

        return back()->with('success', $msg);
    }

    /**
     * Rapport PDF officiel imprimable / téléchargeable au format A4 (Point 12).
     */
    public function print(MonthlyEvaluation $evaluation): View
    {
        $evaluation->load([
            'employee.goals',
            'employee.improvementPlans',
            'evaluator',
            'criteriaScores.criterion',
            'dailyScores',
        ]);

        $settings = HrSetting::current();
        $employee = $evaluation->employee;

        // Historique des 6 derniers mois pour le graphique imprimable
        $history = MonthlyEvaluation::where('employee_id', $employee->id)
            ->where('evaluation_date', '<=', $evaluation->evaluation_date)
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->take(6)
            ->get();

        return view('rh.evaluations.print', compact('evaluation', 'employee', 'settings', 'history'));
    }

    /**
     * Classement interne des performances & palmarès (Point 14).
     */
    public function ranking(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $department = $request->input('department');

        $departments = Employee::select('department')->whereNotNull('department')->distinct()->pluck('department');
        $ranking = $this->evaluationService->getInternalRanking($year, $month, $department);

        return view('rh.evaluations.ranking', compact('ranking', 'year', 'month', 'department', 'departments'));
    }

    /**
     * Notation managériale de l'esprit d'équipe pour une journée spécifique (0.00 à 0.10).
     */
    public function updateTeamwork(Request $request, DailyEvaluationScore $dailyScore): RedirectResponse
    {
        $validated = $request->validate([
            'teamwork_score' => ['required', 'numeric', 'min:0', 'max:0.10'],
            'teamwork_comment' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->evaluationService->setDailyTeamworkScore(
                $dailyScore,
                (float) $validated['teamwork_score'],
                $validated['teamwork_comment'] ?? null,
                auth()->id()
            );

            return back()->with('success', 'Note d\'esprit d\'équipe enregistrée.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Déclenchement de la génération en lot de la campagne mensuelle pour tous les collaborateurs (Point 5).
     */
    public function generateCampaign(Request $request): RedirectResponse
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $count = $this->evaluationService->generateAllForMonth($year, $month, auth()->id());

        ActivityLogger::log(
            'generation_campagne_evaluation',
            "Campagne d'évaluation générée pour {$count} collaborateurs ({$month}/{$year})",
            null
        );

        return redirect()->route('rh.evaluations.index', ['year' => $year, 'month' => $month])
            ->with('success', "Campagne d'évaluation du mois générée avec succès pour {$count} collaborateurs.");
    }

    /**
     * Historique plurimensuel des performances d'un collaborateur (Point 8).
     */
    public function history(Employee $employee): View
    {
        $evaluations = MonthlyEvaluation::where('employee_id', $employee->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        $average = $evaluations->isNotEmpty()
            ? (float) $evaluations->avg(fn ($e) => (float) ($e->final_score_20 ?: ($e->final_score_30 / 1.5)))
            : 0.0;
        $highest = $evaluations->isNotEmpty()
            ? (float) $evaluations->max(fn ($e) => (float) ($e->final_score_20 ?: ($e->final_score_30 / 1.5)))
            : 0.0;
        $lowest = $evaluations->isNotEmpty()
            ? (float) $evaluations->min(fn ($e) => (float) ($e->final_score_20 ?: ($e->final_score_30 / 1.5)))
            : 0.0;

        return view('rh.evaluations.history', compact('employee', 'evaluations', 'average', 'highest', 'lowest'));
    }
}
