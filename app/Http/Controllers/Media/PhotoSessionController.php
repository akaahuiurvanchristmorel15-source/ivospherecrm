<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
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
        $customers = Customer::orderBy('name')->get();

        return view('media.sessions.create_edit', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:50|unique:photo_sessions,reference',
            'customer_id' => 'nullable|exists:customers,id',
            'photographer' => 'nullable|string|max:100',
            'date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'package' => 'nullable|string|max:50',
            'status' => 'required|string|max:30',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $mediaDomain = Domain::where('code', 'MEDIA')->first();
        $validated['domain_id'] = $mediaDomain?->id;
        $validated['user_id'] = auth()->id() ?? 1;
        $validated['price'] = $validated['price'] ?? 0;

        $session = PhotoSession::create($validated);
        ActivityLogger::log('create', 'Création de la session photo '.$session->reference, $session);

        return redirect()->route('media.sessions.index')->with('success', 'Séance photo créée avec succès.');
    }

    public function show(PhotoSession $session)
    {
        $session->load(['customer', 'galleries', 'user', 'domain']);

        return view('media.sessions.show', compact('session'));
    }

    public function edit(PhotoSession $session)
    {
        $customers = Customer::orderBy('name')->get();

        return view('media.sessions.create_edit', compact('session', 'customers'));
    }

    public function update(Request $request, PhotoSession $session)
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:50|unique:photo_sessions,reference,'.$session->id,
            'customer_id' => 'nullable|exists:customers,id',
            'photographer' => 'nullable|string|max:100',
            'date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'package' => 'nullable|string|max:50',
            'status' => 'required|string|max:30',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['price'] = $validated['price'] ?? 0;

        $session->update($validated);
        ActivityLogger::log('update', 'Mise à jour de la session photo '.$session->reference, $session);

        return redirect()->route('media.sessions.index')->with('success', 'Séance photo mise à jour avec succès.');
    }

    public function destroy(PhotoSession $session)
    {
        ActivityLogger::log('delete', 'Suppression de la session photo '.$session->reference, $session);
        $session->delete();

        return redirect()->route('media.sessions.index')->with('success', 'Séance supprimée avec succès.');
    }
}
