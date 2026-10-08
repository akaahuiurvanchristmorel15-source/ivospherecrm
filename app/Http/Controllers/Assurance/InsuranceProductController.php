<?php

namespace App\Http\Controllers\Assurance;

use App\Http\Controllers\Controller;
use App\Models\InsuranceProduct;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class InsuranceProductController extends Controller
{
    public function index(Request $request)
    {
        $products = InsuranceProduct::latest()->paginate(15);

        return view('assurance.products.index', compact('products'));
    }

    public function create()
    {
        return view('assurance.products.create_edit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'status' => 'required|string',
        ]);

        $product = InsuranceProduct::create($validated);
        ActivityLogger::log('create', 'Création d\'un produit d\'assurance', $product);

        return redirect()->route('assurance.products.index')->with('success', 'Produit créé avec succès.');
    }

    public function edit(InsuranceProduct $product)
    {
        return view('assurance.products.create_edit', compact('product'));
    }

    public function update(Request $request, InsuranceProduct $product)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'status' => 'required|string',
        ]);

        $product->update($validated);
        ActivityLogger::log('update', 'Mise à jour d\'un produit d\'assurance', $product);

        return redirect()->route('assurance.products.index')->with('success', 'Produit mis à jour avec succès.');
    }

    public function destroy(InsuranceProduct $product)
    {
        ActivityLogger::log('delete', 'Suppression d\'un produit d\'assurance', $product);
        $product->delete();

        return redirect()->route('assurance.products.index')->with('success', 'Produit supprimé avec succès.');
    }
}
