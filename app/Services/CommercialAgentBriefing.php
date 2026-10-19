<?php

namespace App\Services;

use App\Models\CommercialAppointment;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Prospect;
use App\Models\Quotation;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Briefing de l'agent commercial « Ivo » : ce qu'il faut savoir aujourd'hui sur le volet commerce.
 */
class CommercialAgentBriefing
{
    /**
     * @var list<string>
     */
    private const CANCELLED_ORDER_STATUSES = ['annulée', 'annulee'];

    /**
     * @return array{
     *     greeting: string,
     *     mood: string,
     *     messages: list<array{tone: string, icon: string, text: string, action: ?array{label: string, url: string}}>,
     *     week: list<array{label: string, amount: float, is_today: bool}>,
     *     top_products: list<array{name: string, quantity: float, revenue: float}>
     * }
     */
    public function build(string $userName, ?CarbonInterface $now = null): array
    {
        $now = Carbon::instance($now ?? now());
        $messages = [];

        $today = $this->collected($now->copy()->startOfDay(), $now->copy()->endOfDay());
        $yesterday = $this->collected($now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay());
        $messages[] = [
            'tone' => $today > 0 && $today >= $yesterday ? 'good' : 'info',
            'icon' => 'cash',
            'text' => "Aujourd'hui, vous avez encaissé **{$this->money($today)}**".$this->comparison($today, $yesterday, 'hier').'.',
            'action' => ['label' => 'Ouvrir la caisse POS', 'url' => route('commercial.pos.index')],
        ];

        $month = $this->collected($now->copy()->startOfMonth(), $now->copy()->endOfDay());
        $previousMonth = $this->collected($now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfDay());
        $messages[] = [
            'tone' => $month >= $previousMonth ? 'good' : 'warn',
            'icon' => 'trend',
            'text' => "Depuis le début du mois : **{$this->money($month)}** encaissés".$this->comparison($month, $previousMonth, 'la même période du mois dernier').'.',
            'action' => null,
        ];

        $topProducts = $this->topProducts($now->copy()->startOfMonth(), $now->copy()->endOfDay());
        if ($topProducts !== []) {
            $messages[] = [
                'tone' => 'info',
                'icon' => 'star',
                'text' => "Produit star du mois : **{$topProducts[0]['name']}** (".number_format($topProducts[0]['quantity'], 0, ',', ' ').' vendu(s)).',
                'action' => ['label' => 'Voir le catalogue', 'url' => route('commercial.products.index')],
            ];
        }

        $topCustomer = $this->topCustomer($now->copy()->startOfMonth(), $now->copy()->endOfDay());
        if ($topCustomer !== null) {
            $messages[] = [
                'tone' => 'good',
                'icon' => 'user',
                'text' => "Meilleur client du mois : **{$topCustomer['name']}** avec **{$this->money($topCustomer['revenue'])}** d'achats.",
                'action' => ['label' => 'Fiche client', 'url' => route('commercial.customers.show', $topCustomer['id'])],
            ];
        }

        $unpaid = Invoice::whereIn('status', ['non_payee', 'partielle'])->get(['id', 'total', 'paid_amount', 'due_date']);
        $unpaidAmount = (float) $unpaid->sum(fn (Invoice $invoice) => (float) $invoice->total - (float) $invoice->paid_amount);
        $overdue = $unpaid->filter(fn (Invoice $invoice) => $invoice->due_date !== null && $invoice->due_date->lt($now->copy()->startOfDay()))->count();
        if ($unpaidAmount > 0) {
            $messages[] = [
                'tone' => $overdue > 0 ? 'alert' : 'warn',
                'icon' => 'alert',
                'text' => "**{$this->money($unpaidAmount)}** restent à recouvrer sur **{$unpaid->count()} facture(s)**".($overdue > 0 ? ", dont **{$overdue} en retard**" : '').'.',
                'action' => ['label' => 'Voir les factures', 'url' => route('commercial.invoices.index')],
            ];
        }

        $pendingQuotations = Quotation::whereIn('status', ['envoye', 'envoyé', 'brouillon'])->get(['id', 'total', 'valid_until']);
        if ($pendingQuotations->isNotEmpty()) {
            $expiring = $pendingQuotations->filter(fn (Quotation $quotation) => $quotation->valid_until !== null
                && $quotation->valid_until->between($now->copy()->startOfDay(), $now->copy()->addDays(7)->endOfDay()))->count();
            $messages[] = [
                'tone' => $expiring > 0 ? 'warn' : 'info',
                'icon' => 'doc',
                'text' => "**{$pendingQuotations->count()} devis** en attente pour **{$this->money((float) $pendingQuotations->sum('total'))}**".($expiring > 0 ? ", dont **{$expiring} expirent cette semaine** : relancez-les" : '').'.',
                'action' => ['label' => 'Voir les devis', 'url' => route('commercial.quotations.index')],
            ];
        }

        $activeOrders = Order::whereIn('status', ['en_attente', 'confirmee', 'confirmée', 'en_cours'])->count();
        if ($activeOrders > 0) {
            $messages[] = [
                'tone' => 'info',
                'icon' => 'box',
                'text' => "**{$activeOrders} commande(s)** client sont en cours de traitement.",
                'action' => ['label' => 'Voir les commandes', 'url' => route('commercial.orders.index')],
            ];
        }

        $lowStock = Product::active()->with('warehouseStocks')->get()
            ->filter(fn (Product $product) => (int) $product->min_stock > 0 && $product->current_stock <= (int) $product->min_stock);
        if ($lowStock->isNotEmpty()) {
            $outOfStock = $lowStock->filter(fn (Product $product) => $product->current_stock <= 0)->count();
            $messages[] = [
                'tone' => $outOfStock > 0 ? 'alert' : 'warn',
                'icon' => 'stock',
                'text' => "**{$lowStock->count()} produit(s)** sous le seuil minimum".($outOfStock > 0 ? ", dont **{$outOfStock} en rupture**" : '').' : pensez au réapprovisionnement.',
                'action' => ['label' => 'Voir les produits', 'url' => route('commercial.products.index')],
            ];
        }

        $appointments = CommercialAppointment::whereDate('date', $now->toDateString())
            ->whereNotIn('status', ['annulé', 'annule', 'effectué', 'effectue'])
            ->orderBy('time')
            ->get(['id', 'title', 'time']);
        if ($appointments->isNotEmpty()) {
            $first = $appointments->first();
            $messages[] = [
                'tone' => 'info',
                'icon' => 'calendar',
                'text' => "**{$appointments->count()} rendez-vous** aujourd'hui. Prochain : **{$first->title}**".($first->time ? " à {$first->time}" : '').'.',
                'action' => ['label' => 'Agenda commercial', 'url' => route('commercial.appointments.index')],
            ];
        }

        $followUps = Prospect::whereNotIn('stage', ['gagne', 'perdu'])
            ->whereDate('next_follow_up', '<=', $now->toDateString())
            ->count();
        if ($followUps > 0) {
            $messages[] = [
                'tone' => 'warn',
                'icon' => 'phone',
                'text' => "**{$followUps} prospect(s)** sont à relancer aujourd'hui ou en retard de relance.",
                'action' => ['label' => 'Pipeline prospects', 'url' => route('commercial.prospects.index')],
            ];
        }

        $alerts = collect($messages)->whereIn('tone', ['alert', 'warn'])->count();

        return [
            'greeting' => $this->greeting($now).' '.$userName.' ! Je suis Ivo, votre agent commercial. Voici votre point du jour.',
            'mood' => $alerts === 0 ? 'happy' : ($alerts >= 3 ? 'focused' : 'neutral'),
            'messages' => $messages,
            'week' => $this->lastSevenDays($now),
            'top_products' => $topProducts,
        ];
    }

