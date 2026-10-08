<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PerformanceImprovementPlan;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceImprovementPlanController extends Controller
{
    /**
     * Liste et suivi des plans d'amélioration de la performance (Point 10 - PIP).
     */
    public function index(Request $request): View
    {
        $query = PerformanceImprovementPlan::with(['employee', 'creator', 'supervisor'])->latest('start_date');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pips = $query->paginate(15)->withQueryString();
        $employees = Employee::where('status', 'actif')->orderBy('first_name')->get();
        $supervisors = User::orderBy('name')->get();

        $activeCount = PerformanceImprovementPlan::where('status', 'en_cours')->count();
        $satisfactoryCount = PerformanceImprovementPlan::where('status', 'satisfaisant')->count();
        $totalCount = PerformanceImprovementPlan::count();

        return view('rh.evaluations.pips', compact(
            'pips',
            'employees',
            'supervisors',
            'activeCount',
            'satisfactoryCount',
            'totalCount'
        ));
    }

    /**
     * Création d'un plan d'amélioration pour un collaborateur rencontrant des difficultés.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'monthly_evaluation_id' => ['nullable', 'exists:monthly_evaluations,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'problem_identified' => ['required', 'string', 'max:2000'],
            'target_objective' => ['required', 'string', 'max:2000'],
            'corrective_actions' => ['required', 'string', 'max:2000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['supervisor_id'] = $validated['supervisor_id'] ?? auth()->id();
        $validated['status'] = 'en_cours';
        $validated['progress_pct'] = 0;

        $pip = PerformanceImprovementPlan::create($validated);

        ActivityLogger::log(
            'creation_plan_amelioration',
            "Plan d'amélioration ouvert pour {$pip->employee->full_name} : {$pip->title}",
            $pip
        );

        return back()->with('success', "Plan d'amélioration ouvert pour {$pip->employee->full_name}.");
    }

    /**
     * Mise à jour de la progression et conclusion du plan.
     */
    public function update(Request $request, PerformanceImprovementPlan $pip): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'target_objective' => ['sometimes', 'required', 'string', 'max:2000'],
            'corrective_actions' => ['sometimes', 'required', 'string', 'max:2000'],
            'end_date' => ['sometimes', 'required', 'date'],
            'progress_pct' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => ['required', 'in:en_cours,satisfaisant,non_satisfaisant,annule'],
            'final_assessment' => ['nullable', 'string', 'max:2000'],
        ]);

        $pip->update($validated);

        ActivityLogger::log(
            'maj_plan_amelioration',
            "Plan d'amélioration de {$pip->employee->full_name} mis à jour ({$pip->progress_pct}%, Statut : {$pip->status})",
            $pip
        );

        return back()->with('success', "Plan d'amélioration actualisé.");
    }

    /**
     * Suppression d'un plan d'amélioration.
     */
    public function destroy(PerformanceImprovementPlan $pip): RedirectResponse
    {
        $title = $pip->title;
        $pip->delete();

        return back()->with('success', "Plan d'amélioration « {$title} » supprimé.");
    }
}
