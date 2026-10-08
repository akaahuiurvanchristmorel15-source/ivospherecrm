<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\Domain;
use App\Models\Revenue;
use App\Models\Transaction;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevenueController extends Controller
{
    public function index(Request $request)
    {
        $query = Revenue::with(['domain', 'cashRegister', 'user']);

        if ($request->filled('domain_id')) {
            $query->byDomain($request->domain_id);
        }
        if ($request->filled('source')) {
            $query->where('source', 'like', '%'.$request->source.'%');
        }
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $query->forDateRange($request->start_date, $request->end_date);
        }

        $revenues = $query->orderBy('date', 'desc')->paginate(15);
        $total = $query->sum('amount');

        $domains = Domain::all();

        return view('finance.revenues.index', compact('revenues', 'total', 'domains'));
    }

    public function create()
    {
        $domains = Domain::all();
        $cashRegisters = CashRegister::active()->get();

        return view('finance.revenues.create_edit', compact('domains', 'cashRegisters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain_id' => 'nullable|exists:domains,id',
            'cash_register_id' => 'required|exists:cash_registers,id',
            'source' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['reference'] = 'REC-'.date('Ymd').'-'.rand(1000, 9999);

        $revenue = Revenue::create($validated);

        // Add to cash register
        $caisse = CashRegister::findOrFail($validated['cash_register_id']);

        Transaction::create([
            'cash_register_id' => $caisse->id,
            'domain_id' => $revenue->domain_id,
            'user_id' => Auth::id(),
            'type' => 'encaissement',
            'amount' => $revenue->amount,
            'balance_before' => $caisse->balance,
            'balance_after' => $caisse->balance + $revenue->amount,
            'description' => 'Encaissement recette : '.$revenue->reference,
            'reference' => 'TRX-'.time(),
            'date' => $revenue->date,
        ]);

        $caisse->increment('balance', $revenue->amount);

        ActivityLogger::log('creation_recette', 'Enregistrement d\'une recette', $revenue);

        return redirect()->route('finance.revenues.index')->with('success', 'Recette enregistrée avec succès.');
    }

    public function edit(Revenue $revenue)
    {
        $domains = Domain::all();
        $cashRegisters = CashRegister::active()->get();

        return view('finance.revenues.create_edit', compact('revenue', 'domains', 'cashRegisters'));
    }

    public function update(Request $request, Revenue $revenue)
    {
        // Typically, we don't allow modifying amount/caisse easily due to transactions,
        // but for simplicity we can allow updating notes/description
        $validated = $request->validate([
            'source' => 'required|string|max:255',
            'description' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $revenue->update($validated);
        ActivityLogger::log('modification_recette', 'Modification d\'une recette', $revenue);

        return redirect()->route('finance.revenues.index')->with('success', 'Recette mise à jour avec succès.');
    }

    public function destroy(Revenue $revenue)
    {
        // Revert balance
        $caisse = $revenue->cashRegister;
        if ($caisse) {
            $caisse->decrement('balance', $revenue->amount);
            // We could also record a reverse transaction, but simple deduction works for now
        }

        $revenue->delete();
        ActivityLogger::log('suppression_recette', 'Suppression d\'une recette', $revenue);

        return redirect()->route('finance.revenues.index')->with('success', 'Recette supprimée.');
    }
}
