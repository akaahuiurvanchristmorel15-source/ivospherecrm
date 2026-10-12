<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the main central application dashboard.
     */
    public function index(Request $request): View
    {
        return view('dashboard.index', [
            'filters' => $request->only(['domain', 'period', 'date_from', 'date_to']),
        ]);
    }
}
