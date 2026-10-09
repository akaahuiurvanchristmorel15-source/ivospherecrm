<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Domain;
use App\Models\Product;
use App\Services\ActivityLogger;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    /**
     * Le catalogue commercial est centralisé sous Stock & Logistique (onglet Disponibilité)
     * afin d'offrir une vision unifiée des articles, stocks, codes-barres EAN et QR Codes.
     */
    public function index(Request $request)
    {
        return redirect()->route('stock.index', ['tab' => 'disponibilite']);
    }

    public function create()
    {
        return redirect()->route('stock.products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'domain_id' => 'nullable|exists:domains,id',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit' => 'nullable|string|max:50',
            'selling_price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['purchase_price'] = $validated['purchase_price'] ?? 0;
        $validated['tax_rate'] = $validated['tax_rate'] ?? 18;
        $validated['min_stock'] = $validated['min_stock'] ?? 0;
        $validated['unit'] = ! empty($validated['unit']) ? $validated['unit'] : 'pièce';

        if (empty($validated['barcode'])) {
            $validated['barcode'] = Product::generateEan13();
        }

        $product = Product::create($validated);

        ActivityLogger::log('created_commercial_product', "Création de l'article '{$product->name}' au catalogue commercial (EAN: {$product->ean})", $product);

        return redirect()->route('commercial.products.index')->with('success', "Produit \"{$product->name}\" ajouté avec succès avec son code EAN {$product->ean}.");
    }

    public function show(Product $product)
    {
        return redirect()->route('commercial.products.edit', $product);
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();
        $domains = Domain::active()->get();

        return view('commercial.products.create_edit', compact('product', 'categories', 'brands', 'domains'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,'.$product->id,
            'barcode' => 'nullable|string|max:100|unique:products,barcode,'.$product->id,
            'domain_id' => 'nullable|exists:domains,id',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit' => 'nullable|string|max:50',
            'selling_price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['purchase_price'] = $validated['purchase_price'] ?? 0;
        $validated['tax_rate'] = $validated['tax_rate'] ?? 18;
        $validated['min_stock'] = $validated['min_stock'] ?? 0;
        $validated['unit'] = ! empty($validated['unit']) ? $validated['unit'] : 'pièce';

        if (empty($validated['barcode'])) {
            $validated['barcode'] = Product::generateEan13($product->id);
        }

        $product->update($validated);

        ActivityLogger::log('updated_commercial_product', "Mise à jour de l'article '{$product->name}' au catalogue", $product);

        return redirect()->route('commercial.products.index')->with('success', "Article \"{$product->name}\" mis à jour avec succès.");
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $product->delete();

        ActivityLogger::log('deleted_commercial_product', "Suppression de l'article '{$name}' du catalogue", null);

        return redirect()->route('commercial.products.index')->with('success', "Article \"{$name}\" retiré du catalogue.");
    }

    /**
     * Téléchargement du QR Code intégrant l'EAN au format PNG.
     */
    public function downloadQr(Request $request, Product $product, QrCodeService $qrService): Response
    {
        $asLabel = $request->boolean('label');

        return $qrService->downloadResponse($product, $asLabel);
    }
}
