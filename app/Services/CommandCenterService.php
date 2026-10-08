<?php

namespace App\Services;

use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quotation;
use App\Models\SmartAlert;
use App\Models\StockAlert;
use App\Models\StockMovement;
use App\Models\TechProject;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommandCenterService
{
    /**
     * Compute full dashboard metrics for the Direction Command Center.
     *
     * @return array<string, mixed>
     */
    public function getMetrics(string $period = 'month', ?string $domainSlug = null): array
    {
        [$startDate, $endDate] = $this->getDateRange($period);

        $selectedDomain = null;
        if ($domainSlug && $domainSlug !== 'all') {
            $selectedDomain = Domain::where('code', strtoupper($domainSlug))
                ->orWhere('code', strtolower($domainSlug))
                ->first();
        }

        // 1. Revenue calculations
        $ordersQuery = Order::query()->where('status', '!=', 'annulee');
        if ($startDate && $endDate) {
            $ordersQuery->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                    ->orWhere(fn ($sub) => $sub->whereDate('date', '>=', $startDate->toDateString())->whereDate('date', '<=', $endDate->toDateString()));
            });
        }
        if ($selectedDomain) {
            $ordersQuery->where('domain_id', $selectedDomain->id);
        }
        $totalRevenue = (float) $ordersQuery->sum('total');
        $ordersCount = $ordersQuery->count();

        // Revenue per domain
        $domains = Domain::all();
        $domainRevenues = [];
        foreach ($domains as $domain) {
            $query = Order::query()
                ->where('status', '!=', 'annulee')
                ->where('domain_id', $domain->id);

            if ($startDate && $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate])
                        ->orWhere(fn ($sub) => $sub->whereDate('date', '>=', $startDate->toDateString())->whereDate('date', '<=', $endDate->toDateString()));
                });
            }

            $revenue = (float) $query->sum('total');
            $domainRevenues[] = [
                'id' => $domain->id,
                'name' => $domain->name,
                'code' => $domain->code,
                'color' => $domain->color ?? '#0066FF',
                'revenue' => $revenue,
                'share' => $totalRevenue > 0 ? round(($revenue / $totalRevenue) * 100, 1) : 0,
            ];
        }

        // 2. Expenses & Estimated Profit
        $expensesQuery = Expense::query()->whereIn('status', ['validee', 'payé', 'payee']);
        if ($startDate && $endDate) {
            $expensesQuery->whereDate('date', '>=', $startDate->toDateString())
                ->whereDate('date', '<=', $endDate->toDateString());
        }
        if ($selectedDomain) {
            $expensesQuery->where('domain_id', $selectedDomain->id);
        }
        $totalExpenses = (float) $expensesQuery->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        // 3. Cash Position (Tresorerie)
        $cashRegisters = CashRegister::where('is_active', true)->get();
        $cashAvailable = (float) $cashRegisters->sum('balance');

        // 4. Quotations & Conversion
        $quotationsQuery = Quotation::query();
        if ($startDate && $endDate) {
            $quotationsQuery->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                    ->orWhere(fn ($sub) => $sub->whereDate('date', '>=', $startDate->toDateString())->whereDate('date', '<=', $endDate->toDateString()));
            });
        }
        if ($selectedDomain) {
            $quotationsQuery->where('domain_id', $selectedDomain->id);
        }
        $totalQuotations = $quotationsQuery->count();
        $acceptedQuotations = (clone $quotationsQuery)->whereIn('status', ['validé', 'valide', 'converti', 'accepte'])->count();
        $conversionRate = $totalQuotations > 0 ? round(($acceptedQuotations / $totalQuotations) * 100, 1) : 0;

        // 5. Stock valuation & Alerts
        $latestMovements = StockMovement::select(DB::raw('MAX(id) as id'))
            ->groupBy('warehouse_id', 'product_id')
            ->pluck('id');

        $stockValueQuery = StockMovement::join('products', 'stock_movements.product_id', '=', 'products.id')
            ->whereIn('stock_movements.id', $latestMovements);

        if ($selectedDomain) {
            $stockValueQuery->where('products.domain_id', $selectedDomain->id);
        }

        $stockValue = (float) $stockValueQuery->selectRaw('SUM(stock_movements.stock_after * products.purchase_price) as val')->value('val') ?? 0;

        $stockAlertsQuery = StockAlert::where('status', 'active');
        if ($selectedDomain) {
            $stockAlertsQuery->whereHas('warehouse', fn ($w) => $w->where('domain_id', $selectedDomain->id));
        }
        $lowStockCount = $stockAlertsQuery->count();

        // 6. Receivables & Overdue Invoices
        $invoicesQuery = Invoice::query()->whereIn('status', ['non_payee', 'partielle']);
        if ($selectedDomain) {
            $invoicesQuery->where('domain_id', $selectedDomain->id);
        }
        $allUnpaid = $invoicesQuery->get();
        $totalReceivables = (float) $allUnpaid->sum(fn ($inv) => $inv->total - $inv->paid_amount);

        $overdueInvoices = $allUnpaid->filter(fn ($inv) => $inv->due_date && $inv->due_date < Carbon::now()->toDateString());
        $overdueAmount = (float) $overdueInvoices->sum(fn ($inv) => $inv->total - $inv->paid_amount);

        // Top 5 debtor customers
        $topDebtors = Customer::with('invoices')->get()->map(function ($c) {
            $balance = (float) $c->invoices->whereIn('status', ['non_payee', 'partielle'])->sum(fn ($i) => $i->total - $i->paid_amount);

            return [
                'id' => $c->id,
                'company_name' => $c->company,
                'first_name' => $c->name,
                'last_name' => '',
                'phone' => $c->phone,
                'balance' => $balance,
            ];
        })->filter(fn ($c) => $c['balance'] > 0)->sortByDesc('balance')->take(5)->values();

        // 7. Operations in progress
        $activeProjectsCount = TechProject::whereIn('status', ['nouveau', 'en_cours', 'en_recette'])
            ->when($selectedDomain, fn ($q) => $q->where('domain_id', $selectedDomain->id))
            ->count();

        $today = Carbon::now()->toDateString();
        $upcomingEventsCount = Event::where(function ($q) use ($today) {
            $q->where('date', '>=', $today)
                ->orWhere('end_date', '>=', $today);
        })
            ->when($selectedDomain, fn ($q) => $q->where('domain_id', $selectedDomain->id))
            ->count();

        // 8. Smart Alerts
        $alertsQuery = SmartAlert::query()->where('status', 'active');
        if ($selectedDomain) {
            $alertsQuery->where(function ($q) use ($selectedDomain) {
                $q->where('domain_id', $selectedDomain->id)->orWhereNull('domain_id');
            });
        }
        $recentAlerts = $alertsQuery->orderByRaw("CASE 
            WHEN priority = 'urgente' THEN 1 
            WHEN priority = 'haute' THEN 2 
            WHEN priority = 'moyenne' THEN 3 
            ELSE 4 END")
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        return [
            'period' => $period,
            'selectedDomain' => $selectedDomain,
            'domains' => $domains,
            'totalRevenue' => $totalRevenue,
            'domainRevenues' => $domainRevenues,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'profitMargin' => $profitMargin,
            'cashAvailable' => $cashAvailable,
            'ordersCount' => $ordersCount,
            'totalQuotations' => $totalQuotations,
            'conversionRate' => $conversionRate,
            'stockValue' => $stockValue,
            'lowStockCount' => $lowStockCount,
            'totalReceivables' => $totalReceivables,
            'overdueAmount' => $overdueAmount,
            'overdueCount' => $overdueInvoices->count(),
            'topDebtors' => $topDebtors,
            'activeProjectsCount' => $activeProjectsCount,
            'upcomingEventsCount' => $upcomingEventsCount,
            'alerts' => $recentAlerts,
            'alertsCount' => $recentAlerts->count(),
        ];
    }

    /**
     * Resolve date range from period keyword.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    protected function getDateRange(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'quarter' => [$now->copy()->firstOfQuarter()->startOfDay(), $now->copy()->lastOfQuarter()->endOfDay()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'all' => [null, null],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }
}
