<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\SmartAlert;
use App\Services\AutomationEngineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function __construct(
        protected AutomationEngineService $automationService
    ) {}

    public function index(Request $request): View
    {
        $query = SmartAlert::query()->with(['domain', 'assignee']);

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'active');
        }

        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        $alerts = $query->orderByRaw("CASE 
            WHEN priority = 'urgente' THEN 1 
            WHEN priority = 'haute' THEN 2 
            WHEN priority = 'moyenne' THEN 3 
            ELSE 4 END")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $domains = Domain::all();
        $counts = [
            'active' => SmartAlert::where('status', 'active')->count(),
            'urgente' => SmartAlert::where('status', 'active')->where('priority', 'urgente')->count(),
            'haute' => SmartAlert::where('status', 'active')->where('priority', 'haute')->count(),
            'resolved' => SmartAlert::where('status', 'resolved')->count(),
        ];

        return view('alerts.index', compact('alerts', 'domains', 'counts'));
    }

    public function resolve(SmartAlert $alert): RedirectResponse
    {
        $alert->update(['status' => 'resolved']);

        return back()->with('success', "Alerte « {$alert->title} » marquée comme résolue.");
    }

    public function dismiss(SmartAlert $alert): RedirectResponse
    {
        $alert->update(['status' => 'dismissed']);

        return back()->with('info', 'Alerte masquée.');
    }

    public function runChecks(): RedirectResponse
    {
        $result = $this->automationService->runChecks();

        return back()->with('success', "Vérification automatisée terminée : {$result['alerts_generated']} nouvelle(s) alerte(s) générée(s).");
    }
}
