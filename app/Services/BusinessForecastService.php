<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Throwable;

/**
 * Prévisions des ventes et des ruptures de stock, calculées par le script Python
 * resources/python/forecast.py (repli en PHP si Python est indisponible) et mémorisées par espace.
 */
class BusinessForecastService
{
    public const SETTING_KEY = 'ai_forecast';

    private const HISTORY_DAYS = 180;

    private const HORIZON_DAYS = 30;

    /**
     * @var list<string>
     */
    private const CANCELLED_ORDER_STATUSES = ['annulée', 'annulee'];

    /**
     * Dernière prévision de l'espace courant, recalculée si absente ou datant de plus d'un jour.
     *
     * @return array{engine: string, generated_at: string, sales: array<string, mixed>, stockouts: list<array<string, mixed>>}
     */
    public function latest(): array
    {
        $forecast = Setting::get(self::SETTING_KEY);

        if (! is_array($forecast) || Carbon::parse($forecast['generated_at'] ?? '2000-01-01')->lt(now()->subHours(26))) {
            $forecast = $this->refresh();
        }

        return $forecast;
    }

    /**
     * @return array{engine: string, generated_at: string, sales: array<string, mixed>, stockouts: list<array<string, mixed>>}
     */
    public function refresh(?CarbonInterface $today = null): array
    {
        $payload = $this->payload(Carbon::instance($today ?? now()));
        $forecast = ($this->runPython($payload) ?? $this->fallback($payload)) + ['generated_at' => now()->toIso8601String()];

        Setting::set(self::SETTING_KEY, $forecast, 'ai', 'json');

        return $forecast;
    }

    /**
     * Historique envoyé au script : ventes journalières par pôle et sorties de chaque produit.
     *
     * @return array{today: string, horizon: int, history_days: int, sales: list<array{date: string, domain: ?string, amount: float}>, products: list<array<string, mixed>>}
     */
    public function payload(Carbon $today): array
    {
        $from = $today->copy()->subDays(self::HISTORY_DAYS - 1)->toDateString();
        $orders = Order::query()
            ->with('domain:id,code')
            ->whereNotIn('status', self::CANCELLED_ORDER_STATUSES)
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $today->toDateString())
            ->get(['id', 'date', 'domain_id', 'total']);

        $sales = $orders
            ->groupBy(fn (Order $order) => $order->date->toDateString().'|'.($order->domain?->code ?? ''))
            ->map(function ($group, string $key) {
                [$date, $domain] = explode('|', $key);

                return ['date' => $date, 'domain' => $domain !== '' ? $domain : null, 'amount' => (float) $group->sum('total')];
            })
            ->values()
            ->all();

        $orderDates = $orders->filter(fn (Order $order) => $order->date->gte($today->copy()->subDays(59)->startOfDay()))
            ->mapWithKeys(fn (Order $order) => [$order->id => $order->date->toDateString()]);

        $quantities = OrderItem::query()
            ->whereIn('order_id', $orderDates->keys())
            ->whereNotNull('product_id')
            ->get(['order_id', 'product_id', 'quantity'])
            ->groupBy('product_id');

        $products = Product::active()
            ->with('warehouseStocks')
            ->whereIn('id', $quantities->keys())
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'stock' => $product->current_stock,
                'min_stock' => (int) $product->min_stock,
                'sales' => $quantities[$product->id]
                    ->groupBy(fn (OrderItem $item) => $orderDates[$item->order_id])
                    ->map(fn ($items, string $date) => ['date' => $date, 'qty' => (float) $items->sum('quantity')])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        return [
            'today' => $today->toDateString(),
            'horizon' => self::HORIZON_DAYS,
            'history_days' => self::HISTORY_DAYS,
            'sales' => $sales,
            'products' => $products,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{engine: string, sales: array<string, mixed>, stockouts: list<array<string, mixed>>}|null
     */
    private function runPython(array $payload): ?array
    {
        $binary = config('services.python.binary');

        if (! $binary) {
            return null;
        }

        try {
            $result = Process::timeout((int) config('services.python.timeout', 120))
                ->input(json_encode($payload))
                ->run([$binary, resource_path('python/forecast.py')]);

            $forecast = $result->successful() ? json_decode($result->output(), true) : null;

            if (! is_array($forecast) || ! isset($forecast['sales'], $forecast['stockouts'])) {
                Log::warning('Prévisions IA : le script Python a échoué, calcul de secours en PHP.', ['error' => mb_substr($result->errorOutput(), -1000)]);

                return null;
            }

            return $forecast;
        } catch (Throwable $exception) {
            Log::warning('Prévisions IA : Python indisponible, calcul de secours en PHP.', ['error' => $exception->getMessage()]);

            return null;
        }
    }

    /**
     * Calcul simplifié (moyennes récentes) quand Python n'est pas disponible.
     *
     * @param  array<string, mixed>  $payload
     * @return array{engine: string, sales: array<string, mixed>, stockouts: list<array<string, mixed>>}
     */
    private function fallback(array $payload): array
    {
        $today = Carbon::parse($payload['today']);
        $byDate = collect($payload['sales'])->groupBy('date')->map(fn ($rows) => (float) $rows->sum('amount'));
        $sumBetween = fn (int $fromDaysAgo, int $toDaysAgo) => $byDate
            ->filter(fn (float $amount, string $date) => Carbon::parse($date)->between($today->copy()->subDays($fromDaysAgo)->startOfDay(), $today->copy()->subDays($toDaysAgo)->endOfDay()))
            ->sum();

        $last30 = (float) $sumBetween(29, 0);
        $dailyRate = (float) $sumBetween(27, 0) / 28;
        $next30 = $dailyRate * 30;

        $stockouts = collect($payload['products'])->map(function (array $product) use ($today) {
            $sales = collect($product['sales']);
            $recent = (float) $sales->filter(fn (array $row) => Carbon::parse($row['date'])->gte($today->copy()->subDays(13)->startOfDay()))->sum('qty') / 14;
            $rate = 0.6 * $recent + 0.4 * ((float) $sales->sum('qty') / 60);

            if ($rate <= 0) {
                return null;
            }

            $daysLeft = max((float) $product['stock'], 0) / $rate;

            return [
                'id' => $product['id'],
                'name' => $product['name'],
                'stock' => (float) $product['stock'],
                'daily_rate' => round($rate, 2),
                'days_left' => round($daysLeft, 1),
                'stockout_date' => $today->copy()->addDays((int) floor($daysLeft))->toDateString(),
                'reorder_qty' => max((int) ceil($rate * 30 + $product['min_stock'] - $product['stock']), 0),
            ];
        })->filter()->sortBy('days_left')->values()->all();

        return [
            'engine' => 'php',
            'sales' => [
                'method' => 'moyenne',
                'next_7_days' => round($dailyRate * 7),
                'next_30_days' => round($next30),
                'last_30_days' => round($last30),
                'trend_pct' => $last30 > 0 ? round(($next30 - $last30) / $last30 * 100, 1) : null,
                'daily' => [],
                'by_domain' => [],
            ],
            'stockouts' => $stockouts,
        ];
    }
}
