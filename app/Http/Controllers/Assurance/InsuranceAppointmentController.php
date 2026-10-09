<?php

namespace App\Http\Controllers\Assurance;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InsuranceAppointment;
use App\Models\User;
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
        $customers = Customer::orderBy('name')->get();
        $advisors = User::orderBy('name')->get();

        return view('assurance.appointments.create_edit', compact('customers', 'advisors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'advisor_id' => 'nullable|exists:users,id',
            'date' => 'required|date',
            'time' => 'nullable|string',
            'status' => 'required|string|max:30',
            'notes' => 'nullable|string',
        ]);

        $validated['advisor_id'] = $validated['advisor_id'] ?? auth()->id();

        $appointment = InsuranceAppointment::create($validated);
        ActivityLogger::log('create', 'Création d\'un rendez-vous d\'assurance', $appointment);

        return redirect()->route('assurance.appointments.index')->with('success', 'Rendez-vous planifié avec succès.');
    }

    public function edit(InsuranceAppointment $appointment)
    {
        $customers = Customer::orderBy('name')->get();
        $advisors = User::orderBy('name')->get();

        return view('assurance.appointments.create_edit', compact('appointment', 'customers', 'advisors'));
    }

    public function update(Request $request, InsuranceAppointment $appointment)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'advisor_id' => 'nullable|exists:users,id',
            'date' => 'required|date',
            'time' => 'nullable|string',
            'status' => 'required|string|max:30',
            'notes' => 'nullable|string',
        ]);

        $appointment->update($validated);
        ActivityLogger::log('update', 'Mise à jour d\'un rendez-vous d\'assurance', $appointment);

        return redirect()->route('assurance.appointments.index')->with('success', 'Rendez-vous mis à jour avec succès.');
    }

    public function destroy(InsuranceAppointment $appointment)
    {
        ActivityLogger::log('delete', 'Suppression d\'un rendez-vous d\'assurance', $appointment);
        $appointment->delete();

        return redirect()->route('assurance.appointments.index')->with('success', 'Rendez-vous supprimé avec succès.');
    }
}
