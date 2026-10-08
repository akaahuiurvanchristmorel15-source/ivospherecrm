<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockAlert;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockService
{
    /**
     * Process a stock movement and update alerts
     */
    public static function processMovement(array $data): StockMovement
    {
        return DB::transaction(function () use ($data) {
            $warehouseId = $data['warehouse_id'];
            $productId = $data['product_id'];
            $type = $data['type'];
            $quantity = (int) $data['quantity'];

            $stockBefore = self::getStock($productId, $warehouseId);
            $stockAfter = $stockBefore;

            switch ($type) {
                case 'entree':
                case 'retour':
                    $stockAfter = $stockBefore + $quantity;
                    break;
                case 'sortie':
                    if ($stockBefore < $quantity) {
                        throw new Exception("Stock insuffisant pour cette sortie. Disponible : {$stockBefore}");
                    }
                    $stockAfter = $stockBefore - $quantity;
                    break;
                case 'transfert':
                    if ($stockBefore < $quantity) {
                        throw new Exception("Stock insuffisant pour ce transfert. Disponible : {$stockBefore}");
                    }
                    $stockAfter = $stockBefore - $quantity;
                    break;
                case 'ajustement':
                    $stockAfter = $quantity;
                    break;
            }

            $movement = StockMovement::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'domain_id' => $data['domain_id'] ?? null,
                'user_id' => $data['user_id'] ?? auth()->id(),
                'type' => $type,
                'reason_motif' => $data['reason_motif'] ?? null,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'unit_cost' => $data['unit_cost'] ?? null,
                'reference' => $data['reference'] ?? strtoupper(uniqid('MVT-')),
                'batch_number' => $data['batch_number'] ?? null,
                'destination_warehouse_id' => $data['destination_warehouse_id'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'date' => $data['date'] ?? now(),
            ]);

            WarehouseStock::updateOrCreate(
                ['warehouse_id' => $warehouseId, 'product_id' => $productId],
                ['physical_quantity' => $stockAfter]
            );

            if ($type === 'transfert' && isset($data['destination_warehouse_id'])) {
                $destWarehouseId = $data['destination_warehouse_id'];
                $destStockBefore = self::getStock($productId, $destWarehouseId);
                $destStockAfter = $destStockBefore + $quantity;

                StockMovement::create([
                    'warehouse_id' => $destWarehouseId,
                    'product_id' => $productId,
                    'domain_id' => $data['domain_id'] ?? null,
                    'user_id' => $data['user_id'] ?? auth()->id(),
                    'type' => 'entree',
                    'reason_motif' => 'transfert_entrant',
                    'quantity' => $quantity,
                    'stock_before' => $destStockBefore,
                    'stock_after' => $destStockAfter,
                    'reference' => $movement->reference.'-IN',
                    'notes' => 'Transfert depuis l\'entrepôt '.$warehouseId,
                    'date' => $data['date'] ?? now(),
                ]);

                WarehouseStock::updateOrCreate(
                    ['warehouse_id' => $destWarehouseId, 'product_id' => $productId],
                    ['physical_quantity' => $destStockAfter]
                );

                self::checkProductAlert($productId, $destWarehouseId, $destStockAfter);
            }

            self::checkProductAlert($productId, $warehouseId, $stockAfter);

            ActivityLogger::log('create_stock_movement', "Mouvement de stock ({$type}) de {$quantity} unité(s)", $movement);

            return $movement;
        });
    }

    /**
     * Get current stock for a product in a specific warehouse
     */
    public static function getStock(int $productId, int $warehouseId): int
    {
        $ws = WarehouseStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($ws !== null) {
            return (int) $ws->physical_quantity;
        }

        $lastMovement = StockMovement::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('id', 'desc')
            ->first();

        return $lastMovement ? (int) $lastMovement->stock_after : 0;
    }

    /**
     * Déduit automatiquement le stock physique et enregistre le mouvement lors d'une vente.
     * Cible avec précision le ou les entrepôts détenant physiquement le stock de l'article.
     *
     * @return array<StockMovement>
     */
    public static function deductSaleStock(int $productId, int $quantity, ?int $preferredDomainId = null, ?int $orderId = null, ?string $reference = null, ?string $notes = null): array
    {
        return DB::transaction(function () use ($productId, $quantity, $preferredDomainId, $orderId, $reference, $notes) {
            $product = Product::findOrFail($productId);
            $remaining = $quantity;
            $movements = [];

            // 1. Récupérer les entrepôts où ce produit a du stock physique
            $stocks = WarehouseStock::where('product_id', $productId)
                ->where('physical_quantity', '>', 0)
                ->get();

            // Prioriser l'entrepôt rattaché au domaine s'il a du stock
            if ($preferredDomainId && $stocks->count() > 1) {
                $preferredWarehouse = Warehouse::where('domain_id', $preferredDomainId)->first();
                if ($preferredWarehouse) {
                    $stocks = $stocks->sortByDesc(fn ($s) => $s->warehouse_id === $preferredWarehouse->id ? 1 : 0);
                }
            }

            if ($stocks->isNotEmpty()) {
                foreach ($stocks as $ws) {
                    if ($remaining <= 0) {
                        break;
                    }

                    $deduct = min($ws->physical_quantity, $remaining);
                    $stockBefore = $ws->physical_quantity;
                    $stockAfter = $stockBefore - $deduct;

                    $ws->physical_quantity = $stockAfter;
                    $ws->save();

                    $movement = StockMovement::create([
                        'warehouse_id' => $ws->warehouse_id,
                        'product_id' => $productId,
                        'domain_id' => $preferredDomainId ?? $product->domain_id,
                        'user_id' => auth()->id(),
                        'type' => 'sortie',
                        'reason_motif' => 'vente',
                        'quantity' => $deduct,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference' => $reference ?? ('VTE-'.strtoupper(Str::random(8))),
                        'order_id' => $orderId,
                        'date' => now(),
                        'notes' => $notes ?? 'Sortie vente automatique',
                    ]);

                    self::checkProductAlert($productId, $ws->warehouse_id, $stockAfter);
                    $movements[] = $movement;
                    $remaining -= $deduct;
                }
            }

            // 2. Si le produit n'a pas encore de ligne WarehouseStock > 0 (fallback)
            if ($remaining > 0) {
                $fallbackWarehouse = ($preferredDomainId ? Warehouse::where('domain_id', $preferredDomainId)->first() : null)
                    ?? Warehouse::first();

                if ($fallbackWarehouse) {
                    $stockBefore = (int) $product->current_stock;
                    $stockAfter = max(0, $stockBefore - $remaining);

                    $ws = WarehouseStock::firstOrCreate(
                        ['warehouse_id' => $fallbackWarehouse->id, 'product_id' => $productId],
                        ['physical_quantity' => $stockBefore, 'reserved_quantity' => 0, 'incoming_quantity' => 0]
                    );
                    $ws->physical_quantity = $stockAfter;
                    $ws->save();

                    $movement = StockMovement::create([
                        'warehouse_id' => $fallbackWarehouse->id,
                        'product_id' => $productId,
                        'domain_id' => $preferredDomainId ?? $product->domain_id,
                        'user_id' => auth()->id(),
                        'type' => 'sortie',
                        'reason_motif' => 'vente',
                        'quantity' => $remaining,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference' => $reference ?? ('VTE-'.strtoupper(Str::random(8))),
                        'order_id' => $orderId,
                        'date' => now(),
                        'notes' => $notes ?? 'Sortie vente automatique',
                    ]);

                    self::checkProductAlert($productId, $fallbackWarehouse->id, $stockAfter);
                    $movements[] = $movement;
                }
            }

            ActivityLogger::log('stock_deducted_sale', "Sortie automatique de {$quantity} unité(s) pour {$product->name}", $product);

            return $movements;
        });
    }

    /**
     * Déduit automatiquement le stock de tous les produits d'une commande lors de sa livraison.
     * Cette méthode est idempotente (ne déduit pas deux fois si déjà déduit pour cette commande).
     *
     * @return array<StockMovement>
     */
    public static function deductOrderStock(Order $order, ?string $notes = null): array
    {
        if (! in_array($order->status, ['livrée', 'livree'])) {
            return [];
        }

        $alreadyDeducted = StockMovement::where('order_id', $order->id)
            ->where('type', 'sortie')
            ->exists();

        if ($alreadyDeducted) {
            return [];
        }

        $movements = [];
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if ($item->product_id && (int) $item->quantity > 0) {
                $mvts = self::deductSaleStock(
                    productId: (int) $item->product_id,
                    quantity: (int) $item->quantity,
                    preferredDomainId: $order->domain_id,
                    orderId: $order->id,
                    reference: $order->reference,
                    notes: $notes ?? ('Sortie vente commande livrée ('.$order->reference.')')
                );
                $movements = array_merge($movements, $mvts);
            }
        }

        return $movements;
    }

    /**
     * Get current stock for a product across all warehouses
     */
    public static function getTotalStock(int $productId): int
    {
        $hasWs = WarehouseStock::where('product_id', $productId)->exists();
        if ($hasWs) {
            return (int) WarehouseStock::where('product_id', $productId)->sum('physical_quantity');
        }

        $latestMovementsIds = StockMovement::where('product_id', $productId)
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('warehouse_id')
            ->pluck('id');

        return (int) StockMovement::whereIn('id', $latestMovementsIds)->sum('stock_after');
    }

    /**
     * Check stock alerts
     */
    public static function checkAlerts(): Collection
    {
        return StockAlert::with(['product', 'warehouse'])->active()->get();
    }

    private static function checkProductAlert(int $productId, int $warehouseId, int $currentStock): void
    {
        $product = Product::find($productId);
        if (! $product) {
            return;
        }

        $minStock = $product->min_stock ?? 10;

        if ($currentStock <= $minStock) {
            StockAlert::updateOrCreate(
                [
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'status' => 'active',
                ],
                [
                    'min_quantity' => $minStock,
                    'current_quantity' => $currentStock,
                    'is_notified' => false,
                ]
            );
        } else {
            StockAlert::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->where('status', 'active')
                ->update(['status' => 'resolved']);
        }
    }
}
