<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\PhotoSession;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class PhotoSessionController extends Controller
{
    public function index(Request $request)
    {
        $sessions = PhotoSession::with(['customer', 'galleries'])->latest()->paginate(15);

        return view('media.sessions.index', compact('sessions'));
    }

    public function create()
    {
        return view('media.sessions.create_edit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
            'status' => 'required|string',
        ]);

        $session = PhotoSession::create($validated);
        ActivityLogger::log('create', 'Création de la session photo', $session);

        return redirect()->route('media.sessions.index')->with('success', 'Session créée avec succès.');
    }

    public function edit(PhotoSession $session)
    {
        return view('media.sessions.create_edit', compact('session'));
    }

    public function update(Request $request, PhotoSession $session)
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
            'status' => 'required|string',
        ]);

        $session->update($validated);
        ActivityLogger::log('update', 'Mise à jour de la session photo', $session);

        return redirect()->route('media.sessions.index')->with('success', 'Session mise à jour avec succès.');
    }

    public function destroy(PhotoSession $session)
    {
        ActivityLogger::log('delete', 'Suppression de la session photo', $session);
        $session->delete();

        return redirect()->route('media.sessions.index')->with('success', 'Session supprimée avec succès.');
    }
}
