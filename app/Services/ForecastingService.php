<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ForecastingService
{
    /**
     * Compute full predictive analytics and forecasting data.
     *
     * @return array<string, mixed>
     */
    public function getAnalyticsData(): array
    {
        $monthlyHistorical = $this->getMonthlySalesHistory(6);
        $forecastSales = $this->projectFutureSales($monthlyHistorical, 3);
        $stockVelocityAlerts = $this->getStockVelocityForecast();
        $expenseTrends = $this->getExpenseTrends(6);
        $operationalScores = $this->getOperationalPerformanceScores();

        return [
            'history' => $monthlyHistorical,
            'forecast' => $forecastSales,
            'stockAlerts' => $stockVelocityAlerts,
            'expenseTrends' => $expenseTrends,
            'operationalScores' => $operationalScores,
            'marginOfError' => 8, // ±8% uncertainty margin
        ];
    }

    /**
     * Retrieve monthly sales history for the past N months.
     *
     * @return array<int, array{month: string, label: string, revenue: float, count: int}>
     */
    protected function getMonthlySalesHistory(int $months = 6): array
    {
        $history = [];
        $now = Carbon::now();

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $orders = Order::where('status', '!=', 'annulee')
                ->whereBetween('created_at', [$start, $end]);

            $history[] = [
                'month' => $date->format('Y-m'),
                'label' => $date->translatedFormat('M Y'),
                'revenue' => (float) $orders->sum('total'),
                'count' => $orders->count(),
            ];
        }

        return $history;
    }

    /**
     * Project revenue for future months based on simple linear trend.
     *
     * @param  array<int, array{month: string, label: string, revenue: float, count: int}>  $history
     * @return array<int, array{month: string, label: string, projected: float, min: float, max: float}>
     */
    protected function projectFutureSales(array $history, int $futureMonths = 3): array
    {
        $n = count($history);
        if ($n === 0) {
            return [];
        }

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($history as $idx => $item) {
            $x = $idx + 1;
            $y = $item['revenue'];
            $sumX += $x;
            $sumY += $y;
            $sumXY += ($x * $y);
            $sumX2 += ($x * $x);
        }

        $denominator = ($n * $sumX2) - ($sumX * $sumX);
        if ($denominator != 0) {
            $slope = (($n * $sumXY) - ($sumX * $sumY)) / $denominator;
            $intercept = ($sumY - ($slope * $sumX)) / $n;
        } else {
            $slope = 0;
            $intercept = $n > 0 ? ($sumY / $n) : 0;
        }

        $now = Carbon::now();
        $projections = [];

        for ($j = 1; $j <= $futureMonths; $j++) {
            $targetX = $n + $j;
            $projectedValue = max(0, ($slope * $targetX) + $intercept);
            $targetDate = $now->copy()->addMonths($j);

            $minVal = round($projectedValue * 0.92);
            $maxVal = round($projectedValue * 1.08);

            $projections[] = [
                'month' => $targetDate->format('Y-m'),
                'label' => $targetDate->translatedFormat('M Y'),
                'projected' => round($projectedValue),
                'min' => $minVal,
                'max' => $maxVal,
            ];
        }

        return $projections;
    }

    /**
     * Identify products likely to stock out within 30 days based on 30-day velocity.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getStockVelocityForecast(): array
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Calculate sales velocity for products in the last 30 days
        $recentItemSales = OrderItem::whereHas('order', function ($q) use ($thirtyDaysAgo) {
            $q->where('status', '!=', 'annulee')
                ->where('created_at', '>=', $thirtyDaysAgo);
        })
            ->selectRaw('product_id, SUM(quantity) as sold_qty')
            ->groupBy('product_id')
            ->pluck('sold_qty', 'product_id');

        $latestMovements = StockMovement::select(DB::raw('MAX(id) as id'))
            ->groupBy('warehouse_id', 'product_id')
            ->pluck('id');

        $productStocks = StockMovement::whereIn('id', $latestMovements)
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(stock_after) as current_qty')
            ->pluck('current_qty', 'product_id');

        $alerts = [];
        $products = Product::where('is_active', true)->with('domain')->get();

        foreach ($products as $prod) {
            $currentStock = (int) ($productStocks[$prod->id] ?? 0);
            $sold30Days = (float) ($recentItemSales[$prod->id] ?? 0);
            $dailyVelocity = $sold30Days / 30;

            if ($dailyVelocity > 0 && $currentStock > 0) {
                $daysRemaining = round($currentStock / $dailyVelocity);
                if ($daysRemaining <= 30) {
                    $alerts[] = [
                        'id' => $prod->id,
                        'name' => $prod->name,
                        'domain' => $prod->domain?->name,
                        'current_stock' => $currentStock,
                        'daily_velocity' => round($dailyVelocity, 2),
                        'days_until_stockout' => $daysRemaining,
                        'suggested_reorder' => round($dailyVelocity * 45),
                    ];
                }
            }
        }

        usort($alerts, fn ($a, $b) => $a['days_until_stockout'] <=> $b['days_until_stockout']);

        return array_slice($alerts, 0, 8);
    }

    /**
     * Compute expense trends and 30-day projections.
     *
     * @return array{historical_avg: float, projected_next_month: float}
     */
    protected function getExpenseTrends(int $months = 6): array
    {
        $start = Carbon::now()->subMonths($months)->startOfMonth();
        $totalExpenses = (float) Expense::where('date', '>=', $start->toDateString())
            ->whereIn('status', ['validee', 'payé', 'payee'])
            ->sum('amount');

        $avgMonthly = $months > 0 ? ($totalExpenses / $months) : 0;
        $projectedNext = round($avgMonthly * 1.03);

        return [
            'historical_avg' => round($avgMonthly),
            'projected_next_month' => $projectedNext,
        ];
    }

    /**
     * Compute objective operational performance scores across departments.
     *
     * @return array<string, array{name: string, metric: string, value: string, rating: string}>
     */
    protected function getOperationalPerformanceScores(): array
    {
        $domains = Domain::all();
        $scores = [];

        foreach ($domains as $domain) {
            $ordersCount = Order::where('domain_id', $domain->id)->where('status', '!=', 'annulee')->count();
            $completedOrders = Order::where('domain_id', $domain->id)->where('status', 'livree')->count();
            $rate = $ordersCount > 0 ? round(($completedOrders / $ordersCount) * 100) : 100;

            $rating = 'Excellent';
            if ($rate < 70) {
                $rating = 'Vigilance';
            } elseif ($rate < 85) {
                $rating = 'Bon';
            }

            $scores[] = [
                'name' => $domain->name,
                'metric' => "Taux d'exécution commandes ({$completedOrders}/{$ordersCount})",
                'value' => "{$rate}%",
                'rating' => $rating,
            ];
        }

        return $scores;
    }
}