    /**
     * Encaissements valides + factures réglées sans paiement rattaché, comme le tableau de bord commercial.
     */
    private function collected(CarbonInterface $start, CarbonInterface $end): float
    {
        $payments = (float) Payment::where('status', 'valide')
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->sum('amount');

        $untracked = (float) Invoice::where('status', '!=', 'annulee')
            ->whereDoesntHave('payments')
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->sum('paid_amount');

        return $payments + $untracked;
    }

    /**
     * @return list<array{label: string, amount: float, is_today: bool}>
     */
    private function lastSevenDays(Carbon $now): array
    {
        return collect(range(6, 0))->map(function (int $daysAgo) use ($now) {
            $day = $now->copy()->subDays($daysAgo);

            return [
                'label' => ucfirst($day->locale('fr')->translatedFormat('D')),
                'amount' => $this->collected($day->copy()->startOfDay(), $day->copy()->endOfDay()),
                'is_today' => $daysAgo === 0,
            ];
        })->all();
    }

    /**
     * @return list<array{name: string, quantity: float, revenue: float}>
     */
    private function topProducts(CarbonInterface $start, CarbonInterface $end): array
    {
        $rows = OrderItem::query()
            ->whereIn('order_id', $this->ordersBetween($start, $end)->select('id'))
            ->selectRaw('COALESCE(product_id, 0) as product_key, MAX(description) as label, SUM(quantity) as quantity, SUM(total) as revenue')
            ->groupBy('product_key')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get();

        $names = Product::whereIn('id', $rows->pluck('product_key'))->pluck('name', 'id');

        return $rows->map(fn ($row) => [
            'name' => (string) ($names[$row->product_key] ?? $row->label ?? 'Article'),
            'quantity' => (float) $row->quantity,
            'revenue' => (float) $row->revenue,
        ])->values()->all();
    }

    /**
     * @return array{id: int, name: string, revenue: float}|null
     */
    private function topCustomer(CarbonInterface $start, CarbonInterface $end): ?array
    {
        $row = $this->ordersBetween($start, $end)
            ->whereHas('customer', fn ($query) => $query->where('code', '!=', 'CLI-PASSAGER'))
            ->selectRaw('customer_id, SUM(total) as revenue')
            ->groupBy('customer_id')
            ->orderByDesc('revenue')
            ->first();

        $customer = $row ? Customer::find($row->customer_id) : null;

        return $customer ? ['id' => $customer->id, 'name' => $customer->name, 'revenue' => (float) $row->revenue] : null;
    }

    /**
     * @return Builder<Order>
     */
    private function ordersBetween(CarbonInterface $start, CarbonInterface $end): Builder
    {
        return Order::query()
            ->whereNotIn('status', self::CANCELLED_ORDER_STATUSES)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString());
    }

    private function comparison(float $current, float $previous, string $previousLabel): string
    {
        if ($previous <= 0) {
            return '';
        }

        $trend = round(($current - $previous) / $previous * 100, 1);

        return match (true) {
            $trend > 0 => " (**+{$trend} %** par rapport à {$previousLabel})",
            $trend < 0 => " (**{$trend} %** par rapport à {$previousLabel})",
            default => " (stable par rapport à {$previousLabel})",
        };
    }

    private function greeting(CarbonInterface $now): string
    {
        return match (true) {
            $now->hour < 12 => 'Bonjour',
            $now->hour < 18 => 'Bon après-midi',
            default => 'Bonsoir',
        };
    }

    private function money(float $amount): string
    {
        return number_format($amount, 0, ',', ' ').' FCFA';
    }
}
