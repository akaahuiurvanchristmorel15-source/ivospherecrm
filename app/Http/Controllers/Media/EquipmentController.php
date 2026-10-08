<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
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
            'name' => 'required|string',
            'category' => 'required|string',
            'status' => 'required|string',
        ]);

        $item = Equipment::create($validated);
        ActivityLogger::log('create', 'Ajout d\'un équipement', $item);

        return redirect()->route('media.equipment.index')->with('success', 'Équipement ajouté avec succès.');
    }

    public function edit(Equipment $equipment)
    {
        return view('media.equipment.create_edit', compact('equipment'));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category' => 'required|string',
            'status' => 'required|string',
        ]);

        $equipment->update($validated);
        ActivityLogger::log('update', 'Mise à jour d\'un équipement', $equipment);

        return redirect()->route('media.equipment.index')->with('success', 'Équipement mis à jour avec succès.');
    }

    public function destroy(Equipment $equipment)
    {
        ActivityLogger::log('delete', 'Suppression d\'un équipement', $equipment);
        $equipment->delete();

        return redirect()->route('media.equipment.index')->with('success', 'Équipement supprimé avec succès.');
    }
}
