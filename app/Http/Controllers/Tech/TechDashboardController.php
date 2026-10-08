<?php

namespace App\Http\Controllers\Tech;

use App\Http\Controllers\Controller;
use App\Models\AiCampaign;
use App\Models\TechProject;

class TechDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'projets_en_cours' => TechProject::whereNotIn('status', ['terminé', 'annulé'])->count(),
            'campagnes_actives' => AiCampaign::where('status', 'actif')->count(),
            'ca_tech' => TechProject::sum('budget'),
        ];

        return view('tech.dashboard', compact('stats'));
    }
}
