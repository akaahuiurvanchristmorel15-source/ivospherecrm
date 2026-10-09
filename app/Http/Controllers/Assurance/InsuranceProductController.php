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
            'name' => 'required|string|max:255',
            'partner' => 'required|string|max:100',
            'type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'premium_range' => 'nullable|string|max:100',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|string|max:20',
            'conditions' => 'nullable|string',
        ]);

        $validated['commission_rate'] = $validated['commission_rate'] ?? 0;

        $product = InsuranceProduct::create($validated);
        ActivityLogger::log('create', 'Création d\'un produit d\'assurance '.$product->name, $product);

        return redirect()->route('assurance.products.index')->with('success', 'Produit d\'assurance créé avec succès.');
    }

    public function edit(InsuranceProduct $product)
    {
        return view('assurance.products.create_edit', compact('product'));
    }

    public function update(Request $request, InsuranceProduct $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'partner' => 'required|string|max:100',
            'type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'premium_range' => 'nullable|string|max:100',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|string|max:20',
            'conditions' => 'nullable|string',
        ]);

        $validated['commission_rate'] = $validated['commission_rate'] ?? 0;

        $product->update($validated);
        ActivityLogger::log('update', 'Mise à jour d\'un produit d\'assurance '.$product->name, $product);

        return redirect()->route('assurance.products.index')->with('success', 'Produit d\'assurance mis à jour avec succès.');
    }

    public function destroy(InsuranceProduct $product)
    {
        ActivityLogger::log('delete', 'Suppression d\'un produit d\'assurance '.$product->name, $product);
        $product->delete();

        return redirect()->route('assurance.products.index')->with('success', 'Produit supprimé avec succès.');
    }
}
