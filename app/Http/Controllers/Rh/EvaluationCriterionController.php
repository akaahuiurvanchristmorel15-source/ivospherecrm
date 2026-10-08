<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EvaluationCriterion;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvaluationCriterionController extends Controller
{
    /**
     * Matrice de configuration des critères d'évaluation RH (Point 2 & 15).
     */
    public function index(Request $request): View
    {
        $query = EvaluationCriterion::orderBy('order')->orderBy('id');

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        $criteria = $query->get();
        $departments = Employee::select('department')->whereNotNull('department')->distinct()->pluck('department');
        $totalWeight = (float) $criteria->where('is_active', true)->sum('weight_percentage');

        return view('rh.evaluations.criteria', compact('criteria', 'departments', 'totalWeight'));
    }

    /**
     * Enregistrement d'un nouveau critère personnalisé ou par métier.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:evaluation_criteria,code'],
            'description' => ['nullable', 'string', 'max:1000'],
            'weight_percentage' => ['required', 'numeric', 'min:1', 'max:100'],
            'calculation_mode' => ['required', 'in:automatic,manual'],
            'department' => ['nullable', 'string', 'max:50'],
            'is_mandatory' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_mandatory'] = $request->boolean('is_mandatory', true);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['order'] = EvaluationCriterion::max('order') + 1;

        $criterion = EvaluationCriterion::create($validated);

        ActivityLogger::log(
            'creation_critere_evaluation',
            "Nouveau critère d'évaluation créé : {$criterion->name} ({$criterion->weight_percentage}%)",
            $criterion
        );

        return redirect()->route('rh.evaluations.criteria.index')
            ->with('success', "Critère « {$criterion->name} » configuré avec succès.");
    }

    /**
     * Modification d'un critère existant (poids, mode de calcul, description).
     */
    public function update(Request $request, EvaluationCriterion $criterion): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'weight_percentage' => ['required', 'numeric', 'min:1', 'max:100'],
            'calculation_mode' => ['required', 'in:automatic,manual'],
            'department' => ['nullable', 'string', 'max:50'],
            'is_mandatory' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_mandatory'] = $request->boolean('is_mandatory');
        $validated['is_active'] = $request->boolean('is_active');

        $criterion->update($validated);

        ActivityLogger::log(
            'modification_critere_evaluation',
            "Critère d'évaluation mis à jour : {$criterion->name} ({$criterion->weight_percentage}%)",
            $criterion
        );

        return redirect()->route('rh.evaluations.criteria.index')
            ->with('success', "Critère « {$criterion->name} » mis à jour avec succès.");
    }

    /**
     * Activation / Désactivation rapide d'un critère.
     */
    public function toggle(EvaluationCriterion $criterion): RedirectResponse
    {
        $criterion->update(['is_active' => ! $criterion->is_active]);

        $statusStr = $criterion->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Critère « {$criterion->name} » {$statusStr}.");
    }

    /**
     * Suppression d'un critère.
     */
    public function destroy(EvaluationCriterion $criterion): RedirectResponse
    {
        $name = $criterion->name;
        $criterion->delete();

        return redirect()->route('rh.evaluations.criteria.index')
            ->with('success', "Critère « {$name} » supprimé.");
    }
}
