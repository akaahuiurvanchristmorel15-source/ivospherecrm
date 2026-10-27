<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rh\ContestEvaluationRequest;
use App\Http\Requests\Rh\SaveEvaluationSheetRequest;
use App\Models\EvaluationCriterion;
use App\Models\EvaluationSheet;
use App\Models\MonthlyEvaluation;
use App\Services\ActivityLogger;
use App\Services\EvaluationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Fiches d'évaluation des membres : chaque évaluateur (Direction, RH, responsables de fonction, pairs, auto-évaluation)
 * note seulement les critères de son domaine, selon le référentiel d'évaluation.
 */
class EvaluationSheetController extends Controller
{
    public function __construct(
        protected EvaluationService $evaluationService
    ) {}

    /**
     * Fiches que l'utilisateur connecté peut remplir pour le mois choisi.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $evaluations = MonthlyEvaluation::with(['employee', 'sheets' => fn ($query) => $query->where('evaluator_id', $user->id)])
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->map(fn (MonthlyEvaluation $evaluation) => [
                'evaluation' => $evaluation,
                'groups' => EvaluationSheet::groupsAvailableTo($user, $evaluation->employee),
            ])
            ->filter(fn (array $row) => $row['groups'] !== [])
            ->sortBy(fn (array $row) => $row['evaluation']->employee->full_name)
            ->values();

        return view('rh.evaluations.sheets', compact('evaluations', 'year', 'month'));
    }

    /**
     * Formulaire de la fiche d'un évaluateur pour un membre, au titre d'un groupe d'évaluateurs.
     */
    public function edit(Request $request, MonthlyEvaluation $evaluation): View
    {
        $group = (string) $request->query('group');

        abort_unless(in_array($group, EvaluationSheet::groupsAvailableTo($request->user(), $evaluation->employee), true), 403);

        $sheet = EvaluationSheet::with('scores')->firstOrNew([
            'monthly_evaluation_id' => $evaluation->id,
            'evaluator_id' => $request->user()->id,
            'evaluator_group' => $group,
        ]);

        $criteria = EvaluationCriterion::referential()->get()
            ->filter(fn (EvaluationCriterion $criterion) => $criterion->isRatableBy($group))
            ->groupBy('block');

        $evaluation->load('employee');

        return view('rh.evaluations.sheet', compact('evaluation', 'sheet', 'group', 'criteria'));
    }

    public function update(SaveEvaluationSheetRequest $request, MonthlyEvaluation $evaluation): RedirectResponse
    {
        $validated = $request->validated();

        $sheet = $this->evaluationService->saveSheet($evaluation, $request->user(), $validated['evaluator_group'], $validated['scores'] ?? []);

        ActivityLogger::log(
            'fiche_evaluation_membre',
            "Fiche « {$sheet->group_label} » remplie pour {$evaluation->employee->full_name} ({$evaluation->month_name} {$evaluation->year})",
            $evaluation
        );

        return redirect()->route('rh.evaluation-sheets.index', ['year' => $evaluation->year, 'month' => $evaluation->month])
            ->with('success', "Fiche « {$sheet->group_label} » enregistrée pour {$evaluation->employee->full_name}.");
    }

    /**
     * Contestation écrite de sa note par le membre, dans les 48 heures.
     */
    public function contest(ContestEvaluationRequest $request, MonthlyEvaluation $evaluation): RedirectResponse
    {
        $evaluation->update([
            'contestation' => $request->validated('contestation'),
            'contested_at' => now(),
        ]);

        ActivityLogger::log(
            'contestation_evaluation',
            "{$evaluation->employee->full_name} conteste sa note de {$evaluation->month_name} {$evaluation->year}",
            $evaluation
        );

        return back()->with('success', 'Votre contestation a été transmise à la Gérante, qui vous répondra par écrit.');
    }
}
