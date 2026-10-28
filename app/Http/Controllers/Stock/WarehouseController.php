<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Services\ActivityLogger;
use App\Services\QrCodeService;
use App\Services\StockService;
use App\Support\PdfRenderer;
use App\Support\TenantRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class WarehouseController extends Controller
{
    /**
     * Nombre de QR codes par page A4 (grille de 3 colonnes × 4 lignes).
     */
    public const QR_CODES_PER_PAGE = 12;

    public const QR_CODES_PER_ROW = 3;

    public function index(Request $request)
    {
        $query = Warehouse::with('domain')
            ->withSum('warehouseStocks as physical_stock', 'physical_quantity')
            ->withCount(['warehouseStocks as physical_product_count' => fn ($stockQuery) => $stockQuery->where('physical_quantity', '>', 0)])
            ->orderBy('label_number');

        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        $warehouses = $query->paginate(20);

        // Append stock summary
        foreach ($warehouses as $warehouse) {
            $latestIds = StockMovement::where('warehouse_id', $warehouse->id)
                ->select(DB::raw('MAX(id) as id'))
                ->groupBy('product_id')
                ->pluck('id');

            $warehouse->product_count = StockMovement::whereIn('id', $latestIds)->count();
            $warehouse->total_items = StockMovement::whereIn('id', $latestIds)->sum('stock_after');
        }

        $domains = Domain::all();

        return view('stock.warehouses.index', compact('warehouses', 'domains'));
    }

    public function create()
    {
        $domains = Domain::all();
        $nextLabelNumber = Warehouse::nextLabelNumber();

        return view('stock.warehouses.create_edit', compact('domains', 'nextLabelNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', TenantRule::unique('warehouses', 'code')],
            'label_number' => ['nullable', 'integer', 'min:1', 'max:65535', TenantRule::unique('warehouses', 'label_number')],
            'label_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'domain_id' => 'nullable|exists:domains,id',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'manager' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $warehouse = Warehouse::create($validated);

        ActivityLogger::log('create_warehouse', "Création de l'entrepôt: {$warehouse->name}", $warehouse);

        return redirect()->route('stock.warehouses.index')->with('success', 'Entrepôt créé avec succès.');
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load('domain');

        $latestIds = StockMovement::where('warehouse_id', $warehouse->id)
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('product_id')
            ->pluck('id');

        $movementLevels = StockMovement::with('product')
            ->whereIn('id', $latestIds)
            ->get()
            ->keyBy('product_id');

        // Quantité réelle de l'entrepôt quand elle existe, sinon le stock après le dernier mouvement
        $warehouseStocks = $warehouse->warehouseStocks()->with('product')->get()->keyBy('product_id');

        // Un article entièrement transféré ailleurs n'a plus de ligne de stock ici : il n'est plus listé
        $transferredOut = $movementLevels->filter(fn (StockMovement $movement) => $movement->type === 'transfert'
            && (int) $movement->stock_after === 0
            && ! $warehouseStocks->has($movement->product_id))->keys();

        $stockLevels = $movementLevels->keys()->diff($transferredOut)->merge($warehouseStocks->keys())->unique()
            ->map(fn ($productId) => (object) [
                'product_id' => $productId,
                'product' => $warehouseStocks->get($productId)?->product ?? $movementLevels->get($productId)?->product,
                'stock_after' => $warehouseStocks->has($productId)
                    ? (int) $warehouseStocks->get($productId)->physical_quantity
                    : (int) $movementLevels->get($productId)->stock_after,
            ])
            ->values();

        $recentMovements = StockMovement::with(['product', 'user', 'destinationWarehouse'])
            ->where('warehouse_id', $warehouse->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $catalogProducts = Product::active()->orderBy('name')->get(['id', 'name', 'sku', 'barcode']);

        $destinationWarehouses = Warehouse::active()->whereKeyNot($warehouse->id)->orderBy('label_number')->orderBy('name')->get(['id', 'name', 'label_number']);

        return view('stock.warehouses.show', compact('warehouse', 'stockLevels', 'recentMovements', 'catalogProducts', 'destinationWarehouses'));
    }

    /**
     * Modification groupée des quantités en stock des produits de l'entrepôt (un ajustement par produit modifié).
     * Les lignes arrivent en JSON (un seul champ) pour ne pas dépasser max_input_vars.
     */
    public function updateStock(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $request->merge(['quantities' => array_values((array) json_decode((string) $request->input('quantities_json'), true))]);

        $validated = $request->validate([
            'quantities' => ['required', 'array', 'min:1'],
            'quantities.*.id' => ['required', 'integer', 'distinct'],
            'quantities.*.quantity' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ], [
            'quantities.required' => 'Aucune quantité modifiée.',
            'quantities.*.quantity.required' => 'La quantité est obligatoire.',
            'quantities.*.quantity.integer' => 'La quantité doit être un nombre entier.',
            'quantities.*.quantity.min' => 'La quantité ne peut pas être négative.',
        ]);

        $warehouseProductIds = StockMovement::where('warehouse_id', $warehouse->id)->distinct()->pluck('product_id')
            ->merge($warehouse->warehouseStocks()->pluck('product_id'))
            ->unique();

        $products = Product::whereIn('id', collect($validated['quantities'])->pluck('id')->intersect($warehouseProductIds))->get()->keyBy('id');
        $reason = $validated['reason'] ?? 'Ajustement depuis la fiche entrepôt';
        $reference = 'AJUST-'.strtoupper(Str::random(8));

        $updated = DB::transaction(function () use ($validated, $products, $warehouse, $reason, $reference) {
            $count = 0;

            foreach ($validated['quantities'] as $row) {
                $product = $products->get($row['id']);
                $currentStock = $product ? StockService::getStock($product->id, $warehouse->id) : null;

                if (! $product || $currentStock === (int) $row['quantity']) {
                    continue;
                }

                StockService::processMovement([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'domain_id' => $product->domain_id,
                    'user_id' => auth()->id(),
                    'type' => 'ajustement',
                    'reason_motif' => $reason,
                    'quantity' => (int) $row['quantity'],
                    'unit_cost' => (float) ($product->purchase_price ?: ($product->selling_price * 0.7)),
                    'reference' => $reference,
                    'notes' => "Ajustement du stock de {$currentStock} à {$row['quantity']} pour {$warehouse->name}",
                    'date' => now(),
                ]);
                $count++;
            }

            return $count;
        });

        if ($updated > 0) {
            ActivityLogger::log('stock_entrepot', "A ajusté le stock de {$updated} produit(s) dans l'entrepôt {$warehouse->name}", $warehouse);
        }

        return redirect()->route('stock.warehouses.show', $warehouse)
            ->with('success', $updated > 0 ? "Stock mis à jour pour {$updated} produit(s)." : 'Aucune quantité modifiée.');
    }

    /**
     * Transfert groupé d'articles vers un autre entrepôt, exécuté immédiatement.
     * Un article transféré en totalité n'existe plus dans l'entrepôt source.
     */
    public function transferProducts(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $request->merge(['transfers' => array_values((array) json_decode((string) $request->input('transfers_json'), true))]);

        $validated = $request->validate([
            'destination_warehouse_id' => ['required', 'integer', Rule::notIn([$warehouse->id])],
            'transfers' => ['required', 'array', 'min:1'],
            'transfers.*.product_id' => ['required', 'integer', 'distinct'],
            'transfers.*.quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'destination_warehouse_id.required' => "Choisissez l'entrepôt de destination.",
            'destination_warehouse_id.not_in' => "L'entrepôt de destination doit être différent de l'entrepôt source.",
            'transfers.required' => 'Sélectionnez au moins un article à transférer.',
            'transfers.*.quantity.min' => "La quantité doit être d'au moins 1.",
        ]);

        $destination = Warehouse::find($validated['destination_warehouse_id']);
        if (! $destination) {
            return back()->withErrors(['destination_warehouse_id' => "L'entrepôt de destination est introuvable."]);
        }

        $products = Product::whereIn('id', collect($validated['transfers'])->pluck('product_id'))->get()->keyBy('id');

        foreach ($validated['transfers'] as $item) {
            $product = $products->get($item['product_id']);
            $available = $product ? StockService::getStock($product->id, $warehouse->id) : 0;

            if (! $product || $item['quantity'] > $available) {
                $name = $product?->name ?? 'inconnu';

                return back()->withErrors(['transfers' => "Transfert impossible : seulement {$available} unité(s) de « {$name} » dans {$warehouse->name}, pour {$item['quantity']} demandée(s)."]);
            }
        }

        $notes = $validated['notes'] ?? null;

        try {
            DB::transaction(function () use ($validated, $products, $warehouse, $destination, $notes) {
                foreach ($validated['transfers'] as $item) {
                    $transfer = StockTransfer::create([
                        'reference' => 'TRF-'.now()->format('Y').'-'.strtoupper(Str::random(8)),
                        'source_warehouse_id' => $warehouse->id,
                        'destination_warehouse_id' => $destination->id,
                        'product_id' => $item['product_id'],
                        'user_id' => auth()->id(),
                        'quantity' => $item['quantity'],
                        'reason' => $notes ?? 'Transfert groupé inter-entrepôts',
                        'status' => 'receptionne',
                        'shipped_at' => now(),
                        'received_at' => now(),
                        'notes' => $notes,
                    ]);

                    StockService::processMovement([
                        'warehouse_id' => $warehouse->id,
                        'destination_warehouse_id' => $destination->id,
                        'product_id' => (int) $item['product_id'],
                        'domain_id' => $products->get($item['product_id'])?->domain_id,
                        'user_id' => auth()->id(),
                        'type' => 'transfert',
                        'reason_motif' => 'transfert_inter_entrepots',
                        'quantity' => (int) $item['quantity'],
                        'reference' => $transfer->reference,
                        'notes' => $notes ?? "Transfert groupé vers {$destination->name}",
                        'date' => now(),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['transfers' => "Le transfert a échoué, aucun stock n'a été déplacé : {$exception->getMessage()}"]);
        }

        $count = count($validated['transfers']);
        $units = collect($validated['transfers'])->sum('quantity');

        ActivityLogger::log('transfert_entrepot', "A transféré {$count} article(s) ({$units} unité(s)) de {$warehouse->name} vers {$destination->name}", $warehouse);

        return redirect()->route('stock.warehouses.show', $warehouse)
            ->with('success', "{$count} article(s) transféré(s) vers {$destination->name} ({$units} unité(s)).");
    }

    /**
     * Ajout groupé de produits du catalogue dans l'entrepôt : une entrée de stock par produit.
     * Les lignes arrivent en JSON (un seul champ) pour ne pas dépasser max_input_vars.
     */
    public function addProducts(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $request->merge(['items' => array_values((array) json_decode((string) $request->input('items_json'), true))]);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'items.required' => 'Ajoutez au moins un produit.',
            'items.*.product_id.distinct' => 'Un même produit est ajouté deux fois.',
            'items.*.quantity.min' => "La quantité doit être d'au moins 1.",
        ]);

        $products = Product::whereIn('id', collect($validated['items'])->pluck('product_id'))->get()->keyBy('id');

        if ($products->count() !== count($validated['items'])) {
            return back()->withErrors(['items' => 'Un des produits sélectionnés est introuvable.']);
        }

        $reference = 'AJT-'.strtoupper(Str::random(8));

        DB::transaction(function () use ($validated, $products, $warehouse, $reference) {
            foreach ($validated['items'] as $item) {
                StockService::processMovement([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => (int) $item['product_id'],
                    'domain_id' => $products->get($item['product_id'])?->domain_id ?? $warehouse->domain_id,
                    'user_id' => auth()->id(),
                    'type' => 'entree',
                    'reason_motif' => 'ajout_entrepot',
                    'quantity' => (int) $item['quantity'],
                    'reference' => $reference,
                    'notes' => $validated['notes'] ?? "Ajout groupé dans l'entrepôt {$warehouse->name}",
                    'date' => now(),
                ]);
            }
        });

        $count = count($validated['items']);
        $units = collect($validated['items'])->sum('quantity');

        ActivityLogger::log('ajout_produits_entrepot', "A ajouté {$count} produit(s) ({$units} unité(s)) dans l'entrepôt {$warehouse->name}", $warehouse);

        return redirect()->route('stock.warehouses.show', $warehouse)
            ->with('success', "{$count} produit(s) ajouté(s) à l'entrepôt ({$units} unité(s)).");
    }

    /**
     * Modification groupée des prix d'achat et de vente des produits présents dans l'entrepôt.
     * Les lignes arrivent en JSON (un seul champ) pour ne pas dépasser max_input_vars.
     */
    public function updatePrices(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $request->merge(['prices' => array_values((array) json_decode((string) $request->input('prices_json'), true))]);

        $validated = $request->validate([
            'prices' => ['required', 'array', 'min:1'],
            'prices.*.id' => ['required', 'integer'],
            'prices.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
            'prices.*.selling_price' => ['required', 'numeric', 'min:0'],
        ], [
            'prices.required' => 'Aucun prix modifié.',
            'prices.*.selling_price.required' => 'Le prix de vente est obligatoire.',
        ]);

        $warehouseProductIds = StockMovement::where('warehouse_id', $warehouse->id)->distinct()->pluck('product_id')
            ->merge($warehouse->warehouseStocks()->pluck('product_id'))
            ->unique();

        $products = Product::whereIn('id', collect($validated['prices'])->pluck('id')->intersect($warehouseProductIds))->get()->keyBy('id');

        $updated = DB::transaction(function () use ($validated, $products) {
            $count = 0;

            foreach ($validated['prices'] as $row) {
                $product = $products->get($row['id']);
                if (! $product) {
                    continue;
                }

                $product->fill([
                    'purchase_price' => $row['purchase_price'] ?? $product->purchase_price,
                    'selling_price' => $row['selling_price'],
                ]);

                if ($product->isDirty(['purchase_price', 'selling_price'])) {
                    $product->save();
                    $count++;
                }
            }

            return $count;
        });

        if ($updated > 0) {
            ActivityLogger::log(
                action: 'prix_entrepot',
                description: "A modifié les prix de {$updated} produit(s) depuis l'entrepôt {$warehouse->name}",
                subject: $warehouse
            );
        }

        return redirect()->route('stock.warehouses.show', $warehouse)
            ->with('success', $updated > 0 ? "Prix mis à jour pour {$updated} produit(s)." : 'Aucun prix modifié.');
    }

    /**
     * PDF A4 des QR codes des produits en stock dans l'entrepôt (grille découpable, nom au-dessus, SKU en dessous).
     */
    public function downloadQrCodes(Warehouse $warehouse, QrCodeService $qrCodeService, PdfRenderer $pdfRenderer): Response|RedirectResponse
    {
        $products = Product::whereHas('warehouseStocks', fn ($query) => $query
            ->where('warehouse_id', $warehouse->id)
            ->where('physical_quantity', '>', 0))
            ->orderBy('name')
            ->get();

        if ($products->isEmpty()) {
            return redirect()
                ->route('stock.warehouses.show', $warehouse)
                ->with('error', "Aucun produit en stock dans l'entrepôt « {$warehouse->name} » : il n'y a aucun QR code à imprimer.");
        }

        $labels = $products->map(fn (Product $product) => [
            'product' => $product,
            'qrSrc' => 'data:image/svg+xml;base64,'.base64_encode($qrCodeService->generateCompactSvg($product->ean)),
        ]);

        $data = [
            'warehouse' => $warehouse,
            'pages' => $labels->chunk(self::QR_CODES_PER_PAGE)->map(fn ($page) => $page->chunk(self::QR_CODES_PER_ROW)),
            'productCount' => $products->count(),
            'perRow' => self::QR_CODES_PER_ROW,
            'rowsPerPage' => intdiv(self::QR_CODES_PER_PAGE, self::QR_CODES_PER_ROW),
        ];

        try {
            $pdf = $pdfRenderer->fromHtml(view('stock.warehouses.qr_codes', $data + ['isPdf' => true])->render());
        } catch (Throwable $exception) {
            report($exception);

            return response()->view('stock.warehouses.qr_codes', $data + ['isPdf' => false]);
        }

        $filename = 'QR-codes-'.Str::slug((string) $warehouse->code).'-'.now()->format('Y-m-d').'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function edit(Warehouse $warehouse)
    {
        $domains = Domain::all();
        $nextLabelNumber = $warehouse->label_number ?? Warehouse::nextLabelNumber();

        return view('stock.warehouses.create_edit', compact('warehouse', 'domains', 'nextLabelNumber'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', TenantRule::unique('warehouses', 'code', $warehouse->id)],
            'label_number' => ['nullable', 'integer', 'min:1', 'max:65535', TenantRule::unique('warehouses', 'label_number', $warehouse->id)],
            'label_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'domain_id' => 'nullable|exists:domains,id',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'manager' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $validated['label_number'] ??= $warehouse->label_number ?? Warehouse::nextLabelNumber();
        $validated['label_color'] ??= Warehouse::defaultLabelColor($validated['label_number']);

        $warehouse->update($validated);

        ActivityLogger::log('update_warehouse', "Mise à jour de l'entrepôt: {$warehouse->name}", $warehouse);

        return redirect()->route('stock.warehouses.index')->with('success', 'Entrepôt mis à jour avec succès.');
    }

    public function destroy(Request $request, Warehouse $warehouse)
    {
        if (Warehouse::count() <= 1) {
            return back()->with('error', "Impossible de supprimer le seul entrepôt de l'entreprise. Vous devez conserver au moins un site de stockage actif.");
        }

        $warehouseName = $warehouse->name;
        $remainingStocks = $warehouse->warehouseStocks()->where('physical_quantity', '>', 0)->get();
        $totalUnits = (int) $remainingStocks->sum('physical_quantity');

        $wantsForceDelete = $request->input('stock_resolution') === 'force' || $request->boolean('force_delete');

        // Si l'utilisateur a choisi de transférer les stocks vers un autre entrepôt avant suppression
        if (! $wantsForceDelete && $request->filled('transfer_to_warehouse_id') && $totalUnits > 0) {
            $destWarehouse = Warehouse::find($request->input('transfer_to_warehouse_id'));

            if (! $destWarehouse) {
                return back()->with('error', "L'entrepôt de destination sélectionné est introuvable.");
            }

            if ($destWarehouse->id === $warehouse->id) {
                return back()->with('error', "L'entrepôt de destination doit être différent de l'entrepôt à supprimer.");
            }

            try {
                DB::transaction(function () use ($warehouse, $destWarehouse, $remainingStocks) {
                    foreach ($remainingStocks as $ws) {
                        StockService::processMovement([
                            'warehouse_id' => $warehouse->id,
                            'destination_warehouse_id' => $destWarehouse->id,
                            'product_id' => $ws->product_id,
                            'domain_id' => $ws->product?->domain_id,
                            'user_id' => auth()->id(),
                            'type' => 'transfert',
                            'reason_motif' => 'Transfert avant suppression entrepôt',
                            'quantity' => $ws->physical_quantity,
                            'reference' => 'TRF-CLOTURE-'.strtoupper(Str::random(5)),
                            'notes' => "Transfert automatique de clôture vers {$destWarehouse->name} avant suppression de {$warehouse->name}",
                            'date' => now(),
                        ]);
                    }

                    $warehouse->delete();
                });
            } catch (Throwable $exception) {
                report($exception);

                return back()->with('error', "Le transfert des stocks a échoué, l'entrepôt n'a pas été supprimé : {$exception->getMessage()}");
            }

            ActivityLogger::log('delete_warehouse', "Suppression de l'entrepôt {$warehouseName} (stocks transférés vers {$destWarehouse->name})", null);

            return redirect()->route('stock.warehouses.index')->with('success', "L'entrepôt '{$warehouseName}' a été supprimé et ses {$totalUnits} unité(s) de stock ont été transférées vers '{$destWarehouse->name}'.");
        }

        // Si des stocks physiques existent et que la suppression forcée n'a pas été confirmée
        if ($totalUnits > 0 && ! $wantsForceDelete) {
            return back()->with('error', "Cet entrepôt contient encore {$totalUnits} unité(s) en stock. Veuillez transférer les stocks vers un autre entrepôt ou confirmer la suppression forcée.");
        }

        $warehouse->delete();
        ActivityLogger::log('delete_warehouse', "Suppression de l'entrepôt: {$warehouseName}", null);

        return redirect()->route('stock.warehouses.index')->with('success', "L'entrepôt '{$warehouseName}' a été supprimé avec succès.");
    }
}
