<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Domain;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\ActivityLogger;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $validated['tax_rate'] = $validated['tax_rate'] ?? 18;
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
        $validated['tax_rate'] = $validated['tax_rate'] ?? 18;
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

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        ActivityLogger::log('deleted_stock_product', 'Suppression de l\'article '.$name.' des stocks', null);

        return redirect()->route('stock.index', ['tab' => 'disponibilite'])->with('success', 'Article "'.$name.'" supprimé des stocks.');
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
