<?php

namespace App\Http\Controllers\Assurance;

use App\Http\Controllers\Controller;
use App\Models\InsuranceCommission;
use App\Models\InsuranceContract;

class AssuranceDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'contrats_actifs' => InsuranceContract::where('status', 'actif')->count(),
            'commissions_attente' => InsuranceCommission::where('status', 'en_attente')->sum('amount'),
            'primes_collectees' => InsuranceContract::sum('premium'),
        ];

        return view('assurance.dashboard', compact('stats'));
    }
}
