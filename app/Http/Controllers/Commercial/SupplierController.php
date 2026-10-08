<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $suppliers = $query->latest()->paginate(15);

        return view('commercial.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('commercial.suppliers.create_edit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:suppliers,code',
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'status' => 'required|in:actif,inactif',
            'notes' => 'nullable|string',
        ]);

        $supplier = Supplier::create($validated);
        ActivityLogger::log('created_supplier', 'Création du fournisseur '.$supplier->name, $supplier);

        return redirect()->route('commercial.suppliers.index')->with('success', 'Fournisseur créé avec succès.');
    }

    public function edit(Supplier $supplier)
    {
        return view('commercial.suppliers.create_edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:suppliers,code,'.$supplier->id,
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'status' => 'required|in:actif,inactif',
            'notes' => 'nullable|string',
        ]);

        $supplier->update($validated);
        ActivityLogger::log('updated_supplier', 'Modification du fournisseur '.$supplier->name, $supplier);

        return redirect()->route('commercial.suppliers.index')->with('success', 'Fournisseur mis à jour avec succès.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        ActivityLogger::log('deleted_supplier', 'Suppression du fournisseur '.$supplier->name, $supplier);

        return redirect()->route('commercial.suppliers.index')->with('success', 'Fournisseur supprimé avec succès.');
    }
}
