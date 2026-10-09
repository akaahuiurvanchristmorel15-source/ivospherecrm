<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Equipment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $equipment = Equipment::latest()->paginate(15);

        return view('media.equipment.index', compact('equipment'));
    }

    public function create()
    {
        return view('media.equipment.create_edit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'serial_number' => 'nullable|string|max:50|unique:equipment,serial_number',
            'condition' => 'nullable|string|max:30',
            'daily_rate' => 'nullable|numeric|min:0',
            'value' => 'nullable|numeric|min:0',
            'status' => 'required|string|max:30',
            'is_available' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $mediaDomain = Domain::where('code', 'MEDIA')->first();
        $validated['domain_id'] = $mediaDomain?->id;
        $validated['daily_rate'] = $validated['daily_rate'] ?? 0;
        $validated['value'] = $validated['value'] ?? 0;
        $validated['condition'] = $validated['condition'] ?? 'bon_etat';
        $validated['is_available'] = $request->has('is_available') ? (bool) $request->is_available : true;

        $item = Equipment::create($validated);
        ActivityLogger::log('create', 'Ajout d\'un équipement '.$item->name, $item);

        return redirect()->route('media.equipment.index')->with('success', 'Équipement ajouté avec succès.');
    }

    public function show(Equipment $equipment)
    {
        return view('media.equipment.show', compact('equipment'));
    }

    public function edit(Equipment $equipment)
    {
        return view('media.equipment.create_edit', compact('equipment'));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'serial_number' => 'nullable|string|max:50|unique:equipment,serial_number,'.$equipment->id,
            'condition' => 'nullable|string|max:30',
            'daily_rate' => 'nullable|numeric|min:0',
            'value' => 'nullable|numeric|min:0',
            'status' => 'required|string|max:30',
            'is_available' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $validated['daily_rate'] = $validated['daily_rate'] ?? 0;
        $validated['value'] = $validated['value'] ?? 0;
        $validated['is_available'] = $request->has('is_available') ? (bool) $request->is_available : ($validated['status'] === 'disponible');

        $equipment->update($validated);
        ActivityLogger::log('update', 'Mise à jour d\'un équipement '.$equipment->name, $equipment);

        return redirect()->route('media.equipment.index')->with('success', 'Équipement mis à jour avec succès.');
    }

    public function destroy(Equipment $equipment)
    {
        ActivityLogger::log('delete', 'Suppression d\'un équipement '.$equipment->name, $equipment);
        $equipment->delete();

        return redirect()->route('media.equipment.index')->with('success', 'Équipement supprimé avec succès.');
    }
}
