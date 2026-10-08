<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\AssetRental;
use App\Models\Customer;
use App\Models\FixedAsset;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AssetRentalController extends Controller
{
    public function index(Request $request): View
    {
        $query = AssetRental::with(['asset.domain', 'customer', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('asset_id')) {
            $query->where('fixed_asset_id', $request->asset_id);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $rentals = $query->latest('start_date')->paginate(15)->withQueryString();

        // Statistiques locations
        $allRentals = AssetRental::all();
        $totalRentalRevenue = (float) $allRentals->where('status', '!=', 'annule')->sum('total_amount');
        $activeRentalsCount = $allRentals->where('status', 'loue')->count();
        $pendingReturnsCount = $allRentals->where('status', 'retourne_controle')->count();
        $totalDepositsHeld = (float) $allRentals->where('deposit_returned', false)->where('status', '!=', 'annule')->sum('deposit_amount');

        $eligibleAssets = FixedAsset::rentalEligible()->whereIn('status', ['disponible', 'en_service'])->orderBy('name')->get();
        $customers = Customer::orderBy('company_name')->get();

        return view('finance.assets.rentals.index', compact(
            'rentals',
            'eligibleAssets',
            'customers',
            'totalRentalRevenue',
            'activeRentalsCount',
            'pendingReturnsCount',
            'totalDepositsHeld'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fixed_asset_id' => 'required|exists:fixed_assets,id',
            'customer_id' => 'required|exists:customers,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'daily_rate' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'condition_at_departure' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $days = max(1, $start->diffInDays($end) + 1);

        $asset = FixedAsset::findOrFail($validated['fixed_asset_id']);

        $rental = AssetRental::create([
            'reference' => AssetRental::generateReference(),
            'fixed_asset_id' => $asset->id,
            'customer_id' => $validated['customer_id'],
            'user_id' => Auth::id(),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'daily_rate' => $validated['daily_rate'],
            'total_days' => $days,
            'total_amount' => $days * (float) $validated['daily_rate'],
            'deposit_amount' => $validated['deposit_amount'] ?? $asset->rental_deposit_amount ?? 0,
            'deposit_returned' => false,
            'status' => 'loue',
            'condition_at_departure' => $validated['condition_at_departure'] ?? $asset->condition,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Mise à jour de l'état du matériel
        $asset->update(['status' => 'loue']);

        ActivityLogger::log('creation_location_actif', "Location {$rental->reference} du matériel {$asset->code} pour le client");

        return back()->with('success', "Le contrat de location {$rental->reference} a été enregistré avec succès.");
    }

    public function updateStatus(Request $request, AssetRental $rental): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:reserve,loue,retourne_controle,cloture,annule',
            'actual_return_date' => 'nullable|date',
            'condition_at_return' => 'nullable|string|max:100',
            'deposit_returned' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $asset = $rental->asset;

        $updateData = ['status' => $validated['status']];

        if (! empty($validated['actual_return_date'])) {
            $updateData['actual_return_date'] = $validated['actual_return_date'];
        }

        if (! empty($validated['condition_at_return'])) {
            $updateData['condition_at_return'] = $validated['condition_at_return'];
        }

        if ($request->has('deposit_returned')) {
            $updateData['deposit_returned'] = $request->boolean('deposit_returned');
        }

        if (! empty($validated['notes'])) {
            $updateData['notes'] = ($rental->notes ? $rental->notes."\n" : '').$validated['notes'];
        }

        $rental->update($updateData);

        // Ajustement automatique du statut du matériel
        if ($validated['status'] === 'loue') {
            $asset->update(['status' => 'loue']);
        } elseif ($validated['status'] === 'retourne_controle') {
            $asset->update(['status' => 'en_maintenance']);
        } elseif ($validated['status'] === 'cloture') {
            $asset->update(['status' => 'disponible']);
        } elseif ($validated['status'] === 'annule') {
            $asset->update(['status' => 'disponible']);
        }

        ActivityLogger::log('changement_statut_location', "Mise à jour statut location {$rental->reference} -> {$validated['status']}");

        return back()->with('success', "Le statut de la location {$rental->reference} a été mis à jour ({$validated['status']}).");
    }
}
