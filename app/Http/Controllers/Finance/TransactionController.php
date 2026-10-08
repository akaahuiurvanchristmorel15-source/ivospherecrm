<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\Transaction;
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

        $caisse = CashRegister::findOrFail($validated['cash_register_id']);

        if ($validated['type'] === 'retrait' && $caisse->balance < $validated['amount']) {
            return back()->with('error', 'Solde insuffisant dans la caisse.');
        }

        $balanceBefore = $caisse->balance;
        $balanceAfter = $validated['type'] === 'depot'
            ? $balanceBefore + $validated['amount']
            : $balanceBefore - $validated['amount'];

        $transaction = Transaction::create([
            'cash_register_id' => $caisse->id,
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $validated['description'],
            'reference' => 'TRX-'.time(),
            'date' => now(),
        ]);

        $caisse->update(['balance' => $balanceAfter]);

        return back()->with('success', 'Transaction enregistrée avec succès.');
    }
}
