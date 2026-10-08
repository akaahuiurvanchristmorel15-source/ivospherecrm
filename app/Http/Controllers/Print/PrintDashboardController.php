<?php

namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use App\Models\PrintJob;

class PrintDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'en_cours' => PrintJob::whereNotIn('status', ['terminé', 'livré'])->count(),
            'termines' => PrintJob::whereIn('status', ['terminé', 'livré'])->count(),
            'ca_print' => PrintJob::sum('total'),
        ];

        return view('print.dashboard', compact('stats'));
    }
}
