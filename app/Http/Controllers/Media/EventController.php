<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
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
        return view('media.events.create_edit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string',
            'status' => 'required|string',
        ]);

        $event = Event::create($validated);
        ActivityLogger::log('create', 'Création d\'un événement', $event);

        return redirect()->route('media.events.index')->with('success', 'Événement créé avec succès.');
    }

    public function show(Event $event)
    {
        $event->load(['customer', 'services']);

        return view('media.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        return view('media.events.create_edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string',
            'status' => 'required|string',
        ]);

        $event->update($validated);
        ActivityLogger::log('update', 'Mise à jour d\'un événement', $event);

        return redirect()->route('media.events.index')->with('success', 'Événement mis à jour avec succès.');
    }

    public function destroy(Event $event)
    {
        ActivityLogger::log('delete', 'Suppression d\'un événement', $event);
        $event->delete();

        return redirect()->route('media.events.index')->with('success', 'Événement supprimé avec succès.');
    }
}
