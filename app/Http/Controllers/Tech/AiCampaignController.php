<?php

namespace App\Http\Controllers\Tech;

use App\Http\Controllers\Controller;
use App\Models\AiCampaign;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class AiCampaignController extends Controller
{
    public function index(Request $request)
    {
        $campaigns = AiCampaign::with(['customer', 'contents'])->latest()->paginate(15);

        return view('tech.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('tech.campaigns.create_edit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string',
            'status' => 'required|string',
        ]);

        $campaign = AiCampaign::create($validated);
        ActivityLogger::log('create', 'Création de la campagne AI', $campaign);

        return redirect()->route('tech.campaigns.index')->with('success', 'Campagne créée avec succès.');
    }

    public function show(AiCampaign $campaign)
    {
        $campaign->load(['customer', 'contents', 'user']);

        return view('tech.campaigns.show', compact('campaign'));
    }

    public function edit(AiCampaign $campaign)
    {
        return view('tech.campaigns.create_edit', compact('campaign'));
    }

    public function update(Request $request, AiCampaign $campaign)
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string',
            'status' => 'required|string',
        ]);

        $campaign->update($validated);
        ActivityLogger::log('update', 'Mise à jour de la campagne AI', $campaign);

        return redirect()->route('tech.campaigns.index')->with('success', 'Campagne mise à jour avec succès.');
    }

    public function destroy(AiCampaign $campaign)
    {
        ActivityLogger::log('delete', 'Suppression de la campagne AI', $campaign);
        $campaign->delete();

        return redirect()->route('tech.campaigns.index')->with('success', 'Campagne supprimée avec succès.');
    }
}
