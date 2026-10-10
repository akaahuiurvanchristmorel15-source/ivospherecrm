<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Domain;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\ActivityLogger;
use App\Services\QrCodeService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StockProductController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('stock.index', array_merge(['tab' => 'disponibilite'], $request->all()));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $suppliers = Supplier::orderBy('name')->get();
        $domains = Domain::active()->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('stock.products.create_edit', compact('categories', 'brands', 'suppliers', 'domains', 'warehouses'));
    }

    public function bulkCreate()
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $domains = Domain::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $defaultTaxRate = (float) Setting::get('default_tax_rate', 0);

        return view('stock.products.bulk_create', compact('categories', 'brands', 'suppliers', 'domains', 'warehouses', 'defaultTaxRate'));
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'domain_id' => 'nullable|exists:domains,id',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string|max:255',
            'products.*.sku' => 'nullable|string|max:100',
            'products.*.purchase_price' => 'nullable|numeric|min:0',
            'products.*.selling_price' => 'required|numeric|min:0',
            'products.*.initial_quantity' => 'nullable|integer|min:0',
            'products.*.min_stock' => 'nullable|integer|min:0',
            'products.*.unit' => 'nullable|string|max:50',
            'products.*.domain_id' => 'nullable|exists:domains,id',
            'products.*.category_id' => 'nullable|exists:categories,id',
        ]);

        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);
        $globalDomainId = $validated['domain_id'] ?? null;
        $globalCategoryId = $validated['category_id'] ?? null;
        $globalBrandId = $validated['brand_id'] ?? null;
        $globalSupplierId = $validated['supplier_id'] ?? null;
        $defaultTaxRate = (float) Setting::get('default_tax_rate', 0);
        $createdCount = 0;

        DB::transaction(function () use ($validated, $warehouse, $globalDomainId, $globalCategoryId, $globalBrandId, $globalSupplierId, $defaultTaxRate, &$createdCount) {
            foreach ($validated['products'] as $item) {
                $name = trim($item['name'] ?? '');
                if (empty($name)) {
                    continue;
                }

                $sku = ! empty($item['sku']) ? trim($item['sku']) : 'PRD-'.strtoupper(Str::random(6));
                while (Product::where('sku', $sku)->exists()) {
                    $sku = 'PRD-'.strtoupper(Str::random(6));
                }

                $domainId = $item['domain_id'] ?? $globalDomainId;
                $categoryId = $item['category_id'] ?? $globalCategoryId;
                $brandId = $globalBrandId;
                $supplierId = $globalSupplierId;
                $purchasePrice = (float) ($item['purchase_price'] ?? 0);
                $sellingPrice = (float) ($item['selling_price'] ?? 0);
                $quantity = (int) ($item['initial_quantity'] ?? 0);
                $minStock = (int) ($item['min_stock'] ?? 0);
                $unit = ! empty($item['unit']) ? trim($item['unit']) : 'pièce';

                $product = Product::create([
                    'name' => $name,
                    'sku' => $sku,
                    'barcode' => Product::generateEan13(),
                    'domain_id' => $domainId,
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'supplier_id' => $supplierId,
                    'purchase_price' => $purchasePrice,
                    'selling_price' => $sellingPrice,
                    'tax_rate' => $defaultTaxRate,
                    'min_stock' => $minStock,
                    'unit' => $unit,
                    'is_active' => true,
                ]);

                WarehouseStock::create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'physical_quantity' => $quantity,
                    'reserved_quantity' => 0,
                    'incoming_quantity' => 0,
                ]);

                if ($quantity > 0) {
                    StockMovement::create([
                        'warehouse_id' => $warehouse->id,
                        'product_id' => $product->id,
                        'domain_id' => $product->domain_id,
                        'user_id' => auth()->id(),
                        'type' => 'entree',
                        'reason_motif' => 'stock_initial',
                        'quantity' => $quantity,
                        'stock_before' => 0,
                        'stock_after' => $quantity,
                        'unit_cost' => $purchasePrice ?: ($sellingPrice * 0.7),
                        'reference' => 'INIT-'.$product->sku,
                        'date' => now(),
                        'notes' => "Création groupée d'articles dans l'entrepôt {$warehouse->name}",
                    ]);
                }

                $createdCount++;
            }
        });

        ActivityLogger::log('bulk_created_stock_products', "Création groupée de {$createdCount} article(s) dans l'entrepôt {$warehouse->name}", $warehouse);

        return redirect()->route('stock.index', ['tab' => 'disponibilite'])
            ->with('success', "{$createdCount} nouveau(x) produit(s) ont été créés avec succès et intégrés à l'entrepôt \"{$warehouse->name}\".");
    }

    public function bulkEdit(Request $request)
    {
        $rawIds = $request->input('product_ids', []);
        $productIds = [];

        if (is_array($rawIds)) {
            $productIds = array_filter(array_map('intval', $rawIds));
        } elseif (is_string($rawIds) && strlen(trim($rawIds)) > 0) {
            $productIds = array_filter(array_map('intval', explode(',', $rawIds)));
        }

        $query = Product::with(['domain', 'category', 'brand', 'warehouseStocks.warehouse']);

        if (! empty($productIds)) {
            $query->whereIn('id', $productIds);
        } elseif ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        $products = $query->orderBy('name')->get();

        if ($products->isEmpty()) {
            $products = Product::with(['domain', 'category', 'brand', 'warehouseStocks.warehouse'])
                ->where('is_active', true)
                ->orderBy('name')
                ->take(50)
                ->get();
        }

        $domains = Domain::active()->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('stock.products.bulk_edit', compact('products', 'domains', 'categories'));
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'common_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'apply_common_image_to_all' => 'nullable|boolean',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.purchase_price' => 'nullable|numeric|min:0',
            'products.*.selling_price' => 'required|numeric|min:0',
            'products.*.image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'products.*.delete_image' => 'nullable|boolean',
        ]);

        $commonImagePath = null;
        if ($request->hasFile('common_image') && $request->boolean('apply_common_image_to_all')) {
            $commonImagePath = $request->file('common_image')->store('products', 'public');
        }

        $updatedCount = 0;

        DB::transaction(function () use ($request, $validated, $commonImagePath, &$updatedCount) {
            foreach ($validated['products'] as $index => $itemData) {
                $product = Product::find($itemData['id']);
                if (! $product) {
                    continue;
                }

                $updateData = [
                    'purchase_price' => (float) ($itemData['purchase_price'] ?? 0),
                    'selling_price' => (float) ($itemData['selling_price'] ?? 0),
                ];

                if ($request->hasFile("products.{$index}.image")) {
                    $file = $request->file("products.{$index}.image");
                    if ($file && $file->isValid()) {
                        if ($product->image && Storage::disk('public')->exists($product->image)) {
                            Storage::disk('public')->delete($product->image);
                        }
                        $updateData['image'] = $file->store('products', 'public');
                    }
                } elseif (! empty($itemData['delete_image'])) {
                    if ($product->image && Storage::disk('public')->exists($product->image)) {
                        Storage::disk('public')->delete($product->image);
                    }
                    $updateData['image'] = null;
                } elseif ($commonImagePath) {
                    $updateData['image'] = $commonImagePath;
                }

                $product->update($updateData);
                $updatedCount++;
            }
        });

        ActivityLogger::log('bulk_updated_stock_products', "Mise à jour groupée des prix et images de {$updatedCount} article(s)", null);

        return redirect()->route('stock.index', ['tab' => 'disponibilite'])
            ->with('success', "Les informations (prix d'achat, prix de revente et images) de {$updatedCount} article(s) ont été mises à jour avec succès.");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'domain_id' => 'nullable|exists:domains,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'description' => 'nullable|string',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'image_camera' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'is_active' => 'boolean',
            'initial_warehouse_id' => 'nullable|exists:warehouses,id',
            'initial_quantity' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['purchase_price'] = $validated['purchase_price'] ?? 0;
        $validated['tax_rate'] = $validated['tax_rate'] ?? (float) Setting::get('default_tax_rate', 0);
        $validated['min_stock'] = $validated['min_stock'] ?? 0;
        $validated['unit'] = ! empty($validated['unit']) ? $validated['unit'] : 'pièce';

        if (empty($validated['barcode'])) {
            $validated['barcode'] = Product::generateEan13();
        }

        $imageFile = $request->file('image') ?? $request->file('image_camera');
        if ($imageFile) {
            $validated['image'] = $imageFile->store('products', 'public');
        }
        unset($validated['image_camera']);

        $initialWarehouseId = $validated['initial_warehouse_id'] ?? null;
        $initialQuantity = (int) ($validated['initial_quantity'] ?? 0);
        unset($validated['initial_warehouse_id'], $validated['initial_quantity']);

        $product = DB::transaction(function () use ($validated, $initialWarehouseId, $initialQuantity) {
            $product = Product::create($validated);

            if ($initialWarehouseId && $initialQuantity > 0) {
                WarehouseStock::updateOrCreate(
                    [
                        'warehouse_id' => $initialWarehouseId,
                        'product_id' => $product->id,
                    ],
                    [
                        'physical_quantity' => $initialQuantity,
                        'reserved_quantity' => 0,
                        'incoming_quantity' => 0,
                    ]
                );

                StockMovement::create([
                    'warehouse_id' => $initialWarehouseId,
                    'product_id' => $product->id,
                    'domain_id' => $product->domain_id,
                    'user_id' => auth()->id(),
                    'type' => 'entree',
                    'reason_motif' => 'stock_initial',
                    'quantity' => $initialQuantity,
                    'stock_before' => 0,
                    'stock_after' => $initialQuantity,
                    'unit_cost' => $product->purchase_price ?: ($product->selling_price * 0.7),
                    'reference' => 'INIT-'.$product->sku,
                    'date' => now(),
                    'notes' => 'Création d\'article et initialisation du stock entrant',
                ]);
            }

            return $product;
        });

        ActivityLogger::log('created_stock_product', 'Création de l\'article '.$product->name.' dans les stocks', $product);

        return redirect()->route('stock.index', ['tab' => 'disponibilite'])->with('success', 'Produit "'.$product->name.'" ajouté au catalogue et intégré au stock avec succès.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();
        $suppliers = Supplier::orderBy('name')->get();
        $domains = Domain::active()->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $warehouseStocks = WarehouseStock::where('product_id', $product->id)->with('warehouse')->get();

        return view('stock.products.create_edit', compact('product', 'categories', 'brands', 'suppliers', 'domains', 'warehouses', 'warehouseStocks'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'domain_id' => 'nullable|exists:domains,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,'.$product->id,
            'barcode' => 'nullable|string|max:100|unique:products,barcode,'.$product->id,
            'description' => 'nullable|string',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'image_camera' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'delete_image' => 'nullable|boolean',
            'is_active' => 'boolean',
            'warehouse_stocks' => 'nullable|array',
            'warehouse_stocks.*' => 'nullable|integer|min:0',
            'new_warehouse_id' => 'nullable|exists:warehouses,id',
            'new_warehouse_quantity' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['purchase_price'] = $validated['purchase_price'] ?? 0;
        $validated['tax_rate'] = $validated['tax_rate'] ?? (float) Setting::get('default_tax_rate', 0);
        $validated['min_stock'] = $validated['min_stock'] ?? 0;
        $validated['unit'] = ! empty($validated['unit']) ? $validated['unit'] : 'pièce';

        if (empty($validated['barcode'])) {
            $validated['barcode'] = Product::generateEan13($product->id);
        }

        if ($request->boolean('delete_image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = null;
        }

        $imageFile = $request->file('image') ?? $request->file('image_camera');
        if ($imageFile) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $imageFile->store('products', 'public');
        }

        $warehouseStocksInput = $validated['warehouse_stocks'] ?? [];
        $newWarehouseId = $validated['new_warehouse_id'] ?? null;
        $newWarehouseQty = (int) ($validated['new_warehouse_quantity'] ?? 0);

        unset($validated['delete_image'], $validated['image_camera'], $validated['warehouse_stocks'], $validated['new_warehouse_id'], $validated['new_warehouse_quantity']);

        $product->update($validated);

        // Traitement de l'ajustement des stocks par entrepôt existant
        if (! empty($warehouseStocksInput) && is_array($warehouseStocksInput)) {
            foreach ($warehouseStocksInput as $whId => $newQty) {
                $whId = (int) $whId;
                if ($whId <= 0 || ! Warehouse::where('id', $whId)->exists()) {
                    continue;
                }
                $newQty = max(0, (int) $newQty);
                $currentQty = StockService::getStock($product->id, $whId);

                if ($currentQty !== $newQty) {
                    StockService::processMovement([
                        'warehouse_id' => $whId,
                        'product_id' => $product->id,
                        'domain_id' => $product->domain_id,
                        'user_id' => auth()->id(),
                        'type' => 'ajustement',
                        'reason_motif' => 'Modification fiche article',
                        'quantity' => $newQty,
                        'unit_cost' => $product->purchase_price ?: ($product->selling_price * 0.7),
                        'reference' => 'AJUST-'.strtoupper(Str::random(6)),
                        'notes' => "Ajustement du stock depuis la fiche article ({$currentQty} -> {$newQty})",
                        'date' => now(),
                    ]);
                }
            }
        }

        // Affectation et initialisation d'un nouvel entrepôt
        if ($newWarehouseId && $newWarehouseQty > 0) {
            if (! isset($warehouseStocksInput[$newWarehouseId])) {
                $currentNewWhQty = StockService::getStock($product->id, (int) $newWarehouseId);
                StockService::processMovement([
                    'warehouse_id' => (int) $newWarehouseId,
                    'product_id' => $product->id,
                    'domain_id' => $product->domain_id,
                    'user_id' => auth()->id(),
                    'type' => 'entree',
                    'reason_motif' => 'Affectation entrepôt fiche article',
                    'quantity' => $newWarehouseQty,
                    'unit_cost' => $product->purchase_price ?: ($product->selling_price * 0.7),
                    'reference' => 'ENT-'.strtoupper(Str::random(6)),
                    'notes' => "Attribution d'entrepôt et ajout de stock depuis la fiche article (+{$newWarehouseQty})",
                    'date' => now(),
                ]);
            }
        }

        ActivityLogger::log('updated_stock_product', 'Mise à jour de l\'article '.$product->name.' dans les stocks', $product);

        return redirect()->route('stock.index', ['tab' => 'disponibilite'])->with('success', 'Fiche article "'.$product->name.'" et stocks par entrepôt mis à jour avec succès.');
    }

    public function destroy(Product $product)
    {
        $name = $product->name;

        DB::transaction(function () use ($product) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->warehouseStocks()->delete();
            $product->movements()->delete();
            $product->batches()->delete();
            $product->delete();
        });

        ActivityLogger::log('deleted_stock_product', 'Suppression de l\'article '.$name.' des stocks', null);

        return redirect()->route('stock.index', ['tab' => 'disponibilite'])->with('success', 'Article "'.$name.'" supprimé des stocks.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|integer|exists:products,id',
        ]);

        $products = Product::whereIn('id', $validated['product_ids'])->get();
        $count = $products->count();

        if ($count === 0) {
            return redirect()->route('stock.index', ['tab' => 'disponibilite'])->with('error', 'Aucun produit sélectionné pour la suppression.');
        }

        $names = $products->pluck('name')->take(5)->implode(', ');
        if ($count > 5) {
            $names .= ' et '.($count - 5).' autres';
        }

        DB::transaction(function () use ($products) {
            foreach ($products as $product) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $product->warehouseStocks()->delete();
                $product->movements()->delete();
                $product->batches()->delete();
                $product->delete();
            }
        });

        ActivityLogger::log('bulk_deleted_stock_products', "Suppression groupée de {$count} articles : {$names}", null);

        return redirect()->route('stock.index', ['tab' => 'disponibilite'])
            ->with('success', "{$count} article(s) supprimé(s) des stocks avec succès.");
    }

    /**
     * Impression / Export PDF au format A4 Paysage du catalogue produit.
     * Tableau de 20 produits par page avec Désignation, Quantité, Prix Unitaire (vide) et Prix Revente (vide).
     */
    public function printCatalog(Request $request)
    {
        $query = Product::with(['domain', 'category', 'brand', 'warehouseStocks']);

        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%")
                    ->orWhere('barcode', 'like', "%{$s}%");
            });
        }

        $products = $query->orderBy('name')->get();

        // Découpage strict en paquets de 20 produits par page A4 Paysage
        $chunks = $products->chunk(20);

        return view('stock.products.print_catalog', compact('products', 'chunks'));
    }

    /**
     * Mise à jour rapide de la photo du produit (upload fichier ou capture directe smartphone).
     */
    public function updateImage(Request $request, Product $product)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'image_camera' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'delete_image' => 'nullable|boolean',
        ]);

        if ($request->boolean('delete_image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->update(['image' => null]);

            ActivityLogger::log('updated_stock_product_image', "Suppression de la photo de l'article {$product->name}", $product);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "La photo de l'article \"{$product->name}\" a été supprimée.",
                    'image_url' => null,
                ]);
            }

            return back()->with('success', "La photo de l'article \"{$product->name}\" a été supprimée.");
        }

        $imageFile = $request->file('image') ?? $request->file('image_camera');
        if (! $imageFile) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun fichier image sélectionné.',
                ], 422);
            }

            return back()->with('error', 'Aucun fichier image sélectionné.');
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $path = $imageFile->store('products', 'public');
        $product->update(['image' => $path]);

        ActivityLogger::log('updated_stock_product_image', "Mise à jour rapide de la photo de l'article {$product->name}", $product);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Photo de l'article \"{$product->name}\" mise à jour avec succès.",
                'image_url' => asset('storage/'.$path),
            ]);
        }

        return back()->with('success', "Photo de l'article \"{$product->name}\" mise à jour avec succès.");
    }

    /**
     * Téléchargement du QR Code avec EAN au format PNG.
     */
    public function downloadQr(Request $request, Product $product, QrCodeService $qrService): Response
    {
        $asLabel = $request->boolean('label');

        return $qrService->downloadResponse($product, $asLabel);
    }
}
