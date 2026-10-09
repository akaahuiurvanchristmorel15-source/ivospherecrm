<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Event;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::with(['customer'])->latest()->paginate(15);

        return view('media.events.index', compact('events'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();

        return view('media.events.create_edit', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:50|unique:events,reference',
            'customer_id' => 'nullable|exists:customers,id',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:date',
            'location' => 'nullable|string|max:255',
            'guests_count' => 'nullable|integer|min:0',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|string|max:30',
            'notes' => 'nullable|string',
        ]);

        $mediaDomain = Domain::where('code', 'MEDIA')->first();
        $validated['domain_id'] = $mediaDomain?->id;
        $validated['user_id'] = auth()->id() ?? 1;
        $validated['budget'] = $validated['budget'] ?? 0;
        $validated['total_cost'] = 0;

        $event = Event::create($validated);
        ActivityLogger::log('create', 'Création de l\'événement '.$event->name, $event);

        return redirect()->route('media.events.index')->with('success', 'Événement planifié avec succès.');
    }

    public function show(Event $event)
    {
        $event->load(['customer', 'services', 'user', 'domain']);

        return view('media.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $customers = Customer::orderBy('name')->get();

        return view('media.events.create_edit', compact('event', 'customers'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:50|unique:events,reference,'.$event->id,
            'customer_id' => 'nullable|exists:customers,id',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:date',
            'location' => 'nullable|string|max:255',
            'guests_count' => 'nullable|integer|min:0',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|string|max:30',
            'notes' => 'nullable|string',
        ]);

        $validated['budget'] = $validated['budget'] ?? 0;

        $event->update($validated);
        ActivityLogger::log('update', 'Mise à jour de l\'événement '.$event->name, $event);

        return redirect()->route('media.events.index')->with('success', 'Événement mis à jour avec succès.');
    }

    public function destroy(Event $event)
    {
        ActivityLogger::log('delete', 'Suppression de l\'événement '.$event->name, $event);
        $event->delete();

        return redirect()->route('media.events.index')->with('success', 'Événement supprimé avec succès.');
    }
}
