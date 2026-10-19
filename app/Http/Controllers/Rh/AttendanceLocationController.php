<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceLocationController extends Controller
{
    /**
     * Lieux associés au QR code de pointage (restriction géographique de l'arrivée et du départ).
     */
    public function index(): View
    {
        $locations = AttendanceLocation::query()->orderByDesc('is_active')->orderBy('name')->get();
        $defaultRadius = (int) config('attendance.geofence.default_radius_meters', 100);
        $maxAccuracy = (int) config('attendance.geofence.max_accuracy_meters', 100);

        return view('rh.attendance.locations', compact('locations', 'defaultRadius', 'maxAccuracy'));
    }

    public function store(Request $request): RedirectResponse
    {
        $location = AttendanceLocation::create($this->validated($request));

        ActivityLogger::log('lieu_pointage_ajoute', "Lieu de pointage « {$location->name} » ajouté", $location);

        return redirect()->route('rh.attendance.locations.index')->with('success', "Lieu « {$location->name} » ajouté.");
    }

    public function update(Request $request, AttendanceLocation $location): RedirectResponse
    {
        $location->update($this->validated($request));

        ActivityLogger::log('lieu_pointage_modifie', "Lieu de pointage « {$location->name} » modifié", $location);

        return redirect()->route('rh.attendance.locations.index')->with('success', "Lieu « {$location->name} » mis à jour.");
    }

    public function destroy(AttendanceLocation $location): RedirectResponse
    {
        $name = $location->name;
        $location->delete();

        ActivityLogger::log('lieu_pointage_supprime', "Lieu de pointage « {$name} » supprimé");

        return redirect()->route('rh.attendance.locations.index')->with('success', "Lieu « {$name} » supprimé.");
    }

    /**
     * @return array{name: string, latitude: float, longitude: float, radius_meters: int|null, is_active: bool}
     */
    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['nullable', 'integer', 'min:10', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'name' => 'nom du lieu',
            'radius_meters' => 'rayon',
        ]);

        return [
            'name' => $validated['name'],
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'radius_meters' => $validated['radius_meters'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
