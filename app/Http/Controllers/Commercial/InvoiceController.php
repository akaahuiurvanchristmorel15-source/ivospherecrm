<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'user', 'order']);

        if ($request->filled('search')) {
            $query->where('reference', 'like', "%{$request->search}%");
        }

        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $invoices = $query->latest()->paginate(15);

        return view('commercial.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();
        $domains = Domain::where('is_active', true)->orderBy('name')->get();

        return view('commercial.invoices.create_edit', compact('customers', 'products', 'domains'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'domain_id' => 'nullable|exists:domains,id',
            'date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'required|in:non_payee,partielle,payee,annulee',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        $validated['reference'] = 'FAC-'.Str::upper(Str::random(8));
        $validated['user_id'] = auth()->id();
        $validated['type'] = 'standard';
        $validated['paid_amount'] = 0;

        $invoice = Invoice::create(array_diff_key($validated, array_flip(['items'])));

        $subtotal = 0;
        $tax_amount = 0;
        $discount = 0;
        foreach ($validated['items'] as $item) {
            $q = $item['quantity'];
            $p = $item['unit_price'];
            $tr = $item['tax_rate'] ?? 0;
            $d = $item['discount'] ?? 0;
            $st = $q * $p;
            $ta = ($st - $d) * ($tr / 100);
            $t = $st - $d + $ta;

            $subtotal += $st;
            $tax_amount += $ta;
            $discount += $d;

            $invoice->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $q, 'unit_price' => $p, 'tax_rate' => $tr,
                'tax_amount' => $ta, 'discount' => $d, 'total' => $t,
            ]);
        }

        $invoice->update(['subtotal' => $subtotal, 'tax_amount' => $tax_amount, 'discount' => $discount, 'total' => $subtotal + $tax_amount - $discount]);
        ActivityLogger::log('created_invoice', 'Création de la facture '.$invoice->reference, $invoice);

        return redirect()->route('commercial.invoices.index')->with('success', 'Facture créée avec succès.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['items.product', 'customer', 'user', 'payments']);

        return view('commercial.invoices.show', compact('invoice'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['items.product', 'customer', 'user', 'payments', 'domain']);

        return view('commercial.invoices.print', compact('invoice'));
    }

    public function quickPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:especes,mobile_money,carte,virement,cheque',
            'notes' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        if ($validated['amount'] > $invoice->remaining) {
            return back()->with('error', 'Le montant du paiement ne peut pas dépasser le reste à payer ('.number_format($invoice->remaining, 0, ',', ' ').' FCFA).');
        }

        $payment = Payment::create([
            'reference' => 'PAY-'.Str::upper(Str::random(8)),
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'domain_id' => $invoice->domain_id,
            'user_id' => auth()->id(),
            'date' => $validated['date'] ?? now(),
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'status' => 'valide',
            'notes' => $validated['notes'],
        ]);

        $newPaidAmount = $invoice->paid_amount + $validated['amount'];
        $status = ($newPaidAmount >= $invoice->total) ? 'payee' : 'partielle';

        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'status' => $status,
        ]);

        // Award customer loyalty points
        if ($invoice->customer) {
            $invoice->customer->addLoyaltyPoints((float) $validated['amount']);
        }

        ActivityLogger::log('recorded_payment', 'Règlement rapide de '.number_format($validated['amount'], 0, ',', ' ').' FCFA pour facture '.$invoice->reference, $payment);

        return back()->with('success', 'Règlement de '.number_format($validated['amount'], 0, ',', ' ').' FCFA enregistré avec succès.');
    }
}
