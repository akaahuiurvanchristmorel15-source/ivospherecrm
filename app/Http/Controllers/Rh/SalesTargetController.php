<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\SalesTarget;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesTargetController extends Controller
{
    /**
     * Display a listing of sales targets with global KPIs and performance tracking.
     */
    public function index(Request $request): View
    {
        $employeeId = $request->query('employee_id');
        $domainId = $request->query('domain_id');
        $status = $request->query('status');
        $period = $request->query('period');

        $query = SalesTarget::with(['employee', 'domain'])->latest('start_date');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($domainId) {
            $query->where('domain_id', $domainId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($period) {
            $query->where('period', $period);
        }

        $targets = $query->paginate(15)->withQueryString();

        // Statistiques globales RH
        $allTargets = SalesTarget::when($domainId, fn ($q) => $q->where('domain_id', $domainId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->get();

        $totalTargetAmount = (float) $allTargets->sum('target_amount');
        $totalAchievedAmount = (float) $allTargets->sum('achieved_amount');
        $averageAchievementRate = $totalTargetAmount > 0
            ? round(($totalAchievedAmount / $totalTargetAmount) * 100, 1)
            : 0;

        $totalCommissions = $allTargets->sum(fn ($t) => $t->estimated_commission);
        $activeCount = $allTargets->where('status', 'en_cours')->count();
        $achievedCount = $allTargets->where('status', 'atteint')->count();

        $employees = Employee::where('status', 'actif')->orderBy('first_name')->get();
        $domains = Domain::where('is_active', true)->get();

        return view('rh.sales_targets.index', compact(
            'targets',
            'employees',
            'domains',
            'totalTargetAmount',
            'totalAchievedAmount',
            'averageAchievementRate',
            'totalCommissions',
            'activeCount',
            'achievedCount'
        ));
    }

    /**
     * Show the form for creating a new sales target.
     */
    public function create(): View
    {
        $employees = Employee::where('status', 'actif')->orderBy('first_name')->get();
        $domains = Domain::where('is_active', true)->get();

        return view('rh.sales_targets.create_edit', compact('employees', 'domains'));
    }

    /**
     * Store a newly created sales target in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'domain_id' => 'nullable|exists:domains,id',
            'title' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:1',
            'achieved_amount' => 'nullable|numeric|min:0',
            'target_sales_count' => 'nullable|integer|min:0',
            'achieved_sales_count' => 'nullable|integer|min:0',
            'period' => 'required|string|in:mensuel,trimestriel,semestriel,annuel,personnalise',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'bonus_amount' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:en_cours,atteint,partiel,non_atteint,annule',
            'notes' => 'nullable|string',
        ]);

        $validated['achieved_amount'] = $validated['achieved_amount'] ?? 0;
        $validated['commission_rate'] = $validated['commission_rate'] ?? 0;
        $validated['bonus_amount'] = $validated['bonus_amount'] ?? 0;

        // Auto-statut si montant atteint
        if ($validated['status'] === 'en_cours' && $validated['achieved_amount'] >= $validated['target_amount']) {
            $validated['status'] = 'atteint';
        }

        $target = SalesTarget::create($validated);
        ActivityLogger::log('create', "Création de l'objectif commercial : {$target->title}", $target);

        return redirect()->route('rh.sales-targets.index')->with('success', 'Objectif de vente assigné avec succès.');
    }

    /**
     * Display the specified sales target details.
     */
    public function show(SalesTarget $salesTarget): View
    {
        $salesTarget->load(['employee', 'domain']);

        // Recherche des commandes réalisées pendant la période si l'employé a un compte utilisateur
        $relatedOrders = collect();
        if ($salesTarget->employee && $salesTarget->employee->user_id) {
            $relatedOrders = Order::where('user_id', $salesTarget->employee->user_id)
                ->whereBetween('date', [$salesTarget->start_date->toDateString(), $salesTarget->end_date->toDateString()])
                ->latest('date')
                ->take(10)
                ->get();
        }

        return view('rh.sales_targets.show', compact('salesTarget', 'relatedOrders'));
    }

    /**
     * Show the form for editing the specified sales target.
     */
    public function edit(SalesTarget $salesTarget): View
    {
        $employees = Employee::where('status', 'actif')->orderBy('first_name')->get();
        $domains = Domain::where('is_active', true)->get();

        return view('rh.sales_targets.create_edit', compact('salesTarget', 'employees', 'domains'));
    }

    /**
     * Update the specified sales target in storage.
     */
    public function update(Request $request, SalesTarget $salesTarget): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'domain_id' => 'nullable|exists:domains,id',
            'title' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:1',
            'achieved_amount' => 'required|numeric|min:0',
            'target_sales_count' => 'nullable|integer|min:0',
            'achieved_sales_count' => 'nullable|integer|min:0',
            'period' => 'required|string|in:mensuel,trimestriel,semestriel,annuel,personnalise',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'bonus_amount' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:en_cours,atteint,partiel,non_atteint,annule',
            'notes' => 'nullable|string',
        ]);

        // Auto-statut si montant atteint
        if ($validated['status'] === 'en_cours' && $validated['achieved_amount'] >= $validated['target_amount']) {
            $validated['status'] = 'atteint';
        }

        $salesTarget->update($validated);
        ActivityLogger::log('update', "Mise à jour de l'objectif commercial : {$salesTarget->title}", $salesTarget);

        return redirect()->route('rh.sales-targets.index')->with('success', 'Objectif de vente mis à jour avec succès.');
    }

    /**
     * Recalculate achieved sales from actual database orders/invoices.
     */
    public function recalculate(SalesTarget $salesTarget): RedirectResponse
    {
        $salesTarget->load('employee');

        if ($salesTarget->employee && $salesTarget->employee->user_id) {
            $userId = $salesTarget->employee->user_id;

            // Calcul depuis les factures payées/validées
            $actualSales = (float) Invoice::where('user_id', $userId)
                ->when($salesTarget->domain_id, fn ($q) => $q->where('domain_id', $salesTarget->domain_id))
                ->whereBetween('date', [$salesTarget->start_date->toDateString(), $salesTarget->end_date->toDateString()])
                ->where('status', '!=', 'annulee')
                ->sum('total');

            $actualCount = Order::where('user_id', $userId)
                ->when($salesTarget->domain_id, fn ($q) => $q->where('domain_id', $salesTarget->domain_id))
                ->whereBetween('date', [$salesTarget->start_date->toDateString(), $salesTarget->end_date->toDateString()])
                ->count();

            $salesTarget->achieved_amount = $actualSales;
            $salesTarget->achieved_sales_count = $actualCount;

            if ($salesTarget->achieved_amount >= $salesTarget->target_amount) {
                $salesTarget->status = 'atteint';
            }

            $salesTarget->save();

            return back()->with('success', "Objectif synchronisé avec succès : {$actualSales} FCFA réalisés.");
        }

        return back()->with('info', 'Cet employé n\'a pas de compte utilisateur lié pour synchroniser automatiquement les ventes.');
    }

    /**
     * Remove the specified sales target from storage.
     */
    public function destroy(SalesTarget $salesTarget): RedirectResponse
    {
        ActivityLogger::log('delete', "Suppression de l'objectif commercial : {$salesTarget->title}", $salesTarget);
        $salesTarget->delete();

        return redirect()->route('rh.sales-targets.index')->with('success', 'Objectif de vente supprimé avec succès.');
    }
}
