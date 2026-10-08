<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Domain;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $query = Budget::with('domain');

        if ($request->filled('domain_id')) {
            $query->byDomain($request->domain_id);
        }

        $budgets = $query->orderBy('period_end', 'desc')->paginate(15);
        $domains = Domain::all();

        return view('finance.budgets.index', compact('budgets', 'domains'));
    }

    public function create()
    {
        $domains = Domain::all();

        return view('finance.budgets.create_edit', compact('domains'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'status' => 'required|in:actif,cloturé',
            'notes' => 'nullable|string',
        ]);

        $validated['spent'] = 0; // Initialize spent to 0

        $budget = Budget::create($validated);
        ActivityLogger::log('creation_budget', 'Création d\'un budget', $budget);

        return redirect()->route('finance.budgets.index')->with('success', 'Budget créé avec succès.');
    }

    public function edit(Budget $budget)
    {
        $domains = Domain::all();

        return view('finance.budgets.create_edit', compact('budget', 'domains'));
    }

    public function update(Request $request, Budget $budget)
    {
        $validated = $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0',
            'spent' => 'required|numeric|min:0',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'status' => 'required|in:actif,cloturé',
            'notes' => 'nullable|string',
        ]);

        $budget->update($validated);
        ActivityLogger::log('modification_budget', 'Modification d\'un budget', $budget);

        return redirect()->route('finance.budgets.index')->with('success', 'Budget mis à jour avec succès.');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        ActivityLogger::log('suppression_budget', 'Suppression d\'un budget', $budget);

        return redirect()->route('finance.budgets.index')->with('success', 'Budget supprimé.');
    }
}
