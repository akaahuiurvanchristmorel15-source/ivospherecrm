<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\Transaction;
use App\Services\TreasuryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['cashRegister', 'bankAccount', 'domain', 'user']);

        if ($request->filled('type')) {
            $query->byType($request->type);
        }
        if ($request->filled('cash_register_id')) {
            $query->where('cash_register_id', $request->cash_register_id);
        }
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $query->forDateRange($request->start_date, $request->end_date);
        }

        $transactions = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(15);
        $cashRegisters = CashRegister::active()->get();

        return view('finance.transactions.index', compact('transactions', 'cashRegisters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'type' => 'required|in:depot,retrait',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string',
        ]);

        try {
            TreasuryService::move(CashRegister::findOrFail($validated['cash_register_id']), $validated['type'], (float) $validated['amount'], [
                'user_id' => Auth::id(),
                'description' => $validated['description'],
            ]);
        } catch (Exception $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return back()->with('success', ($validated['type'] === 'depot' ? 'Dépôt' : 'Retrait').' enregistré : le solde de la caisse est à jour.');
    }
}
