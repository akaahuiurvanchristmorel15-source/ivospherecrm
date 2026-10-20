<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Services\CyberAgentPerformanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Suivi et performance des agents du cyber (mensuel), comme la feuille « Suivi Employés » du classeur Excel.
 */
class CyberPerformanceController extends Controller
{
    public function index(Request $request, CyberAgentPerformanceService $performance): View
    {
        $month = preg_match('/^\d{4}-\d{2}$/', (string) $request->query('mois'))
            ? Carbon::createFromFormat('Y-m-d', $request->query('mois').'-01')->startOfMonth()
            : now()->startOfMonth();

        if ($month->isAfter(now()->startOfMonth())) {
            $month = now()->startOfMonth();
        }

        return view('commercial.cyber.performance', [
            'month' => $month,
            'report' => $performance->forMonth($month),
        ]);
    }
}
