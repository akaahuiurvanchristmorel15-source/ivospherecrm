<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\Domain;
use App\Models\Revenue;
use App\Services\ActivityLogger;
use App\Services\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $revenue = DB::transaction(function () use ($validated) {
            $revenue = Revenue::create($validated);

            TreasuryService::move(CashRegister::findOrFail($validated['cash_register_id']), 'encaissement', (float) $revenue->amount, [
                'domain_id' => $revenue->domain_id,
                'description' => 'Encaissement recette : '.$revenue->reference,
                'date' => $revenue->date,
            ]);

            return $revenue;
        });

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
        // Annule l'encaissement : l'argent ressort de la caisse, avec une transaction de contre-passation
        DB::transaction(function () use ($revenue) {
            if ($revenue->cashRegister && (float) $revenue->amount > 0) {
                TreasuryService::move($revenue->cashRegister, 'retrait', (float) $revenue->amount, [
                    'domain_id' => $revenue->domain_id,
                    'description' => 'Annulation recette : '.$revenue->reference,
                ], allowNegative: true);
            }

            $revenue->delete();
        });
        ActivityLogger::log('suppression_recette', 'Suppression d\'une recette', $revenue);

        return redirect()->route('finance.revenues.index')->with('success', 'Recette supprimée.');
    }
}
