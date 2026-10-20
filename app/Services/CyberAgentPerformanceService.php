<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Order;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Performance mensuelle des agents du cyber : clients servis, montant généré (agent qui réalise)
 * et montant encaissé (caissier), classement et statut par rapport à la moyenne des agents.
 */
class CyberAgentPerformanceService
{
    /**
     * @var list<string>
     */
    private const CANCELLED_ORDER_STATUSES = ['annulée', 'annulee'];

    /**
     * @return array{
     *     totals: array{revenue: float, clients: int, days: int, average_ticket: float, threshold: float, unassigned_revenue: float, unassigned_clients: int},
     *     agents: list<array{employee_id: ?int, name: string, position: ?string, clients: int, days: int, clients_per_day: float, revenue: float, revenue_per_day: float, share: float, collected: float, rank: ?int, status: string}>,
     *     daily: array{days: list<int>, agents: list<array{employee_id: int, name: string}>, counts: array<int, array<int, int>>, totals: array<int, int>}
     * }
     */
    public function forMonth(CarbonInterface $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $orders = Order::query()
            ->where('reference', 'like', 'CYB-%')
            ->whereNotIn('status', self::CANCELLED_ORDER_STATUSES)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->get(['id', 'date', 'total', 'user_id', 'performed_by_employee_id']);

        $revenue = (float) $orders->sum('total');
        $byAgent = $orders->whereNotNull('performed_by_employee_id')->groupBy('performed_by_employee_id');
        $byCashier = $orders->groupBy('user_id');

        $employees = Employee::query()
            ->where(fn ($query) => $query->where('status', 'actif')->orWhereIn('id', $byAgent->keys()))
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'position', 'user_id']);

        $rows = $employees->map(function (Employee $employee) use ($byAgent, $byCashier, $revenue) {
            $agentOrders = $byAgent->get($employee->id, collect());
            $agentRevenue = (float) $agentOrders->sum('total');
            $days = $agentOrders->map(fn (Order $order) => $order->date->toDateString())->unique()->count();

            return [
                'employee_id' => $employee->id,
                'name' => $employee->full_name,
                'position' => $employee->position,
                'clients' => $agentOrders->count(),
                'days' => $days,
                'clients_per_day' => $days > 0 ? round($agentOrders->count() / $days, 1) : 0.0,
                'revenue' => $agentRevenue,
                'revenue_per_day' => $days > 0 ? round($agentRevenue / $days) : 0.0,
                'share' => $revenue > 0 ? round($agentRevenue / $revenue * 100, 1) : 0.0,
                'collected' => $employee->user_id ? (float) $byCashier->get($employee->user_id, collect())->sum('total') : 0.0,
                'rank' => null,
                'status' => '',
            ];
        });

        $producers = $rows->filter(fn (array $row) => $row['revenue'] > 0);
        $threshold = $producers->isNotEmpty() ? round($producers->avg('revenue')) : 0.0;
        $ranking = $producers->sortByDesc('revenue')->pluck('employee_id')->values()->flip();

        $agents = $rows->map(fn (array $row) => [
            ...$row,
            'rank' => isset($ranking[$row['employee_id']]) ? $ranking[$row['employee_id']] + 1 : null,
            'status' => $this->status($row, $threshold),
        ])
            ->sortBy(fn (array $row) => [$row['rank'] ?? PHP_INT_MAX, -$row['collected'], $row['name']])
            ->values()
            ->all();

        $unassigned = $orders->whereNull('performed_by_employee_id');

        return [
            'totals' => [
                'revenue' => $revenue,
                'clients' => $orders->count(),
                'days' => $orders->map(fn (Order $order) => $order->date->toDateString())->unique()->count(),
                'average_ticket' => $orders->isNotEmpty() ? round($revenue / $orders->count()) : 0.0,
                'threshold' => $threshold,
                'unassigned_revenue' => (float) $unassigned->sum('total'),
                'unassigned_clients' => $unassigned->count(),
            ],
            'agents' => $agents,
            'daily' => $this->daily($byAgent, $employees, $start),
        ];
    }

    /**
     * @param  array{revenue: float, collected: float}  $row
     */
    private function status(array $row, float $threshold): string
    {
        return match (true) {
            $row['revenue'] <= 0 && $row['collected'] <= 0 => 'Inactif',
            $row['revenue'] <= 0 => 'Caisse',
            $row['revenue'] >= $threshold => 'Performant',
            default => 'À améliorer',
        };
    }

    /**
     * Nombre de clients servis par agent et par jour du mois.
     *
     * @param  Collection<int|string, Collection<int, Order>>  $byAgent
     * @param  Collection<int, Employee>  $employees
     * @return array{days: list<int>, agents: list<array{employee_id: int, name: string}>, counts: array<int, array<int, int>>, totals: array<int, int>}
     */
    private function daily(Collection $byAgent, Collection $employees, CarbonInterface $start): array
    {
        $days = range(1, $start->daysInMonth);
        $agents = $employees->filter(fn (Employee $employee) => $byAgent->has($employee->id))
            ->map(fn (Employee $employee) => ['employee_id' => $employee->id, 'name' => $employee->full_name])
            ->values()
            ->all();

        $counts = [];
        $totals = array_fill_keys($days, 0);

        foreach ($agents as $agent) {
            foreach ($byAgent[$agent['employee_id']] as $order) {
                $day = (int) $order->date->day;
                $counts[$day][$agent['employee_id']] = ($counts[$day][$agent['employee_id']] ?? 0) + 1;
                $totals[$day]++;
            }
        }

        return ['days' => $days, 'agents' => $agents, 'counts' => $counts, 'totals' => $totals];
    }
}
