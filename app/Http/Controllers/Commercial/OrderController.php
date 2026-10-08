<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'user']);

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

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('commercial.orders.index', compact('orders'));
    }

    public function create()
    {
        $customers = Customer::active()->orderBy('name')->get();
        $products = Product::where('status', 'actif')->orWhere('is_active', true)->orderBy('name')->get();
        $domains = Domain::where('is_active', true)->orderBy('name')->get();

        return view('commercial.orders.create_edit', compact('customers', 'products', 'domains'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'domain_id' => 'nullable|exists:domains,id',
            'date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'status' => 'required|in:en_attente,confirmée,en_cours,livrée,annulée',
            'notes' => 'nullable|string',
            'delivery_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        $validated['reference'] = 'CMD-'.Str::upper(Str::random(8));
        $validated['user_id'] = auth()->id();

        $order = Order::create(array_diff_key($validated, array_flip(['items'])));

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

            $order->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $q, 'unit_price' => $p, 'tax_rate' => $tr,
                'tax_amount' => $ta, 'discount' => $d, 'total' => $t,
            ]);
        }

        $order->update(['subtotal' => $subtotal, 'tax_amount' => $tax_amount, 'discount' => $discount, 'total' => $subtotal + $tax_amount - $discount]);
        StockService::deductOrderStock($order);
        ActivityLogger::log('created_order', 'Création de la commande '.$order->reference, $order);

        return redirect()->route('commercial.orders.index')->with('success', 'Commande créée avec succès.');
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'customer', 'user', 'invoices']);

        return view('commercial.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $order->load(['items.product', 'customer']);
        $customers = Customer::active()->orderBy('name')->get();
        $products = Product::where('status', 'actif')->orWhere('is_active', true)->orderBy('name')->get();
        $domains = Domain::where('is_active', true)->orderBy('name')->get();

        return view('commercial.orders.create_edit', compact('order', 'customers', 'products', 'domains'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'domain_id' => 'nullable|exists:domains,id',
            'date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'status' => 'required|in:en_attente,confirmée,en_cours,livrée,annulée',
            'notes' => 'nullable|string',
            'delivery_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        $order->update(array_diff_key($validated, array_flip(['items'])));
        $order->items()->delete();

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

            $order->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $q, 'unit_price' => $p, 'tax_rate' => $tr,
                'tax_amount' => $ta, 'discount' => $d, 'total' => $t,
            ]);
        }

        $order->update(['subtotal' => $subtotal, 'tax_amount' => $tax_amount, 'discount' => $discount, 'total' => $subtotal + $tax_amount - $discount]);
        StockService::deductOrderStock($order);
        ActivityLogger::log('updated_order', 'Modification de la commande '.$order->reference, $order);

        return redirect()->route('commercial.orders.index')->with('success', 'Commande mise à jour avec succès.');
    }

    public function destroy(Order $order)
    {
        $order->delete();
        ActivityLogger::log('deleted_order', 'Suppression de la commande '.$order->reference, $order);

        return redirect()->route('commercial.orders.index')->with('success', 'Commande supprimée avec succès.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|in:en_attente,confirmée,en_cours,livrée,annulée']);
        $order->update(['status' => $request->status]);
        StockService::deductOrderStock($order);
        ActivityLogger::log('status_order', 'Mise à jour du statut de la commande: '.$order->reference, $order);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function generateInvoice(Order $order)
    {
        $invoice = Invoice::create([
            'reference' => 'FAC-'.Str::upper(Str::random(8)),
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'domain_id' => $order->domain_id,
            'user_id' => auth()->id(),
            'type' => 'standard',
            'date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'non_payee',
            'subtotal' => $order->subtotal,
            'tax_amount' => $order->tax_amount,
            'discount' => $order->discount,
            'total' => $order->total,
            'paid_amount' => 0,
        ]);

        foreach ($order->items as $item) {
            $invoice->items()->create($item->toArray());
        }

        ActivityLogger::log('generated_invoice', 'Facture générée depuis la commande '.$order->reference, $invoice);

        return redirect()->route('commercial.invoices.show', $invoice)->with('success', 'Facture générée avec succès.');
    }
}
