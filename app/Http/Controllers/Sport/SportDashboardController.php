<?php

namespace App\Http\Controllers\Sport;

use App\Http\Controllers\Controller;
use App\Models\SportArticle;

class SportDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'articles_commandes' => SportArticle::count(),
            'articles_livres' => SportArticle::where('status', 'livré')->count(),
            'ca_sport' => SportArticle::sum('total'),
        ];

        return view('sport.dashboard', compact('stats'));
    }
}
