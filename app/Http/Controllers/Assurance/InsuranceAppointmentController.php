<?php

namespace App\Http\Controllers\Assurance;

use App\Http\Controllers\Controller;
use App\Models\InsuranceAppointment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class InsuranceAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $appointments = InsuranceAppointment::with(['customer', 'advisor'])->latest()->paginate(15);

        return view('assurance.appointments.index', compact('appointments'));
    }

    public function create()
    {
        return view('assurance.appointments.create_edit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'status' => 'required|string',
        ]);

        $appointment = InsuranceAppointment::create($validated);
        ActivityLogger::log('create', 'Création d\'un rendez-vous', $appointment);

        return redirect()->route('assurance.appointments.index')->with('success', 'Rendez-vous créé avec succès.');
    }

    public function edit(InsuranceAppointment $appointment)
    {
        return view('assurance.appointments.create_edit', compact('appointment'));
    }

    public function update(Request $request, InsuranceAppointment $appointment)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'status' => 'required|string',
        ]);

        $appointment->update($validated);
        ActivityLogger::log('update', 'Mise à jour d\'un rendez-vous', $appointment);

        return redirect()->route('assurance.appointments.index')->with('success', 'Rendez-vous mis à jour avec succès.');
    }

    public function destroy(InsuranceAppointment $appointment)
    {
        ActivityLogger::log('delete', 'Suppression d\'un rendez-vous', $appointment);
        $appointment->delete();

        return redirect()->route('assurance.appointments.index')->with('success', 'Rendez-vous supprimé avec succès.');
    }
}
