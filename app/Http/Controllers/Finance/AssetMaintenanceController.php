<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\AssetMaintenance;
use App\Models\FixedAsset;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetMaintenanceController extends Controller
{
    public function store(Request $request, FixedAsset $asset): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:preventive,curative,revision,etalonnage',
            'maintenance_date' => 'required|date',
            'provider_name' => 'nullable|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'cost' => 'required|numeric|min:0',
            'description' => 'required|string',
            'parts_replaced' => 'nullable|string|max:255',
            'status' => 'required|in:planifiee,en_cours,terminee',
            'next_maintenance_date' => 'nullable|date',
        ]);

        $maintenance = $asset->maintenances()->create([
            'reference' => AssetMaintenance::generateReference(),
            'type' => $validated['type'],
            'maintenance_date' => $validated['maintenance_date'],
            'provider_name' => $validated['provider_name'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'cost' => $validated['cost'],
            'description' => $validated['description'],
            'parts_replaced' => $validated['parts_replaced'] ?? null,
            'status' => $validated['status'],
            'next_maintenance_date' => $validated['next_maintenance_date'] ?? null,
            'user_id' => Auth::id(),
        ]);

        // Si la maintenance est en cours ou curative, basculer le matériel en maintenance
        if ($validated['status'] !== 'terminee') {
            $asset->update(['status' => 'en_maintenance']);
        }

        ActivityLogger::log('maintenance_actif', "Maintenance {$maintenance->reference} enregistrée sur l'actif {$asset->code} (Coût: ".number_format((float) $maintenance->cost, 0, ',', ' ').' FCFA)');

        return back()->with('success', "L'intervention de maintenance {$maintenance->reference} a été enregistrée.");
    }

    public function complete(Request $request, AssetMaintenance $maintenance): RedirectResponse
    {
        $maintenance->update([
            'status' => 'terminee',
        ]);

        $asset = $maintenance->asset;
        if ($asset && $asset->status === 'en_maintenance') {
            $asset->update(['status' => $asset->is_rental_eligible ? 'disponible' : 'en_service']);
        }

        ActivityLogger::log('cloture_maintenance', "Maintenance {$maintenance->reference} terminée pour l'actif {$asset->code}");

        return back()->with('success', "La maintenance {$maintenance->reference} a été marquée comme terminée.");
    }
}
