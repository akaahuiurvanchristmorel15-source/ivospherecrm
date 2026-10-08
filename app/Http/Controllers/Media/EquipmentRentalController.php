<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\EquipmentRental;
use App\Services\ActivityLogger;
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
        return view('media.rentals.create_edit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'equipment_id' => 'required|exists:equipment,id',
            'customer_id' => 'required|exists:customers,id',
            'status' => 'required|string',
        ]);

        $rental = EquipmentRental::create($validated);
        ActivityLogger::log('create', 'Création d\'une location', $rental);

        return redirect()->route('media.rentals.index')->with('success', 'Location créée avec succès.');
    }

    public function edit(EquipmentRental $rental)
    {
        return view('media.rentals.create_edit', compact('rental'));
    }

    public function update(Request $request, EquipmentRental $rental)
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'equipment_id' => 'required|exists:equipment,id',
            'customer_id' => 'required|exists:customers,id',
            'status' => 'required|string',
        ]);

        $rental->update($validated);
        ActivityLogger::log('update', 'Mise à jour d\'une location', $rental);

        return redirect()->route('media.rentals.index')->with('success', 'Location mise à jour avec succès.');
    }

    public function returnEquipment(Request $request, EquipmentRental $rental)
    {
        $rental->update(['status' => 'retourné', 'returned_at' => now()]);
        ActivityLogger::log('update', 'Retour d\'équipement', $rental);

        return redirect()->route('media.rentals.index')->with('success', 'Équipement retourné avec succès.');
    }

    public function destroy(EquipmentRental $rental)
    {
        ActivityLogger::log('delete', 'Suppression d\'une location', $rental);
        $rental->delete();

        return redirect()->route('media.rentals.index')->with('success', 'Location supprimée avec succès.');
    }
}
