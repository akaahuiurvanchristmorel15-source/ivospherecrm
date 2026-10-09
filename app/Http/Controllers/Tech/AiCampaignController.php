<?php

namespace App\Http\Controllers\Tech;

use App\Http\Controllers\Controller;
use App\Models\AiCampaign;
use App\Models\Customer;
use App\Models\Domain;
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
        $customers = Customer::orderBy('name')->get();

        return view('tech.campaigns.create_edit', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string|unique:ai_campaigns,reference',
            'customer_id' => 'nullable|exists:customers,id',
            'name' => 'required|string|max:255',
            'status' => 'required|string|max:30',
            'brief' => 'nullable|string',
            'channels' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'results' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id() ?? 1;
        $validated['domain_id'] = Domain::where('code', 'TECH')->value('id');
        $validated['budget'] = $validated['budget'] ?? 0;

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
        $customers = Customer::orderBy('name')->get();

        return view('tech.campaigns.create_edit', compact('campaign', 'customers'));
    }

    public function update(Request $request, AiCampaign $campaign)
    {
        $validated = $request->validate([
            'reference' => 'required|string|unique:ai_campaigns,reference,'.$campaign->id,
            'customer_id' => 'nullable|exists:customers,id',
            'name' => 'required|string|max:255',
            'status' => 'required|string|max:30',
            'brief' => 'nullable|string',
            'channels' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'results' => 'nullable|string',
        ]);

        $validated['budget'] = $validated['budget'] ?? 0;

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
