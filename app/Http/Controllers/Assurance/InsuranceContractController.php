<?php

namespace App\Http\Controllers\Assurance;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InsuranceContract;
use App\Models\InsuranceProduct;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class InsuranceContractController extends Controller
{
    public function index(Request $request)
    {
        $contracts = InsuranceContract::with(['customer', 'product'])->latest()->paginate(15);

        return view('assurance.contracts.index', compact('contracts'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = InsuranceProduct::orderBy('name')->get();

        return view('assurance.contracts.create_edit', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:50|unique:insurance_contracts,reference',
            'customer_id' => 'nullable|exists:customers,id',
            'product_id' => 'required|exists:insurance_products,id',
            'partner' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'premium' => 'required|numeric|min:0',
            'frequency' => 'required|string|in:mensuel,trimestriel,semestriel,annuel,unique',
            'status' => 'required|string|in:actif,en_attente,resilie,expire',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['partner'])) {
            $product = InsuranceProduct::find($validated['product_id']);
            $validated['partner'] = $product?->partner ?? 'Partenaire Assurance';
        }

        $validated['user_id'] = auth()->id() ?? 1;

        $contract = InsuranceContract::create($validated);
        ActivityLogger::log('create', 'Création du contrat d\'assurance '.$contract->reference, $contract);

        return redirect()->route('assurance.contracts.index')->with('success', 'Contrat d\'assurance créé avec succès.');
    }

    public function show(InsuranceContract $contract)
    {
        $contract->load(['customer', 'product', 'commissions', 'user']);

        return view('assurance.contracts.show', compact('contract'));
    }

    public function edit(InsuranceContract $contract)
    {
        $customers = Customer::orderBy('name')->get();
        $products = InsuranceProduct::orderBy('name')->get();

        return view('assurance.contracts.create_edit', compact('contract', 'customers', 'products'));
    }

    public function update(Request $request, InsuranceContract $contract)
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:50|unique:insurance_contracts,reference,'.$contract->id,
            'customer_id' => 'nullable|exists:customers,id',
            'product_id' => 'required|exists:insurance_products,id',
            'partner' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'premium' => 'required|numeric|min:0',
            'frequency' => 'required|string|in:mensuel,trimestriel,semestriel,annuel,unique',
            'status' => 'required|string|in:actif,en_attente,resilie,expire',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['partner'])) {
            $product = InsuranceProduct::find($validated['product_id']);
            $validated['partner'] = $product?->partner ?? $contract->partner;
        }

        $contract->update($validated);
        ActivityLogger::log('update', 'Mise à jour du contrat d\'assurance '.$contract->reference, $contract);

        return redirect()->route('assurance.contracts.index')->with('success', 'Contrat d\'assurance mis à jour avec succès.');
    }

    public function destroy(InsuranceContract $contract)
    {
        ActivityLogger::log('delete', 'Suppression du contrat d\'assurance '.$contract->reference, $contract);
        $contract->delete();

        return redirect()->route('assurance.contracts.index')->with('success', 'Contrat supprimé avec succès.');
    }
}
