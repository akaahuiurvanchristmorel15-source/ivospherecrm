<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promotion;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $productsQuery = Product::with(['category', 'domain', 'warehouseStocks'])->active();

        if ($request->filled('domain_id')) {
            $productsQuery->where('domain_id', $request->domain_id);
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
        $domains = Domain::active()->get();
        $activePromotions = Promotion::active()->get();

        return view('commercial.pos.index', compact('products', 'customers', 'domains', 'activePromotions'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'domain_id' => 'nullable|exists:domains,id',
            'payment_method' => 'required|in:especes,mobile_money,carte,virement,cheque,credit',
            'mobile_money_provider' => 'nullable|string|in:wave,orange_money',
            'amount_paid' => 'required|numeric|min:0',
            'promo_code' => 'nullable|string',
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

        foreach ($groupedQuantities as $productId => $totalRequestedQty) {
            $product = Product::with('warehouseStocks')->findOrFail($productId);
            $availableStock = (int) $product->available_stock;

            if ($totalRequestedQty > $availableStock) {
                return back()->withInput()->with('error', "Validation impossible : la quantité commandée pour l'article \"{$product->name}\" ({$totalRequestedQty}) est supérieure au stock disponible ({$availableStock}).");
            }
        }

        return DB::transaction(function () use ($request) {
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

            $domainId = $request->domain_id ?? $customer->domain_id ?? Domain::first()?->id;

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

            // Coupon / promo discount
            $globalDiscount = 0;
            if ($request->filled('promo_code')) {
                $promo = Promotion::where('code', $request->promo_code)->first();
                if ($promo && $promo->isValid($subtotal)) {
                    $globalDiscount = $promo->calculateDiscount($subtotal);
                }
            }

            $taxTotal = collect($itemsData)->sum('tax_amount');
            $finalTotal = max(0, ($subtotal - $globalDiscount) + $taxTotal);
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
                'discount' => $globalDiscount,
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
                'discount' => $globalDiscount,
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
            StockService::deductOrderStock($order, 'Sortie vente comptoir (POS)');

            // 5. Add Loyalty Points
            if ($customer->code !== 'CLI-PASSAGER' && $amountPaid > 0) {
                $customer->addLoyaltyPoints($amountPaid);
            }

            ActivityLogger::log('pos_sale', 'Vente comptoir '.$order->reference.' d\'un montant de '.number_format($finalTotal, 0, ',', ' ').' FCFA', $order);

            return redirect()->route('commercial.invoices.print', $invoice)->with('success', 'Vente enregistrée avec succès ! Ticket généré.');
        });
    }
}
