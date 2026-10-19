<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Les ventes de la caisse (POS) étaient rattachées au premier domaine ou à celui du client de passage,
     * et non à celui des articles vendus : on réattribue chaque vente au domaine qui pèse le plus dans le ticket.
     */
    public function up(): void
    {
        $dominantDomains = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.reference', 'like', 'POS-%')
            ->whereNotNull('products.domain_id')
            ->groupBy('order_items.order_id', 'products.domain_id')
            ->selectRaw('order_items.order_id as order_id, products.domain_id as domain_id, SUM(order_items.total) as amount')
            ->get()
            ->groupBy('order_id')
            ->map(fn ($rows) => $rows->sortByDesc('amount')->first()->domain_id);

        foreach ($dominantDomains as $orderId => $domainId) {
            DB::table('orders')->where('id', $orderId)->update(['domain_id' => $domainId]);

            $invoiceIds = DB::table('invoices')->where('order_id', $orderId)->pluck('id');
            DB::table('invoices')->whereIn('id', $invoiceIds)->update(['domain_id' => $domainId]);
            DB::table('payments')->whereIn('invoice_id', $invoiceIds)->update(['domain_id' => $domainId]);
        }
    }

    public function down(): void
    {
        // Correction de données : pas de retour arrière.
    }
};
