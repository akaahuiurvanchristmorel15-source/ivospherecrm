<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetUsageController extends Controller
{
    public function store(Request $request, FixedAsset $asset): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
            'order_id' => 'nullable|exists:orders,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'duration_hours' => 'nullable|numeric|min:0',
            'revenue_generated' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $usage = $asset->usages()->create([
            'title' => $validated['title'],
            'date' => $validated['date'],
            'customer_id' => $validated['customer_id'] ?? null,
            'order_id' => $validated['order_id'] ?? null,
            'invoice_id' => $validated['invoice_id'] ?? null,
            'duration_hours' => $validated['duration_hours'] ?? 0,
            'revenue_generated' => $validated['revenue_generated'],
            'notes' => $validated['notes'] ?? null,
            'user_id' => Auth::id(),
        ]);

        ActivityLogger::log('utilisation_actif', "Prestation enregistrée sur l'actif {$asset->code} (CA: ".number_format((float) $usage->revenue_generated, 0, ',', ' ').' FCFA)');

        return back()->with('success', "L'utilisation / prestation a été enregistrée avec succès pour l'actif {$asset->code}.");
    }
}
