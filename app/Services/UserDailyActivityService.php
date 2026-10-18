<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\CashRegisterSession;
use App\Models\CyberPrintRequest;
use App\Models\DailyTaskSheet;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Journée d'un utilisateur : pointage, tâches, ventes, encaissements, stock et activité.
 */
class UserDailyActivityService
{
    /**
     * @return array{
     *     attendance: ?Attendance,
     *     tasks: array{total: int, done: int, items: list<array{title: string, done: bool}>},
     *     sales: array{invoices: int, invoiced: float, orders: int, ordered: float, quotations: int, quoted: float},
     *     payments: array{count: int, total: float, by_method: array<string, float>},
     *     expenses: array{count: int, total: float},
     *     cash_sessions: Collection<int, CashRegisterSession>,
     *     stock: array<string, int>,
     *     cyber_deposits: int,
     *     timeline: Collection<int, ActivityLog>
     * }
     */
    public function forDay(User $user, CarbonInterface $date): array
    {
        $day = $date->toDateString();
        $employee = $user->employee;

        $invoices = Invoice::where('user_id', $user->id)->whereDate('date', $day)->get(['id', 'total']);
        $orders = Order::where('user_id', $user->id)->whereDate('date', $day)->get(['id', 'total']);
        $quotations = Quotation::where('user_id', $user->id)->whereDate('date', $day)->get(['id', 'total']);
        $payments = Payment::where('user_id', $user->id)->whereDate('date', $day)->get(['id', 'amount', 'method']);
        $expenses = Expense::where('user_id', $user->id)->whereDate('date', $day)->get(['id', 'amount']);

        return [
            'attendance' => $employee ? Attendance::where('employee_id', $employee->id)->whereDate('date', $day)->first() : null,
            'tasks' => $this->tasks($employee ? DailyTaskSheet::where('employee_id', $employee->id)->whereDate('date', $day)->first() : null),
            'sales' => [
                'invoices' => $invoices->count(),
                'invoiced' => (float) $invoices->sum('total'),
                'orders' => $orders->count(),
                'ordered' => (float) $orders->sum('total'),
                'quotations' => $quotations->count(),
                'quoted' => (float) $quotations->sum('total'),
            ],
            'payments' => [
                'count' => $payments->count(),
                'total' => (float) $payments->sum('amount'),
                'by_method' => $payments->groupBy('method')->map(fn (Collection $group) => (float) $group->sum('amount'))->all(),
            ],
            'expenses' => ['count' => $expenses->count(), 'total' => (float) $expenses->sum('amount')],
            'cash_sessions' => CashRegisterSession::where('user_id', $user->id)->whereDate('opened_at', $day)->get(),
            'stock' => StockMovement::where('user_id', $user->id)->whereDate('date', $day)->get(['type'])->countBy('type')->all(),
            'cyber_deposits' => CyberPrintRequest::where('handled_by', $user->id)->whereDate('updated_at', $day)->count(),
            'timeline' => ActivityLog::where('user_id', $user->id)->whereDate('created_at', $day)->latest('created_at')->limit(60)->get(),
        ];
    }

    /**
     * @return array{total: int, done: int, items: list<array{title: string, done: bool}>}
     */
    private function tasks(?DailyTaskSheet $sheet): array
    {
        $items = collect($sheet?->tasks ?? [])->map(fn ($task) => [
            'title' => is_array($task) ? (string) ($task['title'] ?? '') : (string) $task,
            'done' => is_array($task) && ($task['status'] ?? null) === 'termine',
        ])->values()->all();

        return ['total' => count($items), 'done' => count(array_filter($items, fn ($item) => $item['done'])), 'items' => $items];
    }
}
