<?php

namespace App\Http\Controllers\Sport;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Product;
use App\Models\SportArticle;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SportArticleController extends Controller
{
    public function index(Request $request)
    {
        $articles = SportArticle::with(['customer', 'product'])->latest()->paginate(15);

        return view('sport.articles.index', compact('articles'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('sport.articles.create_edit', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'new_product_name' => 'nullable|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'team' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:20',
            'color' => 'nullable|string|max:50',
            'number' => 'nullable|string|max:10',
            'custom_name' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'status' => 'required|string|max:30',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['product_id'])) {
            $sportDomain = Domain::where('code', 'SPORT')->first();
            $productName = ! empty($validated['new_product_name'])
                ? $validated['new_product_name']
                : 'Maillot / Tenue Sportive Personnalisée';

            $product = Product::firstOrCreate(
                ['name' => $productName],
                [
                    'sku' => 'SPT-'.strtoupper(Str::random(6)),
                    'domain_id' => $sportDomain?->id,
                    'selling_price' => $validated['unit_price'],
                    'purchase_price' => 0,
                    'unit' => 'pièce',
                    'min_stock' => 0,
                    'is_active' => true,
                ]
            );
            $validated['product_id'] = $product->id;
        }

        unset($validated['new_product_name']);
        $validated['total'] = $validated['quantity'] * $validated['unit_price'];

        $article = SportArticle::create($validated);
        ActivityLogger::log('create', 'Création d\'un article de sport personnalisé', $article);

        return redirect()->route('sport.articles.index')->with('success', 'Article de sport créé avec succès.');
    }

    public function show(SportArticle $article)
    {
        $article->load(['customer', 'product', 'order']);

        return view('sport.articles.show', compact('article'));
    }

    public function edit(SportArticle $article)
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('sport.articles.create_edit', compact('article', 'customers', 'products'));
    }

    public function update(Request $request, SportArticle $article)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'nullable|exists:customers,id',
            'team' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:20',
            'color' => 'nullable|string|max:50',
            'number' => 'nullable|string|max:10',
            'custom_name' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'status' => 'required|string|max:30',
            'notes' => 'nullable|string',
        ]);

        $validated['total'] = $validated['quantity'] * $validated['unit_price'];

        $article->update($validated);
        ActivityLogger::log('update', 'Mise à jour d\'un article de sport', $article);

        return redirect()->route('sport.articles.index')->with('success', 'Article de sport mis à jour avec succès.');
    }

    public function destroy(SportArticle $article)
    {
        ActivityLogger::log('delete', 'Suppression d\'un article de sport', $article);
        $article->delete();

        return redirect()->route('sport.articles.index')->with('success', 'Article supprimé avec succès.');
    }
}
