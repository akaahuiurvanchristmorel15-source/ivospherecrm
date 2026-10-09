<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\EquipmentRental;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EquipmentRentalController extends Controller
{
    public function index(Request $request)
    {
        $rentals = EquipmentRental::with(['equipment', 'customer'])->latest()->paginate(15);

        return view('media.rentals.index', compact('rentals'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $equipment = Equipment::orderBy('name')->get();

        return view('media.rentals.create_edit', compact('customers', 'equipment'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:50|unique:equipment_rentals,reference',
            'equipment_id' => 'required|exists:equipment,id',
            'customer_id' => 'nullable|exists:customers,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'daily_rate' => 'nullable|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'condition_before' => 'nullable|string|max:30',
            'status' => 'required|string|max:30',
            'notes' => 'nullable|string',
        ]);

        $item = Equipment::find($validated['equipment_id']);
        $validated['daily_rate'] = $validated['daily_rate'] ?? $item?->daily_rate ?? 0;
        $validated['deposit'] = $validated['deposit'] ?? 0;
        $validated['penalty'] = 0;
        $validated['user_id'] = auth()->id() ?? 1;

        $days = max(1, Carbon::parse($validated['start_date'])->diffInDays(Carbon::parse($validated['end_date'])) + 1);
        $validated['total'] = $days * $validated['daily_rate'];

        $rental = EquipmentRental::create($validated);

        $item?->update(['status' => 'en_location', 'is_available' => false]);

        ActivityLogger::log('create', 'Création d\'un contrat de location '.$rental->reference, $rental);

        return redirect()->route('media.rentals.index')->with('success', 'Location enregistrée avec succès.');
    }

    public function edit(EquipmentRental $rental)
    {
        $customers = Customer::orderBy('name')->get();
        $equipment = Equipment::orderBy('name')->get();

        return view('media.rentals.create_edit', compact('rental', 'customers', 'equipment'));
    }

    public function update(Request $request, EquipmentRental $rental)
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:50|unique:equipment_rentals,reference,'.$rental->id,
            'equipment_id' => 'required|exists:equipment,id',
            'customer_id' => 'nullable|exists:customers,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'daily_rate' => 'nullable|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'condition_before' => 'nullable|string|max:30',
            'condition_after' => 'nullable|string|max:30',
            'status' => 'required|string|max:30',
            'notes' => 'nullable|string',
        ]);

        $days = max(1, Carbon::parse($validated['start_date'])->diffInDays(Carbon::parse($validated['end_date'])) + 1);
        $validated['total'] = $days * ($validated['daily_rate'] ?? $rental->daily_rate);

        $rental->update($validated);
        ActivityLogger::log('update', 'Mise à jour de la location '.$rental->reference, $rental);

        return redirect()->route('media.rentals.index')->with('success', 'Location mise à jour avec succès.');
    }

    public function returnEquipment(Request $request, EquipmentRental $rental)
    {
        $rental->update(['status' => 'retourné', 'returned_at' => now()]);
        $rental->equipment?->update(['status' => 'disponible', 'is_available' => true]);
        ActivityLogger::log('update', 'Retour d\'équipement '.$rental->reference, $rental);

        return redirect()->route('media.rentals.index')->with('success', 'Équipement retourné et marqué disponible.');
    }

    public function destroy(EquipmentRental $rental)
    {
        ActivityLogger::log('delete', 'Suppression de la location '.$rental->reference, $rental);
        $rental->equipment?->update(['status' => 'disponible', 'is_available' => true]);
        $rental->delete();

        return redirect()->route('media.rentals.index')->with('success', 'Location supprimée avec succès.');
    }
}
