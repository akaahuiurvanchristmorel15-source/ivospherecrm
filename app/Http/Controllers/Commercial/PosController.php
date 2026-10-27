<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $productsQuery = Product::with(['category', 'domain', 'warehouseStocks.warehouse:id,name,label_number,label_color'])->active();

        if ($request->filled('warehouse_id')) {
            $productsQuery->whereHas('warehouseStocks', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id)->where('physical_quantity', '>', 0);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $productsQuery->orderBy('name')->get();
        $customers = Customer::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('label_number')->orderBy('name')->get(['id', 'name', 'label_number', 'label_color']);
        $selectedWarehouseId = $warehouses->firstWhere('id', (int) $request->warehouse_id)?->id;

        return view('commercial.pos.index', compact('products', 'customers', 'warehouses', 'selectedWarehouseId'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'domain_id' => 'nullable|exists:domains,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'payment_method' => 'required|in:especes,mobile_money,carte,virement,cheque,credit',
            'mobile_money_provider' => 'nullable|string|in:wave,orange_money',
            'amount_paid' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Contrôle strict : la quantité commandée ne doit pas être supérieure au stock disponible
        $groupedQuantities = [];
        foreach ($request->items as $item) {
            $pid = (int) $item['product_id'];
            $groupedQuantities[$pid] = ($groupedQuantities[$pid] ?? 0) + (int) $item['quantity'];
        }

        $warehouseId = $request->filled('warehouse_id') ? (int) $request->warehouse_id : null;

        foreach ($groupedQuantities as $productId => $totalRequestedQty) {
            $product = Product::with('warehouseStocks')->findOrFail($productId);
            $availableStock = $warehouseId === null
                ? (int) $product->available_stock
                : (int) $product->warehouseStocks->where('warehouse_id', $warehouseId)->sum(fn ($ws) => max(0, $ws->physical_quantity - $ws->reserved_quantity));

            if ($totalRequestedQty > $availableStock) {
                return back()->withInput()->with('error', "Validation impossible : la quantité commandée pour l'article \"{$product->name}\" ({$totalRequestedQty}) est supérieure au stock disponible ({$availableStock}).");
            }
        }

        return DB::transaction(function () use ($request, $warehouseId) {
            $customer = null;
            if ($request->filled('customer_id')) {
                $customer = Customer::find($request->customer_id);
            } else {
                // Find or create default walk-in customer
                $customer = Customer::firstOrCreate(
                    ['code' => 'CLI-PASSAGER'],
                    [
                        'name' => 'Client Comptoir / Passager',
                        'type' => 'particulier',
                        'status' => 'actif',
                        'domain_id' => $request->domain_id,
                        'user_id' => auth()->id(),
                    ]
                );
            }

            // Domaine de la vente : celui des articles vendus (le plus important en montant si plusieurs domaines)
            $domainId = self::dominantProductDomain($request->items) ?? $request->domain_id ?? Domain::first()?->id;

            // Calculate totals
            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $qty = (int) $item['quantity'];
                $price = (float) $item['unit_price'];
                $itemDiscount = (float) ($item['discount'] ?? 0);
                $lineSubtotal = ($qty * $price) - $itemDiscount;

                $subtotal += $lineSubtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'description' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $product->tax_rate ?? 0,
                    'tax_amount' => $lineSubtotal * (($product->tax_rate ?? 0) / 100),
                    'discount' => $itemDiscount,
                    'total' => $lineSubtotal * (1 + (($product->tax_rate ?? 0) / 100)),
                ];
            }

            $taxTotal = collect($itemsData)->sum('tax_amount');
            $finalTotal = max(0, $subtotal + $taxTotal);
            $amountPaid = min($finalTotal, (float) $request->amount_paid);

            // 1. Create Order
            $order = Order::create([
                'reference' => 'POS-'.strtoupper(Str::random(8)),
                'customer_id' => $customer->id,
                'domain_id' => $domainId,
                'user_id' => auth()->id(),
                'date' => now(),
                'status' => 'livree',
                'subtotal' => $subtotal,
                'tax_amount' => $taxTotal,
                'discount' => 0,
                'total' => $finalTotal,
                'notes' => 'Vente directe comptoir (POS). '.$request->notes,
            ]);

            foreach ($itemsData as $itemRow) {
                $order->items()->create($itemRow);
            }

            // 2. Create Invoice
            $invoiceStatus = ($amountPaid >= $finalTotal && $finalTotal > 0) ? 'payee' : (($amountPaid > 0) ? 'partielle' : 'non_payee');

            $invoice = Invoice::create([
                'reference' => 'FAC-POS-'.strtoupper(Str::random(8)),
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'domain_id' => $domainId,
                'user_id' => auth()->id(),
                'date' => now(),
                'due_date' => now(),
                'type' => 'standard',
                'status' => $invoiceStatus,
                'subtotal' => $subtotal,
                'tax_amount' => $taxTotal,
                'discount' => 0,
                'total' => $finalTotal,
                'paid_amount' => $amountPaid,
                'notes' => 'Ticket de caisse / Facture POS.',
            ]);

            foreach ($itemsData as $itemRow) {
                $invoice->items()->create($itemRow);
            }

            // 3. Create Payment if paid
            if ($amountPaid > 0 && $request->payment_method !== 'credit') {
                $paymentNotes = 'Paiement comptoir POS';
                if ($request->payment_method === 'mobile_money' && $request->filled('mobile_money_provider')) {
                    $providerLabel = $request->mobile_money_provider === 'wave' ? 'Wave' : 'Orange Money';
                    $paymentNotes = 'Paiement Mobile Money ('.$providerLabel.')';
                }

                Payment::create([
                    'reference' => 'PAY-POS-'.strtoupper(Str::random(8)),
                    'invoice_id' => $invoice->id,
                    'customer_id' => $customer->id,
                    'domain_id' => $domainId,
                    'user_id' => auth()->id(),
                    'date' => now(),
                    'amount' => $amountPaid,
                    'method' => $request->payment_method,
                    'status' => 'valide',
                    'notes' => $paymentNotes,
                ]);
            }

            // 4. Décrémentation automatique des stocks physiques vendus
            StockService::deductOrderStock($order, 'Sortie vente comptoir (POS)', $warehouseId);

            // 5. Add Loyalty Points
            if ($customer->code !== 'CLI-PASSAGER' && $amountPaid > 0) {
                $customer->addLoyaltyPoints($amountPaid);
            }

            ActivityLogger::log('pos_sale', 'Vente comptoir '.$order->reference.' d\'un montant de '.number_format($finalTotal, 0, ',', ' ').' FCFA', $order);

            return redirect()->route('commercial.invoices.print', $invoice)->with('success', 'Vente enregistrée avec succès ! Ticket généré.');
        });
    }

    /**
     * Domaine qui pèse le plus dans le ticket, d'après le domaine de chaque article vendu.
     *
     * @param  array<int, array{product_id: int|string, quantity: int|string, unit_price: float|int|string, discount?: float|int|string|null}>  $items
     */
    public static function dominantProductDomain(array $items): ?int
    {
        $domains = Product::whereIn('id', collect($items)->pluck('product_id'))->pluck('domain_id', 'id');
        $amounts = [];

        foreach ($items as $item) {
            $domainId = $domains[(int) $item['product_id']] ?? null;
            if ($domainId !== null) {
                $line = (float) $item['quantity'] * (float) $item['unit_price'] - (float) ($item['discount'] ?? 0);
                $amounts[$domainId] = ($amounts[$domainId] ?? 0) + max(0, $line);
            }
        }

        if ($amounts === []) {
            return null;
        }

        arsort($amounts);

        return (int) array_key_first($amounts);
    }
}
