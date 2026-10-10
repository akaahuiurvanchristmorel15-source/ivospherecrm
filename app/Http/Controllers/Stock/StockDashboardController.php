<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\EquipmentMaintenance;
use App\Models\PhysicalInventory;
use App\Models\PhysicalInventoryItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Promotion;
use App\Models\PurchaseRequest;
use App\Models\StockAlert;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\SupplierReception;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\ActivityLogger;
use App\Services\StockRevenueBoosterService;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockDashboardController extends Controller
{
    public function index(Request $request)
    {
        $activeWarehousesCount = Warehouse::active()->count();
        $movementsToday = StockMovement::whereDate('date', today())->count();
        $activeAlertsCount = StockAlert::active()->count();

        $domains = Domain::all();
        $warehouses = Warehouse::active()->with(['warehouseStocks.product'])->get();
        $suppliers = Supplier::orderBy('name')->get();

        // Products with full WMS enrichment
        $productsQuery = Product::with(['domain', 'category', 'brand', 'warehouseStocks.warehouse', 'batches'])
            ->where('is_active', true);

        if ($request->filled('domain_id')) {
            $productsQuery->where('domain_id', $request->domain_id);
        }

        // Optimized batch queries for exits & movement dates (prevents N+1 queries)
        $exitCounts = StockMovement::where('type', 'sortie')
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(quantity) as total_exits')
            ->pluck('total_exits', 'product_id');

        $lastMoveDates = StockMovement::groupBy('product_id')
            ->selectRaw('product_id, MAX(date) as last_date')
            ->pluck('last_date', 'product_id');

        $products = $productsQuery->orderBy('name')->get()->map(function (Product $product) use ($exitCounts, $lastMoveDates) {
            $lastDate = $lastMoveDates[$product->id] ?? null;
            $exitCount = (int) ($exitCounts[$product->id] ?? 0);

            $daysDormant = $lastDate
                ? (int) now()->diffInDays(Carbon::parse($lastDate))
                : 120;

            if ($daysDormant >= 90 && $exitCount == 0) {
                $rotation = 'dormant';
            } elseif ($exitCount >= 50) {
                $rotation = 'tres_vendu';
            } elseif ($exitCount >= 15) {
                $rotation = 'moyen';
            } else {
                $rotation = 'peu_vendu';
            }

            $product->last_movement_at = $lastDate;
            $product->days_without_movement = $daysDormant;
            $product->exit_volume = $exitCount;
            $product->rotation_class = $rotation;

            $maxTarget = max((int) ($product->max_stock ?: ($product->min_stock * 3 ?: 50)), 10);
            $product->effective_max_stock = $maxTarget;
            $product->suggested_order_qty = max(0, $maxTarget - (int) $product->current_stock);

            return $product;
        });

        // Hub d'Optimisation des Revenus : Analyse approfondie des Tops Ventes et leviers de croissance
        $revenueBoosterService = new StockRevenueBoosterService;
        $revenueOptimization = $revenueBoosterService->analyze($products);
        $topSellersAnalyzed = $revenueOptimization['top_sellers'];
        $revenueMetrics = $revenueOptimization['portfolio_metrics'];
        $dormantCandidates = $revenueOptimization['dormant_candidates'];

        // 7 Top KPI Metrics
        $totalProductsCount = $products->count();
        $totalStockItems = $products->sum('current_stock');

        $availableStockValue = (float) $products->sum(function (Product $p) {
            $cost = (float) ($p->purchase_price > 0 ? $p->purchase_price : ($p->selling_price * 0.65));

            return max(0, (int) $p->available_stock) * $cost;
        });

        $potentialSellingValue = (float) $products->sum(function (Product $p) {
            return max(0, (int) $p->current_stock) * (float) ($p->selling_price ?? 0);
        });

        $immobilizedCostValue = (float) $products->sum(function (Product $p) {
            $cost = (float) ($p->purchase_price > 0 ? $p->purchase_price : ($p->selling_price * 0.65));

            return max(0, (int) $p->current_stock) * $cost;
        });

        $lowStockProducts = $products->filter(fn (Product $p) => $p->current_stock > 0 && $p->current_stock <= max(1, (int) $p->min_stock));
        $outOfStockProducts = $products->filter(fn (Product $p) => $p->current_stock <= 0);
        $overstockProducts = $products->filter(fn (Product $p) => $p->max_stock > 0 && $p->current_stock > $p->max_stock);
        $dormantProducts = $products->filter(fn (Product $p) => $p->days_without_movement >= 90);
        $topSellingProducts = $products->sortByDesc('exit_volume')->take(6);
        $replenishmentSuggestions = $products->filter(fn (Product $p) => $p->current_stock <= max(1, (int) $p->min_stock) && $p->suggested_order_qty > 0);

        $lowStockCount = $lowStockProducts->count();
        $outOfStockCount = $outOfStockProducts->count();
        $overstockCount = $overstockProducts->count();

        $entriesCount = StockMovement::where('type', 'entree')->count();
        $exitsCount = StockMovement::where('type', 'sortie')->count();
        $transfersCount = StockTransfer::count() + StockMovement::where('type', 'transfert')->count();

        $recentMovements = StockMovement::with(['warehouse', 'product', 'user', 'destinationWarehouse'])
            ->orderBy('created_at', 'desc')
            ->take(25)
            ->get();

        $warehouseStocks = WarehouseStock::with(['warehouse', 'product.domain'])
            ->orderBy('warehouse_id')
            ->get();

        $transfers = StockTransfer::with(['sourceWarehouse', 'destinationWarehouse', 'product', 'user'])
            ->latest()
            ->get();

        $inventories = PhysicalInventory::with(['warehouse', 'user', 'items.product'])
            ->latest()
            ->get();

        $purchaseRequests = PurchaseRequest::with(['product', 'warehouse', 'supplier', 'user'])
            ->latest()
            ->get();

        $receptions = SupplierReception::with(['warehouse', 'supplier', 'product', 'user'])
            ->latest()
            ->get();

        $batches = ProductBatch::with(['product', 'warehouse'])
            ->orderBy('expiration_date')
            ->get();

        $maintenances = EquipmentMaintenance::with(['product'])
            ->latest()
            ->get();

        return view('stock.index', compact(
            'activeWarehousesCount',
            'movementsToday',
            'activeAlertsCount',
            'totalStockItems',
            'totalProductsCount',
            'availableStockValue',
            'potentialSellingValue',
            'immobilizedCostValue',
            'lowStockCount',
            'outOfStockCount',
            'overstockCount',
            'entriesCount',
            'exitsCount',
            'transfersCount',
            'lowStockProducts',
            'outOfStockProducts',
            'overstockProducts',
            'dormantProducts',
            'topSellingProducts',
            'topSellersAnalyzed',
            'revenueMetrics',
            'dormantCandidates',
            'replenishmentSuggestions',
            'recentMovements',
            'warehouses',
            'warehouseStocks',
            'products',
            'domains',
            'suppliers',
            'transfers',
            'inventories',
            'purchaseRequests',
            'receptions',
            'batches',
            'maintenances'
        ));
    }

    public function scanner()
    {
        $warehouses = Warehouse::active()->get();
        $products = Product::with(['domain', 'warehouseStocks'])->where('is_active', true)->orderBy('name')->get();
        $recentScans = StockMovement::with(['product', 'warehouse', 'user'])->latest()->take(8)->get();

        return view('stock.scanner', compact('warehouses', 'products', 'recentScans'));
    }

    public function scannerAction(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'action_type' => 'required|in:entree,sortie,transfert,inventaire',
            'warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'nullable|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'reason_motif' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        $code = trim($validated['code']);
        $product = Product::where('sku', $code)
            ->orWhere('barcode', $code)
            ->orWhere('id', is_numeric($code) ? (int) $code : 0)
            ->first();

        if (! $product) {
            return back()->with('error', "Aucun produit trouvé pour le code-barres / SKU : {$code}");
        }

        $movementType = $validated['action_type'] === 'inventaire' ? 'ajustement' : $validated['action_type'];

        StockService::processMovement([
            'warehouse_id' => $validated['warehouse_id'],
            'destination_warehouse_id' => $validated['destination_warehouse_id'] ?? null,
            'product_id' => $product->id,
            'type' => $movementType,
            'reason_motif' => $validated['reason_motif'] ?? ($movementType === 'entree' ? 'scan_reception' : 'scan_magasinier'),
            'quantity' => $validated['quantity'],
            'reference' => 'SCAN-'.strtoupper(substr(uniqid(), -5)),
            'notes' => $validated['notes'] ?? "Opération rapide par scan ({$product->sku})",
            'date' => now(),
        ]);

        return back()->with('success', "Scan validé : {$validated['quantity']} x {$product->name} ({$movementType}) traité avec succès.");
    }

    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'execute_immediately' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $executeNow = $request->boolean('execute_immediately', true);

        $transfer = StockTransfer::create([
            'reference' => 'TRF-'.now()->format('Y').'-'.str_pad((string) (StockTransfer::count() + 1), 3, '0', STR_PAD_LEFT).'-'.substr(uniqid(), -3),
            'source_warehouse_id' => $validated['source_warehouse_id'],
            'destination_warehouse_id' => $validated['destination_warehouse_id'],
            'product_id' => $validated['product_id'],
            'user_id' => auth()->id(),
            'quantity' => $validated['quantity'],
            'reason' => $validated['notes'] ?? 'Transfert inter-entrepôts',
            'status' => $executeNow ? 'receptionne' : 'en_attente',
            'shipped_at' => $executeNow ? now() : null,
            'received_at' => $executeNow ? now() : null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($executeNow) {
            StockService::processMovement([
                'warehouse_id' => $transfer->source_warehouse_id,
                'destination_warehouse_id' => $transfer->destination_warehouse_id,
                'product_id' => $transfer->product_id,
                'type' => 'transfert',
                'reason_motif' => 'transfert_inter_entrepots',
                'quantity' => $transfer->quantity,
                'reference' => $transfer->reference,
                'notes' => $transfer->notes ?? 'Transfert inter-entrepôts exécuté',
                'date' => now(),
            ]);
        }

        return back()->with('success', "Transfert {$transfer->reference} enregistré avec succès.");
    }

    public function updateTransferStatus(Request $request, StockTransfer $transfer)
    {
        $validated = $request->validate([
            'status' => 'required|in:valide,expedie,receptionne,annule',
        ]);

        $previousStatus = $transfer->status;
        $newStatus = $validated['status'];

        $transfer->status = $newStatus;
        if ($newStatus === 'expedie') {
            $transfer->shipped_at = now();
        } elseif ($newStatus === 'receptionne' && $previousStatus !== 'receptionne') {
            $transfer->received_at = now();

            StockService::processMovement([
                'warehouse_id' => $transfer->source_warehouse_id,
                'destination_warehouse_id' => $transfer->destination_warehouse_id,
                'product_id' => $transfer->product_id,
                'type' => 'transfert',
                'reason_motif' => 'transfert_inter_entrepots',
                'quantity' => $transfer->quantity,
                'reference' => $transfer->reference,
                'notes' => "Réception du transfert {$transfer->reference}",
                'date' => now(),
            ]);
        }

        $transfer->save();

        return back()->with('success', "Statut du transfert {$transfer->reference} mis à jour : {$newStatus}.");
    }

    public function storeInventory(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'counted_quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:255',
            'auto_validate' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $autoValidate = $request->boolean('auto_validate', true);
            $product = Product::findOrFail($validated['product_id']);

            $inventory = PhysicalInventory::create([
                'reference' => 'INV-'.now()->format('Y').'-'.str_pad((string) (PhysicalInventory::count() + 1), 3, '0', STR_PAD_LEFT).'-'.substr(uniqid(), -3),
                'warehouse_id' => $validated['warehouse_id'],
                'user_id' => auth()->id(),
                'status' => $autoValidate ? 'valide' : 'en_cours',
                'date' => today(),
                'validated_at' => $autoValidate ? now() : null,
                'notes' => $validated['reason'] ?? 'Comptage physique de contrôle',
            ]);

            $systemQty = StockService::getStock($validated['product_id'], $validated['warehouse_id']);
            $countedQty = (int) $validated['counted_quantity'];
            $diff = $countedQty - $systemQty;

            PhysicalInventoryItem::create([
                'physical_inventory_id' => $inventory->id,
                'product_id' => $validated['product_id'],
                'system_quantity' => $systemQty,
                'real_quantity' => $countedQty,
                'discrepancy' => $diff,
                'unit_cost' => (float) ($product->cost_price ?: ($product->unit_price * 0.65)),
                'reason' => $validated['reason'] ?? ($diff === 0 ? 'Conforme' : 'Écart constaté lors du comptage'),
            ]);

            if ($autoValidate && $diff !== 0) {
                StockService::processMovement([
                    'warehouse_id' => $validated['warehouse_id'],
                    'product_id' => $validated['product_id'],
                    'type' => 'ajustement',
                    'reason_motif' => 'regularisation_inventaire',
                    'quantity' => $countedQty,
                    'reference' => $inventory->reference,
                    'notes' => "Régularisation inventaire ({$inventory->reference}) : Système {$systemQty} -> Réel {$countedQty} (Écart: {$diff})",
                    'date' => now(),
                ]);
            }

            return back()->with('success', "Inventaire {$inventory->reference} enregistré (Système: {$systemQty}, Réel: {$countedQty}, Écart: {$diff}).");
        });
    }

    public function storePurchaseRequest(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'requested_quantity' => 'required|integer|min:1',
            'priority' => 'nullable|string|max:30',
            'reason' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $unitCost = (float) ($product->cost_price ?: ($product->unit_price * 0.65));

        $pr = PurchaseRequest::create([
            'reference' => 'DA-'.now()->format('Y').'-'.str_pad((string) (PurchaseRequest::count() + 1), 3, '0', STR_PAD_LEFT).'-'.substr(uniqid(), -3),
            'product_id' => $product->id,
            'warehouse_id' => $validated['warehouse_id'] ?? Warehouse::first()?->id,
            'supplier_id' => $validated['supplier_id'] ?? Supplier::first()?->id,
            'user_id' => auth()->id(),
            'quantity' => $validated['requested_quantity'],
            'estimated_unit_price' => $unitCost,
            'status' => 'en_attente_responsable',
            'urgency' => $validated['priority'] ?? 'haute',
            'reason' => $validated['reason'] ?? "Réapprovisionnement stock ({$product->name})",
        ]);

        return back()->with('success', "Demande d'achat {$pr->reference} créée avec succès ({$pr->quantity} unités).");
    }

    public function updatePurchaseRequestStatus(Request $request, PurchaseRequest $purchaseRequest)
    {
        $validated = $request->validate([
            'status' => 'required|string|max:50',
        ]);

        $purchaseRequest->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', "Workflow de la demande d'achat {$purchaseRequest->reference} avancé vers : {$validated['status']}.");
    }

    public function storeReception(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'product_id' => 'required|exists:products,id',
            'ordered_quantity' => 'required|integer|min:1',
            'received_quantity' => 'required|integer|min:0',
            'damaged_quantity' => 'nullable|integer|min:0',
            'quality_status' => 'required|in:conforme,partiel,non_conforme',
            'batch_number' => 'nullable|string|max:100',
            'expiration_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $damaged = (int) ($validated['damaged_quantity'] ?? 0);
            $missing = max(0, (int) $validated['ordered_quantity'] - (int) $validated['received_quantity']);

            $reception = SupplierReception::create([
                'reference' => 'REC-'.now()->format('Y').'-'.str_pad((string) (SupplierReception::count() + 1), 3, '0', STR_PAD_LEFT).'-'.substr(uniqid(), -3),
                'warehouse_id' => $validated['warehouse_id'],
                'supplier_id' => $validated['supplier_id'] ?? null,
                'product_id' => $validated['product_id'],
                'user_id' => auth()->id(),
                'ordered_quantity' => $validated['ordered_quantity'],
                'received_quantity' => $validated['received_quantity'],
                'missing_quantity' => $missing,
                'damaged_quantity' => $damaged,
                'quality_status' => $validated['quality_status'],
                'batch_number' => $validated['batch_number'] ?? null,
                'expiration_date' => $validated['expiration_date'] ?? null,
                'received_at' => today(),
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($validated['received_quantity'] > 0) {
                StockService::processMovement([
                    'warehouse_id' => $validated['warehouse_id'],
                    'product_id' => $validated['product_id'],
                    'type' => 'entree',
                    'reason_motif' => 'achat_fournisseur',
                    'batch_number' => $validated['batch_number'] ?? null,
                    'quantity' => $validated['received_quantity'],
                    'reference' => $reception->reference,
                    'notes' => "Réception fournisseur {$reception->reference}",
                    'date' => now(),
                ]);
            }

            if (! empty($validated['batch_number'])) {
                ProductBatch::create([
                    'product_id' => $validated['product_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'batch_number' => $validated['batch_number'],
                    'quantity' => $validated['received_quantity'],
                    'manufacturing_date' => now()->subMonth(),
                    'expiration_date' => $validated['expiration_date'] ?? null,
                    'status' => 'disponible',
                ]);
            }

            return back()->with('success', "Réception fournisseur {$reception->reference} enregistrée (+{$validated['received_quantity']} en stock).");
        });
    }

    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'batch_number' => 'required|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'imei' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
            'expiration_date' => 'nullable|date',
            'warranty_months' => 'nullable|integer|min:0',
            'status' => 'nullable|string|max:30',
        ]);

        ProductBatch::create([
            'product_id' => $validated['product_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'batch_number' => $validated['batch_number'],
            'serial_number' => $validated['serial_number'] ?? null,
            'imei' => $validated['imei'] ?? null,
            'quantity' => $validated['quantity'],
            'manufacturing_date' => today(),
            'expiration_date' => $validated['expiration_date'] ?? null,
            'warranty_months' => $validated['warranty_months'] ?? 12,
            'status' => $validated['status'] ?? 'disponible',
        ]);

        return back()->with('success', "Lot / Numéro de série {$validated['batch_number']} enregistré avec succès.");
    }

    public function storeMaintenance(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|string|max:30',
            'issue_description' => 'required|string',
            'cost' => 'nullable|numeric|min:0',
            'technician_name' => 'nullable|string|max:120',
        ]);

        $maint = EquipmentMaintenance::create([
            'reference' => 'MNT-'.now()->format('Y').'-'.str_pad((string) (EquipmentMaintenance::count() + 1), 3, '0', STR_PAD_LEFT).'-'.substr(uniqid(), -3),
            'product_id' => $validated['product_id'],
            'title' => $validated['issue_description'],
            'type' => $validated['type'],
            'status' => 'en_cours',
            'cost' => $validated['cost'] ?? 0,
            'started_at' => today(),
            'technician' => $validated['technician_name'] ?? 'Service Technique IVOSPHERE',
            'notes' => $validated['issue_description'],
        ]);

        return back()->with('success', "Intervention de maintenance {$maint->reference} ouverte.");
    }

    public function completeMaintenance(EquipmentMaintenance $maintenance)
    {
        $maintenance->update([
            'status' => 'termine',
            'completed_at' => today(),
        ]);

        return back()->with('success', "Maintenance {$maintenance->reference} clôturée : l'équipement est de nouveau disponible.");
    }

    public function exportCsv(): StreamedResponse
    {
        $products = Product::with(['domain', 'category'])->where('is_active', true)->orderBy('name')->get();

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Référence SKU',
                'Code-barres',
                'Produit',
                'Domaine',
                'Marque',
                'Unité',
                'Stock Physique',
                'Stock Réservé',
                'Stock Disponible',
                'Stock Min',
                'Stock Max',
                'Prix Achat (FCFA)',
                'Prix Vente (FCFA)',
                'Valeur Immobilisée (FCFA)',
                'Valeur Vente Potentielle (FCFA)',
            ], ';');

            foreach ($products as $p) {
                $cost = (float) ($p->cost_price ?: ($p->unit_price * 0.65));
                fputcsv($handle, [
                    $p->sku,
                    $p->barcode,
                    $p->name,
                    $p->domain?->name ?? 'N/A',
                    $p->brand ?? '-',
                    $p->unit ?? 'pièce',
                    $p->current_stock,
                    $p->reserved_stock,
                    $p->available_stock,
                    $p->min_stock,
                    $p->max_stock,
                    number_format($cost, 0, ',', ' '),
                    number_format((float) $p->unit_price, 0, ',', ' '),
                    number_format($p->current_stock * $cost, 0, ',', ' '),
                    number_format($p->current_stock * (float) $p->unit_price, 0, ',', ' '),
                ], ';');
            }
            fclose($handle);
        }, 'ivosphere-rapport-stock-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Optimisation tarifaire instantanée pour un produit vedette (élasticité & rentabilité).
     */
    public function optimizePrice(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'selling_price' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $oldPrice = (float) $product->selling_price;
        $newPrice = (float) $validated['selling_price'];

        $product->update([
            'selling_price' => $newPrice,
        ]);

        ActivityLogger::log(
            'stock_price_optimized',
            "Optimisation tarifaire du produit vedette '{$product->name}' : ".number_format($oldPrice, 0, ',', ' ').' FCFA -> '.number_format($newPrice, 0, ',', ' ')." FCFA ({$validated['reason']})",
            $product
        );

        $gain = $newPrice - $oldPrice;
        $gainFormatted = number_format(abs($gain), 0, ',', ' ');
        $msg = $gain >= 0
            ? "Prix du produit vedette '{$product->name}' optimisé à ".number_format($newPrice, 0, ',', ' ')." FCFA (+{$gainFormatted} FCFA de marge par vente)."
            : "Prix de l'article '{$product->name}' ajusté à ".number_format($newPrice, 0, ',', ' ').' FCFA.';

        return back()->with('success', $msg);
    }

    /**
     * Création automatique d'une offre pack synergie (vente croisée top seller + dormant).
     */
    public function createBundle(Request $request)
    {
        $validated = $request->validate([
            'primary_product_id' => 'required|exists:products,id',
            'secondary_product_id' => 'required|exists:products,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'discount_percent' => 'required|numeric|min:1|max:90',
            'min_amount' => 'nullable|numeric|min:0',
        ]);

        $primary = Product::findOrFail($validated['primary_product_id']);
        $secondary = Product::findOrFail($validated['secondary_product_id']);

        $code = strtoupper(trim($validated['code']));

        $promotion = Promotion::updateOrCreate(
            ['code' => $code],
            [
                'name' => $validated['name'],
                'type' => 'percent',
                'value' => $validated['discount_percent'],
                'min_amount' => $validated['min_amount'] ?? ($primary->selling_price + ($secondary->selling_price * 0.8)),
                'start_date' => now()->startOfDay(),
                'end_date' => now()->addMonths(3)->endOfDay(),
                'is_active' => true,
            ]
        );

        ActivityLogger::log(
            'stock_bundle_created',
            "Création de l'offre pack '{$promotion->name}' (Code: {$promotion->code}) associant '{$primary->name}' et '{$secondary->name}'",
            $primary
        );

        return back()->with('success', "Offre Pack activée avec succès ! Code Promo '{$promotion->code}' ({$promotion->value}% de remise combinée) disponible en caisse et sur les devis pour valoriser le panier moyen.");
    }

    /**
     * Sécurisation immédiate du réapprovisionnement pour éviter une rupture de cashflow.
     */
    public function secureStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'requested_quantity' => 'required|integer|min:1',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reason' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $unitCost = (float) ($product->purchase_price ?: ($product->selling_price * 0.65));

        $pr = PurchaseRequest::create([
            'reference' => 'DA-VIP-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
            'product_id' => $product->id,
            'warehouse_id' => $validated['warehouse_id'] ?? Warehouse::first()?->id,
            'supplier_id' => $validated['supplier_id'] ?? $product->supplier_id ?? Supplier::first()?->id,
            'user_id' => auth()->id(),
            'quantity' => $validated['requested_quantity'],
            'estimated_unit_price' => $unitCost,
            'status' => 'en_attente_responsable',
            'urgency' => 'urgente',
            'reason' => $validated['reason'] ?? "Bouclier Anti-Rupture : Produit vedette {$product->name} à forte vélocité. Sécurisation immédiate du chiffre d'affaires.",
            'notes' => "Généré via le Hub d'Optimisation des Revenus Stock.",
        ]);

        ActivityLogger::log(
            'stock_secured_reorder',
            "Déclenchement du réapprovisionnement prioritaire anti-rupture pour {$product->name} ({$pr->quantity} unités)",
            $pr
        );

        return back()->with('success', "Demande d'approvisionnement VIP {$pr->reference} créée ({$pr->quantity} unités). Risque de rupture neutralisé !");
    }

    /**
     * Ajustement et réassignation rapide du stock par entrepôt.
     */
    public function adjustStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'action_type' => 'required|in:set,add,remove,transfer',
            'quantity' => 'required|integer|min:0',
            'target_warehouse_id' => 'nullable|required_if:action_type,transfer|exists:warehouses,id|different:warehouse_id',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);
        $currentStock = StockService::getStock($product->id, $warehouse->id);
        $actionType = $validated['action_type'];
        $qty = (int) $validated['quantity'];
        $reason = $validated['reason'] ?? 'Ajustement manuel de stock';
        $notes = $validated['notes'] ?? null;
        $unitCost = (float) ($product->purchase_price ?: ($product->selling_price * 0.7));

        try {
            if ($actionType === 'set') {
                StockService::processMovement([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'domain_id' => $product->domain_id,
                    'user_id' => auth()->id(),
                    'type' => 'ajustement',
                    'reason_motif' => $reason,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'reference' => 'AJUST-'.strtoupper(Str::random(6)),
                    'notes' => $notes ?: "Ajustement du stock de {$currentStock} à {$qty} pour {$warehouse->name}",
                    'date' => now(),
                ]);
                $msg = "Stock de '{$product->name}' ajusté à {$qty} unité(s) dans {$warehouse->name}.";
            } elseif ($actionType === 'add') {
                if ($qty <= 0) {
                    return back()->with('error', 'La quantité à ajouter doit être supérieure à 0.');
                }
                StockService::processMovement([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'domain_id' => $product->domain_id,
                    'user_id' => auth()->id(),
                    'type' => 'entree',
                    'reason_motif' => $reason,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'reference' => 'ENT-'.strtoupper(Str::random(6)),
                    'notes' => $notes ?: "Ajout direct de {$qty} unité(s) pour {$warehouse->name}",
                    'date' => now(),
                ]);
                $msg = "{$qty} unité(s) ajoutée(s) au stock de '{$product->name}' dans {$warehouse->name}.";
            } elseif ($actionType === 'remove') {
                if ($qty <= 0) {
                    return back()->with('error', 'La quantité à retirer doit être supérieure à 0.');
                }
                if ($currentStock < $qty) {
                    return back()->with('error', "Stock insuffisant dans {$warehouse->name} (Disponible : {$currentStock}).");
                }
                StockService::processMovement([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'domain_id' => $product->domain_id,
                    'user_id' => auth()->id(),
                    'type' => 'sortie',
                    'reason_motif' => $reason,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'reference' => 'SRT-'.strtoupper(Str::random(6)),
                    'notes' => $notes ?: "Sortie directe de {$qty} unité(s) depuis {$warehouse->name}",
                    'date' => now(),
                ]);
                $msg = "{$qty} unité(s) retirée(s) du stock de '{$product->name}' dans {$warehouse->name}.";
            } elseif ($actionType === 'transfer') {
                if ($qty <= 0) {
                    return back()->with('error', 'La quantité à transférer doit être supérieure à 0.');
                }
                if ($currentStock < $qty) {
                    return back()->with('error', "Stock insuffisant pour ce transfert depuis {$warehouse->name} (Disponible : {$currentStock}).");
                }
                $targetWarehouse = Warehouse::findOrFail($validated['target_warehouse_id']);
                StockService::processMovement([
                    'warehouse_id' => $warehouse->id,
                    'destination_warehouse_id' => $targetWarehouse->id,
                    'product_id' => $product->id,
                    'domain_id' => $product->domain_id,
                    'user_id' => auth()->id(),
                    'type' => 'transfert',
                    'reason_motif' => $reason ?: 'Transfert / Changement d\'entrepôt',
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'reference' => 'TRF-'.strtoupper(Str::random(6)),
                    'notes' => $notes ?: "Transfert de {$qty} unité(s) de {$warehouse->name} vers {$targetWarehouse->name}",
                    'date' => now(),
                ]);
                $msg = "Transfert de {$qty} unité(s) de '{$product->name}' depuis {$warehouse->name} vers {$targetWarehouse->name} effectué avec succès.";
            }

            ActivityLogger::log('stock_adjusted', $msg, $product);

            return redirect()->route('stock.index', ['tab' => 'disponibilite'])->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la mise à jour du stock : '.$e->getMessage());
        }
    }
}
