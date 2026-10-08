<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\Domain;
use App\Models\Expense;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['domain', 'cashRegister', 'supplier', 'user']);

        if ($request->filled('domain_id')) {
            $query->byDomain($request->domain_id);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $query->forDateRange($request->start_date, $request->end_date);
        }

        $expenses = $query->orderBy('date', 'desc')->paginate(15);
        $total = $query->sum('amount');

        $domains = Domain::all();

        return view('finance.expenses.index', compact('expenses', 'total', 'domains'));
    }

    public function create()
    {
        $domains = Domain::all();
        $cashRegisters = CashRegister::active()->get();
        $suppliers = Supplier::all();

        return view('finance.expenses.create_edit', compact('domains', 'cashRegisters', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain_id' => 'nullable|exists:domains,id',
            'cash_register_id' => 'nullable|exists:cash_registers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category' => 'required|string|max:100',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'en_attente';
        $validated['reference'] = 'EXP-'.date('Ymd').'-'.rand(1000, 9999);

        if ($request->hasFile('receipt')) {
            $validated['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }

        $expense = Expense::create($validated);
        ActivityLogger::log('creation_depense', 'Création d\'une dépense', $expense);

        return redirect()->route('finance.expenses.index')->with('success', 'Dépense enregistrée avec succès.');
    }

    public function edit(Expense $expense)
    {
        $domains = Domain::all();
        $cashRegisters = CashRegister::active()->get();
        $suppliers = Supplier::all();

        return view('finance.expenses.create_edit', compact('expense', 'domains', 'cashRegisters', 'suppliers'));
    }

    public function update(Request $request, Expense $expense)
    {
        if ($expense->status === 'validee') {
            return back()->with('error', 'Impossible de modifier une dépense validée.');
        }

        $validated = $request->validate([
            'domain_id' => 'nullable|exists:domains,id',
            'cash_register_id' => 'nullable|exists:cash_registers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category' => 'required|string|max:100',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'notes' => 'nullable|string',
        ]);

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $validated['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }

        $expense->update($validated);
        ActivityLogger::log('modification_depense', 'Modification d\'une dépense', $expense);

        return redirect()->route('finance.expenses.index')->with('success', 'Dépense mise à jour avec succès.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->status === 'validee') {
            return back()->with('error', 'Impossible de supprimer une dépense validée.');
        }
        $expense->delete();
        ActivityLogger::log('suppression_depense', 'Suppression d\'une dépense', $expense);

        return redirect()->route('finance.expenses.index')->with('success', 'Dépense supprimée.');
    }

    public function approve(Expense $expense)
    {
        if ($expense->status === 'validee') {
            return back()->with('error', 'Dépense déjà validée.');
        }

        if ($expense->cash_register_id) {
            $caisse = $expense->cashRegister;
            if ($caisse->balance < $expense->amount) {
                return back()->with('error', 'Solde insuffisant dans la caisse.');
            }

            // Create transaction
            Transaction::create([
                'cash_register_id' => $caisse->id,
                'domain_id' => $expense->domain_id,
                'user_id' => Auth::id(),
                'type' => 'retrait',
                'amount' => $expense->amount,
                'balance_before' => $caisse->balance,
                'balance_after' => $caisse->balance - $expense->amount,
                'description' => 'Paiement dépense : '.$expense->reference,
                'reference' => 'TRX-'.time(),
                'date' => now(),
            ]);

            $caisse->decrement('balance', $expense->amount);
        }

        $expense->update([
            'status' => 'validee',
            'approved_by' => Auth::id(),
        ]);

        ActivityLogger::log('validation_depense', 'Validation d\'une dépense', $expense);

        return back()->with('success', 'Dépense validée et déduite de la caisse.');
    }
}
