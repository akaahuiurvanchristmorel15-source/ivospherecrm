<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'customer']);

        if ($request->filled('search')) {
            $query->where('reference', 'like', "%{$request->search}%");
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $payments = $query->latest()->paginate(15);

        return view('commercial.payments.index', compact('payments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:especes,virement,cheque,mobile_money,carte',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        // Check if amount doesn't exceed remaining
        if ($validated['amount'] > $invoice->remaining) {
            return back()->with('error', 'Le montant du paiement dépasse le reste à payer de la facture.');
        }

        $validated['reference'] = 'PAY-'.Str::upper(Str::random(8));
        $validated['customer_id'] = $invoice->customer_id;
        $validated['domain_id'] = $invoice->domain_id;
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'valide';

        $payment = Payment::create($validated);

        // Update invoice
        $invoice->paid_amount += $validated['amount'];
        if ($invoice->paid_amount >= $invoice->total) {
            $invoice->status = 'payee';
        } else {
            $invoice->status = 'partielle';
        }
        $invoice->save();

        ActivityLogger::log('created_payment', 'Enregistrement du paiement '.$payment->reference, $payment);

        return back()->with('success', 'Paiement enregistré avec succès.');
    }
}
