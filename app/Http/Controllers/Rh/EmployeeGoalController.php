<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeGoal;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeGoalController extends Controller
{
    /**
     * Liste et suivi global des objectifs individuels (Point 9).
     */
    public function index(Request $request): View
    {
        $query = EmployeeGoal::with(['employee', 'assignedBy'])->latest('due_date');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $goals = $query->paginate(20)->withQueryString();
        $employees = Employee::where('status', 'actif')->orderBy('first_name')->get();

        $totalGoals = EmployeeGoal::count();
        $achievedGoals = EmployeeGoal::where('status', 'atteint')->count();
        $inProgressGoals = EmployeeGoal::where('status', 'en_cours')->count();
        $achievementRate = $totalGoals > 0 ? round(($achievedGoals / $totalGoals) * 100, 1) : 0;

        return view('rh.evaluations.goals', compact(
            'goals',
            'employees',
            'totalGoals',
            'achievedGoals',
            'inProgressGoals',
            'achievementRate'
        ));
    }

    /**
     * Assignation d'un nouvel objectif individuel à un collaborateur.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'monthly_evaluation_id' => ['nullable', 'exists:monthly_evaluations,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:start_date'],
            'progress_pct' => ['nullable', 'integer', 'min:0', 'max:100'],
            'status' => ['required', 'in:a_faire,en_cours,atteint,non_atteint'],
        ]);

        $validated['assigned_by'] = auth()->id();
        $validated['progress_pct'] = (int) ($validated['progress_pct'] ?? 0);

        if ($validated['status'] === 'atteint') {
            $validated['completed_at'] = now();
            $validated['progress_pct'] = 100;
        }

        $goal = EmployeeGoal::create($validated);

        ActivityLogger::log(
            'assignation_objectif_employe',
            "Objectif « {$goal->title} » assigné à {$goal->employee->full_name} (Échéance : {$goal->due_date->format('d/m/Y')})",
            $goal
        );

        return back()->with('success', "Objectif assigné avec succès à {$goal->employee->full_name}.");
    }

    /**
     * Mise à jour de la progression, du statut ou du résultat d'un objectif.
     */
    public function update(Request $request, EmployeeGoal $goal): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'due_date' => ['sometimes', 'required', 'date'],
            'progress_pct' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => ['required', 'in:a_faire,en_cours,atteint,non_atteint'],
            'result_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['status'] === 'atteint' && $goal->status !== 'atteint') {
            $validated['completed_at'] = now();
            $validated['progress_pct'] = 100;
        }

        $goal->update($validated);

        ActivityLogger::log(
            'maj_objectif_employe',
            "Objectif « {$goal->title} » mis à jour ({$goal->progress_pct}%, Statut : {$goal->status})",
            $goal
        );

        return back()->with('success', "Objectif « {$goal->title} » actualisé.");
    }

    /**
     * Suppression d'un objectif.
     */
    public function destroy(EmployeeGoal $goal): RedirectResponse
    {
        $title = $goal->title;
        $goal->delete();

        return back()->with('success', "Objectif « {$title} » supprimé.");
    }
}
