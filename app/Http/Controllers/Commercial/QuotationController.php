<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quotation;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::with(['customer', 'user']);

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

        $quotations = $query->latest()->paginate(15);

        return view('commercial.quotations.index', compact('quotations'));
    }

    public function create()
    {
        $customers = Customer::active()->orderBy('name')->get();
        $domains = Domain::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get(['id', 'name', 'selling_price', 'tax_rate', 'sku']);

        return view('commercial.quotations.create_edit', compact('customers', 'domains', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'domain_id' => 'nullable|exists:domains,id',
            'date' => 'required|date',
            'valid_until' => 'nullable|date',
            'status' => 'required|in:brouillon,envoyé,accepté,refusé,expiré',
            'notes' => 'nullable|string',
            'conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        $validated['reference'] = 'DEV-'.Str::upper(Str::random(8));
        $validated['user_id'] = auth()->id() ?? 1;

        $quotation = Quotation::create(array_diff_key($validated, array_flip(['items'])));

        foreach ($validated['items'] as $item) {
            $quantity = $item['quantity'];
            $price = $item['unit_price'];
            $tax_rate = $item['tax_rate'] ?? 0;
            $discount = $item['discount'] ?? 0;

            $subtotal = $quantity * $price;
            $tax_amount = max(0, $subtotal - $discount) * ($tax_rate / 100);
            $total = max(0, $subtotal - $discount) + $tax_amount;

            $quotation->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $price,
                'tax_rate' => $tax_rate,
                'tax_amount' => $tax_amount,
                'discount' => $discount,
                'total' => $total,
            ]);
        }

        $quotation->calculateTotals();
        ActivityLogger::log('created_quotation', 'Création du devis '.$quotation->reference, $quotation);

        return redirect()->route('commercial.quotations.index')->with('success', 'Devis créé avec succès.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['items.product', 'customer', 'user', 'domain']);

        return view('commercial.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load('items');
        $customers = Customer::active()->orderBy('name')->get();
        $domains = Domain::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get(['id', 'name', 'selling_price', 'tax_rate', 'sku']);

        return view('commercial.quotations.create_edit', compact('quotation', 'customers', 'domains', 'products'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'domain_id' => 'nullable|exists:domains,id',
            'date' => 'required|date',
            'valid_until' => 'nullable|date',
            'status' => 'required|in:brouillon,envoyé,accepté,refusé,expiré',
            'notes' => 'nullable|string',
            'conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        $quotation->update(array_diff_key($validated, array_flip(['items'])));
        $quotation->items()->delete();

        foreach ($validated['items'] as $item) {
            $quantity = $item['quantity'];
            $price = $item['unit_price'];
            $tax_rate = $item['tax_rate'] ?? 0;
            $discount = $item['discount'] ?? 0;

            $subtotal = $quantity * $price;
            $tax_amount = max(0, $subtotal - $discount) * ($tax_rate / 100);
            $total = max(0, $subtotal - $discount) + $tax_amount;

            $quotation->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $price,
                'tax_rate' => $tax_rate,
                'tax_amount' => $tax_amount,
                'discount' => $discount,
                'total' => $total,
            ]);
        }

        $quotation->calculateTotals();
        ActivityLogger::log('updated_quotation', 'Modification du devis '.$quotation->reference, $quotation);

        return redirect()->route('commercial.quotations.index')->with('success', 'Devis mis à jour avec succès.');
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();
        ActivityLogger::log('deleted_quotation', 'Suppression du devis '.$quotation->reference, $quotation);

        return redirect()->route('commercial.quotations.index')->with('success', 'Devis supprimé avec succès.');
    }

    public function send(Quotation $quotation)
    {
        $quotation->update(['status' => 'envoyé']);
        ActivityLogger::log('sent_quotation', 'Devis marqué comme envoyé: '.$quotation->reference, $quotation);

        return back()->with('success', 'Le statut du devis a été mis à jour.');
    }

    public function convertToOrder(Quotation $quotation)
    {
        $order = Order::create([
            'reference' => 'CMD-'.Str::upper(Str::random(8)),
            'quotation_id' => $quotation->id,
            'customer_id' => $quotation->customer_id,
            'domain_id' => $quotation->domain_id,
            'user_id' => auth()->id(),
            'date' => now(),
            'status' => 'en_attente',
            'subtotal' => $quotation->subtotal,
            'tax_amount' => $quotation->tax_amount,
            'discount' => $quotation->discount,
            'total' => $quotation->total,
            'notes' => $quotation->notes,
        ]);

        foreach ($quotation->items as $item) {
            $order->items()->create($item->toArray());
        }

        $quotation->update(['status' => 'accepté']);
        ActivityLogger::log('converted_quotation', 'Conversion du devis '.$quotation->reference.' en commande', $quotation);

        return redirect()->route('commercial.orders.show', $order)->with('success', 'Devis converti en commande avec succès.');
    }

    public function print(Quotation $quotation)
    {
        $quotation->load(['items.product', 'customer', 'user', 'domain']);

        return view('commercial.quotations.print', compact('quotation'));
    }
}
