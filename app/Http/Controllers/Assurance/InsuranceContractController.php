<?php

namespace App\Http\Controllers\Assurance;

use App\Http\Controllers\Controller;
use App\Models\InsuranceContract;
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
        return view('assurance.contracts.create_edit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:insurance_products,id',
            'status' => 'required|string',
        ]);

        $contract = InsuranceContract::create($validated);
        ActivityLogger::log('create', 'Création d\'un contrat d\'assurance', $contract);

        return redirect()->route('assurance.contracts.index')->with('success', 'Contrat créé avec succès.');
    }

    public function show(InsuranceContract $contract)
    {
        $contract->load(['customer', 'product', 'commissions']);

        return view('assurance.contracts.show', compact('contract'));
    }

    public function edit(InsuranceContract $contract)
    {
        return view('assurance.contracts.create_edit', compact('contract'));
    }

    public function update(Request $request, InsuranceContract $contract)
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:insurance_products,id',
            'status' => 'required|string',
        ]);

        $contract->update($validated);
        ActivityLogger::log('update', 'Mise à jour d\'un contrat d\'assurance', $contract);

        return redirect()->route('assurance.contracts.index')->with('success', 'Contrat mis à jour avec succès.');
    }

    public function destroy(InsuranceContract $contract)
    {
        ActivityLogger::log('delete', 'Suppression d\'un contrat d\'assurance', $contract);
        $contract->delete();

        return redirect()->route('assurance.contracts.index')->with('success', 'Contrat supprimé avec succès.');
    }
}
