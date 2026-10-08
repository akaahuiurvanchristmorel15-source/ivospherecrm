<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\CommercialAppointment;
use App\Models\Customer;
use App\Models\Prospect;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class CommercialAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = CommercialAppointment::with(['customer', 'prospect', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $appointments = $query->orderBy('date', 'desc')->paginate(15);
        $customers = Customer::active()->orderBy('name')->get();
        $prospects = Prospect::orderBy('name')->get();
        $commercials = User::active()->orderBy('name')->get();

        return view('commercial.appointments.index', compact('appointments', 'customers', 'prospects', 'commercials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'prospect_id' => 'nullable|exists:prospects,id',
            'date' => 'required|date',
            'time' => 'nullable|string|max:10',
            'location' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = $request->input('user_id', auth()->id()) ?: auth()->id();

        $appointment = CommercialAppointment::create($validated);

        ActivityLogger::log('created_appointment', 'Création du rendez-vous commercial : '.$appointment->title, $appointment);

        return back()->with('success', 'Rendez-vous commercial planifié avec succès.');
    }

    public function update(Request $request, CommercialAppointment $appointment)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'prospect_id' => 'nullable|exists:prospects,id',
            'date' => 'required|date',
            'time' => 'nullable|string|max:10',
            'location' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $appointment->update($validated);

        ActivityLogger::log('updated_appointment', 'Mise à jour du rendez-vous commercial : '.$appointment->title, $appointment);

        return back()->with('success', 'Rendez-vous commercial mis à jour avec succès.');
    }

    public function destroy(CommercialAppointment $appointment)
    {
        $title = $appointment->title;
        $appointment->delete();

        ActivityLogger::log('deleted_appointment', 'Suppression du rendez-vous commercial : '.$title, null);

        return back()->with('success', 'Rendez-vous commercial supprimé avec succès.');
    }
}
