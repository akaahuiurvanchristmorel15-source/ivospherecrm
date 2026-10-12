<?php

namespace App\Services;

use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\TechProject;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Assistant de pilotage sans IA externe : comprend des questions en français
 * (intention, période, pôle, « top N », produit ou client cité) et interroge les données de l'ERP.
 */
class AiAssistantService
{
    /**
     * Statuts de commande exclus du chiffre d'affaires.
     *
     * @var list<string>
     */
    private const CANCELLED_ORDER_STATUSES = ['annulée', 'annulee'];

    /**
     * Mots-clés pondérés par intention (comparés à la question sans accents ni majuscules).
     *
     * @var array<string, array<string, int>>
     */
    private const INTENT_KEYWORDS = [
        'revenue' => ['chiffre d affaire' => 6, 'chiffre' => 3, 'ca' => 4, 'vente' => 3, 'ventes' => 3, 'vendu' => 2, 'revenu' => 3, 'revenus' => 3, 'recette' => 3, 'recettes' => 3, 'gagne' => 2],
        'top_products' => ['produit' => 2, 'produits' => 2, 'article' => 2, 'articles' => 2, 'plus vendu' => 5, 'plus vendus' => 5, 'meilleur' => 2, 'meilleurs' => 2, 'top' => 2, 'best' => 2],
        'top_customers' => ['meilleur client' => 6, 'meilleurs clients' => 6, 'top client' => 6, 'top clients' => 6, 'plus gros client' => 6, 'plus gros clients' => 6, 'clients fideles' => 4],
        'stock_low' => ['rupture' => 6, 'ruptures' => 6, 'stock faible' => 6, 'stock bas' => 6, 'reapprovisionner' => 5, 'reappro' => 5, 'alerte stock' => 6, 'epuise' => 5, 'epuises' => 5, 'manque' => 2, 'critique' => 3],
        'stock_lookup' => ['stock' => 3, 'combien de' => 2, 'quantite' => 3, 'reste' => 2, 'disponible' => 2, 'en stock' => 3],
        'receivables' => ['facture' => 3, 'factures' => 3, 'retard' => 4, 'impaye' => 6, 'impayes' => 6, 'impayee' => 6, 'impayees' => 6, 'creance' => 6, 'creances' => 6, 'doit' => 3, 'doivent' => 3, 'recouvrement' => 6, 'a encaisser' => 5],
        'inactive_customers' => ['inactif' => 6, 'inactifs' => 6, 'pas commande' => 6, 'plus commande' => 6, 'perdu' => 3, 'perdus' => 3, 'relancer' => 3, 'dormant' => 5, 'dormants' => 5],
        'customer_lookup' => ['client' => 2, 'fiche client' => 6, 'historique' => 3, 'achete' => 2],
        'cash' => ['tresorerie' => 6, 'caisse' => 5, 'caisses' => 5, 'solde' => 4, 'liquidite' => 5, 'argent' => 3, 'cash' => 4],
        'expenses' => ['depense' => 6, 'depenses' => 6, 'charge' => 4, 'charges' => 4, 'cout' => 3, 'couts' => 3, 'achat' => 2, 'achats' => 2, 'sorties d argent' => 6],
        'profit' => ['benefice' => 6, 'benefices' => 6, 'marge' => 5, 'resultat' => 5, 'rentable' => 5, 'rentabilite' => 6, 'profit' => 6, 'perte' => 4],
        'average_basket' => ['panier moyen' => 8, 'ticket moyen' => 8, 'moyenne par commande' => 6],
        'projects' => ['projet' => 5, 'projets' => 5, 'chantier' => 4, 'developpement' => 3, 'avancement' => 4],
        'overview' => ['resume' => 4, 'bilan' => 5, 'synthese' => 5, 'recapitulatif' => 5, 'point' => 2, 'situation' => 4, 'tableau de bord' => 5, 'comment va' => 5, 'comment vont' => 5, 'global' => 2],
    ];

    /**
     * Mots ignorés quand on cherche un nom de produit ou de client dans la question.
     *
     * @var list<string>
     */
    private const STOP_WORDS = [
        'le', 'la', 'les', 'l', 'un', 'une', 'des', 'du', 'de', 'd', 'en', 'au', 'aux', 'a', 'et', 'ou', 'est', 'sont', 'ce', 'cet', 'cette', 'ces',
        'quel', 'quelle', 'quels', 'quelles', 'combien', 'reste', 't', 'il', 'y', 'nous', 'on', 'avons', 'ai', 'pour', 'sur', 'dans', 'par', 'avec',
        'stock', 'stocks', 'quantite', 'disponible', 'disponibles', 'produit', 'produits', 'article', 'articles', 'client', 'clients', 'fiche',
        'historique', 'achete', 'donne', 'moi', 'montre', 'affiche', 'me', 'mon', 'ma', 'mes', 'notre', 'nos', 'qu', 'que', 'qui', 'quoi', 'svp',
        'stp', 'encore', 'actuellement', 'maintenant', 'aujourd', 'hui', 'chez', 'son', 'sa', 'ses', 'leur', 'leurs', 'commande', 'commandes',
    ];

    /**
     * @var array<string, int>
     */
    private const MONTHS = [
        'janvier' => 1, 'fevrier' => 2, 'mars' => 3, 'avril' => 4, 'mai' => 5, 'juin' => 6,
        'juillet' => 7, 'aout' => 8, 'septembre' => 9, 'octobre' => 10, 'novembre' => 11, 'decembre' => 12,
    ];

    /**
     * Analyse une question en langage naturel et renvoie une réponse structurée.
     *
     * @return array{intent: string, answer: string, understood: list<string>, metrics: list<array{label: string, value: string, hint?: string, trend?: float|null}>, table: array{columns: list<string>, rows: list<list<string>>}|null, suggestions: list<string>}
     */
    public function processQuery(string $query): array
    {
        $text = $this->normalize($query);
        $context = $this->parseContext($text);

        $result = match ($this->detectIntent($text)) {
            'revenue' => $this->revenue($context),
            'top_products' => $this->topProducts($context),
            'top_customers' => $this->topCustomers($context),
            'stock_low' => $this->lowStock($context),
            'stock_lookup' => $this->stockLookup($text, $context),
            'receivables' => $this->receivables($context),
            'inactive_customers' => $this->inactiveCustomers($text),
            'customer_lookup' => $this->customerLookup($text),
            'cash' => $this->cash(),
            'expenses' => $this->expenses($context),
            'profit' => $this->profit($context),
            'average_basket' => $this->averageBasket($context),
            'projects' => $this->projects(),
            'overview' => $this->overview($context),
            default => $this->help($query),
        };

        return $result + ['understood' => $context['understood'], 'metrics' => [], 'table' => null];
    }

    /*
    |--------------------------------------------------------------------------
    | Compréhension de la question
    |--------------------------------------------------------------------------
    */

    private function normalize(string $text): string
    {
        $ascii = Str::lower(Str::ascii($text));

        return trim((string) preg_replace('/\s+/', ' ', (string) preg_replace('/[^a-z0-9%]+/', ' ', $ascii)));
    }

    private function detectIntent(string $text): ?string
    {
        $padded = " {$text} ";
        $scores = [];

        foreach (self::INTENT_KEYWORDS as $intent => $keywords) {
            foreach ($keywords as $keyword => $weight) {
                if (str_contains($padded, " {$keyword} ")) {
                    $scores[$intent] = ($scores[$intent] ?? 0) + $weight;
                }
            }
        }

        // « top / meilleurs » + client(s) => meilleurs clients plutôt que meilleurs produits
        if (isset($scores['top_products']) && preg_match('/\b(top|meilleurs?|plus gros)\b.*\bclients?\b/', $text)) {
            $scores['top_customers'] = ($scores['top_customers'] ?? 0) + 6;
        }

        // « produits les plus vendus » ne doit pas devenir une question de chiffre d'affaires
        if (isset($scores['top_products'], $scores['revenue']) && preg_match('/\b(plus vendus?|meilleurs?|top)\b/', $text)) {
            $scores['top_products'] += 4;
        }

        if ($scores === []) {
            return null;
        }

        arsort($scores);

        return array_key_first($scores);
    }

    /**
     * @return array{domain: ?Domain, start: Carbon, end: Carbon, previousStart: Carbon, previousEnd: Carbon, periodLabel: string, previousLabel: string, limit: int, understood: list<string>}
     */
    private function parseContext(string $text): array
    {
        $now = Carbon::now();
        $padded = " {$text} ";

        [$start, $end, $label] = match (true) {
            str_contains($padded, ' aujourd hui ') || str_contains($padded, ' ce jour ') => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), "aujourd'hui"],
            str_contains($padded, ' hier ') => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay(), 'hier'],
            (bool) preg_match('/ semaine (derniere|passee|precedente) /', $padded) => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek(), 'la semaine dernière'],
            str_contains($padded, ' semaine ') => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'cette semaine'],
            (bool) preg_match('/ mois (dernier|passe|precedent) /', $padded) => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth(), 'le mois dernier'],
            (bool) preg_match('/ annee (derniere|passee|precedente) /', $padded) => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear(), "l'année dernière"],
            (bool) preg_match('/ (cette annee|annee|an) /', $padded) => [$now->copy()->startOfYear(), $now->copy()->endOfDay(), 'cette année'],
            (bool) preg_match('/ (\d{1,3}) derniers jours /', $padded, $days) => [$now->copy()->subDays((int) $days[1] - 1)->startOfDay(), $now->copy()->endOfDay(), "les {$days[1]} derniers jours"],
            default => $this->namedMonthPeriod($padded) ?? [$now->copy()->startOfMonth(), $now->copy()->endOfDay(), 'ce mois-ci'],
        };

        $lengthInDays = (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        $isFullMonth = $start->isSameDay($start->copy()->startOfMonth()) && $end->isSameDay($start->copy()->endOfMonth());

        [$previousStart, $previousEnd, $previousLabel] = match (true) {
            $label === 'ce mois-ci' => [$start->copy()->subMonthNoOverflow(), $end->copy()->subMonthNoOverflow(), 'la même période du mois dernier'],
            $label === 'cette année' => [$start->copy()->subYear(), $end->copy()->subYear(), "la même période l'an dernier"],
            $isFullMonth => [$start->copy()->subMonthNoOverflow()->startOfMonth(), $start->copy()->subMonthNoOverflow()->endOfMonth(), 'le mois précédent'],
            default => [$start->copy()->subDays($lengthInDays), $end->copy()->subDays($lengthInDays), 'la période précédente'],
        };

        $domain = Domain::all()->first(function (Domain $domain) use ($padded) {
            $code = $this->normalize((string) $domain->code);
            $name = $this->normalize(Str::after((string) $domain->name, 'IVOSPHERE'));

            return ($code !== '' && str_contains($padded, " {$code} ")) || ($name !== '' && str_contains($padded, " {$name} "));
        });

        preg_match('/\b(?:top|les|des)\s+(\d{1,2})\b/', $text, $limitMatch);
        $limit = max(1, min(20, (int) ($limitMatch[1] ?? 5)));

        $understood = [ucfirst($label)];
        if ($domain) {
            array_unshift($understood, 'Pôle '.$domain->name);
        }

        return [
            'domain' => $domain,
            'start' => $start,
            'end' => $end,
            'previousStart' => $previousStart,
            'previousEnd' => $previousEnd,
            'periodLabel' => $label,
            'previousLabel' => $previousLabel,
            'limit' => $limit,
            'understood' => $understood,
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}|null
     */
    private function namedMonthPeriod(string $padded): ?array
    {
        foreach (self::MONTHS as $name => $number) {
            if (str_contains($padded, " {$name} ")) {
                $year = preg_match('/ (20\d{2}) /', $padded, $yearMatch) ? (int) $yearMatch[1] : Carbon::now()->year;
                if (! isset($yearMatch[1]) && $number > Carbon::now()->month) {
                    $year--;
                }
                $start = Carbon::create($year, $number, 1)->startOfMonth();

                return [$start, $start->copy()->endOfMonth(), 'en '.$start->translatedFormat('F Y')];
            }
        }

        return null;
    }

    /**
     * Mots restants une fois retirés les mots outils, l'intention, la période et le pôle.
     *
     * @return list<string>
     */
    private function entityTerms(string $text): array
    {
        $ignored = array_merge(
            self::STOP_WORDS,
            array_keys(self::MONTHS),
            ['semaine', 'mois', 'annee', 'jour', 'jours', 'derniers', 'dernier', 'derniere', 'hier', 'ivosphere'],
            Domain::pluck('code')->map(fn ($code) => $this->normalize((string) $code))->all(),
        );

        return array_values(array_filter(
            explode(' ', $text),
            fn (string $word) => strlen($word) > 1 && ! in_array($word, $ignored, true) && ! is_numeric($word)
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Requêtes de base
    |--------------------------------------------------------------------------
    */

    private function ordersQuery(array $context, bool $previous = false): Builder
    {
        $start = $previous ? $context['previousStart'] : $context['start'];
        $end = $previous ? $context['previousEnd'] : $context['end'];

        return Order::query()
            ->whereNotIn('status', self::CANCELLED_ORDER_STATUSES)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->when($context['domain'], fn (Builder $query, Domain $domain) => $query->where('domain_id', $domain->id));
    }

    private function money(float $amount): string
    {
        return number_format($amount, 0, ',', ' ').' FCFA';
    }

    private function trend(float $current, float $previous): ?float
    {
        if ($previous == 0.0) {
            return null;
        }

        return round(($current - $previous) / abs($previous) * 100, 1);
    }

    private function trendSentence(?float $trend, string $previousLabel): string
    {
        return match (true) {
            $trend === null => '',
            $trend > 0 => " C'est **+{$trend} %** par rapport à {$previousLabel}.",
            $trend < 0 => " C'est **{$trend} %** par rapport à {$previousLabel}.",
            default => " C'est stable par rapport à {$previousLabel}.",
        };
    }

    private function scopeLabel(array $context): string
    {
        return $context['domain'] ? "du pôle {$context['domain']->name}" : 'tous pôles confondus';
    }

    /*
    |--------------------------------------------------------------------------
    | Réponses
    |--------------------------------------------------------------------------
    */

    private function revenue(array $context): array
    {
        $revenue = (float) $this->ordersQuery($context)->sum('total');
        $count = $this->ordersQuery($context)->count();
        $previousRevenue = (float) $this->ordersQuery($context, true)->sum('total');
        $trend = $this->trend($revenue, $previousRevenue);

        $table = null;
        if (! $context['domain']) {
            $byDomain = $this->ordersQuery($context)
                ->selectRaw('domain_id, COUNT(*) as orders_count, SUM(total) as revenue')
                ->groupBy('domain_id')
                ->orderByDesc('revenue')
                ->get();
            $domainNames = Domain::pluck('name', 'id');

            $table = [
                'columns' => ['Pôle', 'Commandes', "Chiffre d'affaires", 'Part'],
                'rows' => $byDomain->map(fn ($row) => [
                    $domainNames[$row->domain_id] ?? 'Non classé',
                    (string) $row->orders_count,
                    $this->money((float) $row->revenue),
                    $revenue > 0 ? round($row->revenue / $revenue * 100).' %' : '—',
                ])->all(),
            ];
        }

        return [
            'intent' => 'revenue',
            'answer' => "Le chiffre d'affaires {$this->scopeLabel($context)} {$context['periodLabel']} est de **{$this->money($revenue)}** sur **{$count} commande(s)**."
                .$this->trendSentence($trend, $context['previousLabel']),
            'metrics' => [
                ['label' => "Chiffre d'affaires", 'value' => $this->money($revenue), 'trend' => $trend],
                ['label' => 'Commandes', 'value' => (string) $count],
                ['label' => 'Panier moyen', 'value' => $count ? $this->money($revenue / $count) : '—'],
                ['label' => ucfirst($context['previousLabel']), 'value' => $this->money($previousRevenue)],
            ],
            'table' => $table,
            'suggestions' => ['Quels sont les produits les plus vendus '.$context['periodLabel'].' ?', 'Quels sont mes meilleurs clients ?', 'Quel est le bénéfice '.$context['periodLabel'].' ?'],
        ];
    }

    private function topProducts(array $context): array
    {
        $orderIds = $this->ordersQuery($context)->select('id');

        $rows = OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->selectRaw('COALESCE(product_id, 0) as product_key, MAX(description) as label, SUM(quantity) as quantity, SUM(total) as revenue')
            ->groupBy('product_key')
            ->orderByDesc('quantity')
            ->limit($context['limit'])
            ->get();

        if ($rows->isEmpty()) {
            return ['intent' => 'top_products', 'answer' => "Aucune vente enregistrée {$context['periodLabel']} ({$this->scopeLabel($context)}).", 'suggestions' => ['Quels sont les produits les plus vendus cette année ?']];
        }

        $productNames = Product::whereIn('id', $rows->pluck('product_key'))->pluck('name', 'id');
        $best = $rows->first();
        $bestName = $productNames[$best->product_key] ?? $best->label;

        return [
            'intent' => 'top_products',
            'answer' => "Voici les **{$rows->count()} produits les plus vendus** {$context['periodLabel']} ({$this->scopeLabel($context)}). En tête : **{$bestName}** avec **".number_format((float) $best->quantity, 0, ',', ' ').' unité(s)**.',
            'table' => [
                'columns' => ['#', 'Produit', 'Quantité', "Chiffre d'affaires"],
                'rows' => $rows->values()->map(fn ($row, $index) => [
                    (string) ($index + 1),
                    $productNames[$row->product_key] ?? $row->label,
                    number_format((float) $row->quantity, 0, ',', ' '),
                    $this->money((float) $row->revenue),
                ])->all(),
            ],
            'suggestions' => ['Quels produits sont bientôt en rupture ?', "Quel est le chiffre d'affaires ".$context['periodLabel'].' ?'],
        ];
    }

    private function topCustomers(array $context): array
    {
        $rows = $this->ordersQuery($context)
            ->whereHas('customer', fn (Builder $query) => $query->where('code', '!=', 'CLI-PASSAGER'))
            ->selectRaw('customer_id, COUNT(*) as orders_count, SUM(total) as revenue')
            ->groupBy('customer_id')
            ->orderByDesc('revenue')
            ->limit($context['limit'])
            ->get();

        if ($rows->isEmpty()) {
            return ['intent' => 'top_customers', 'answer' => "Aucun achat client identifié {$context['periodLabel']}.", 'suggestions' => ['Quels sont mes meilleurs clients cette année ?']];
        }

        $customers = Customer::whereIn('id', $rows->pluck('customer_id'))->get()->keyBy('id');
        $total = (float) $rows->sum('revenue');

        return [
            'intent' => 'top_customers',
            'answer' => "Vos **{$rows->count()} meilleurs clients** {$context['periodLabel']} représentent **{$this->money($total)}** d'achats.",
            'table' => [
                'columns' => ['#', 'Client', 'Commandes', 'Achats'],
                'rows' => $rows->values()->map(fn ($row, $index) => [
                    (string) ($index + 1),
                    $customers[$row->customer_id]?->name ?? 'Client supprimé',
                    (string) $row->orders_count,
                    $this->money((float) $row->revenue),
                ])->all(),
            ],
            'suggestions' => ["Quels clients n'ont pas commandé depuis 3 mois ?", 'Quelles factures sont impayées ?'],
        ];
    }

    private function lowStock(array $context): array
    {
        $products = Product::active()
            ->with('warehouseStocks')
            ->when($context['domain'], fn (Builder $query, Domain $domain) => $query->where('domain_id', $domain->id))
            ->get()
            ->filter(fn (Product $product) => $product->current_stock <= (int) $product->min_stock)
            ->sortBy(fn (Product $product) => $product->current_stock - (int) $product->min_stock)
            ->values();

        if ($products->isEmpty()) {
            return ['intent' => 'stock_low', 'answer' => 'Aucun produit sous son seuil minimum : les stocks sont au bon niveau.', 'suggestions' => ['Quels sont les produits les plus vendus ce mois-ci ?']];
        }

        $outOfStock = $products->filter(fn (Product $product) => $product->current_stock <= 0)->count();

        return [
            'intent' => 'stock_low',
            'answer' => "**{$products->count()} produit(s)** sont sous leur seuil minimum, dont **{$outOfStock} en rupture totale**. À réapprovisionner en priorité :",
            'metrics' => [
                ['label' => 'Sous le seuil', 'value' => (string) $products->count()],
                ['label' => 'En rupture', 'value' => (string) $outOfStock],
            ],
            'table' => [
                'columns' => ['Produit', 'Référence', 'Stock', 'Seuil'],
                'rows' => $products->take(15)->map(fn (Product $product) => [
                    $product->name,
                    (string) $product->sku,
                    (string) $product->current_stock,
                    (string) (int) $product->min_stock,
                ])->all(),
            ],
            'suggestions' => ['Quels sont les produits les plus vendus ce mois-ci ?', 'Combien avons-nous de trésorerie disponible ?'],
        ];
    }

    private function stockLookup(string $text, array $context): array
    {
        $terms = $this->entityTerms($text);

        if ($terms === []) {
            return $this->lowStock($context);
        }

        $products = Product::with('warehouseStocks.warehouse')
            ->where(function (Builder $query) use ($terms) {
                foreach ($terms as $term) {
                    $query->where(fn (Builder $inner) => $inner->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%"));
                }
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        if ($products->isEmpty()) {
            return ['intent' => 'stock_lookup', 'answer' => 'Je ne trouve aucun produit correspondant à « '.implode(' ', $terms).' ». Essayez avec un autre mot du nom ou la référence.', 'suggestions' => ['Quels produits sont bientôt en rupture ?']];
        }

        $first = $products->first();
        $byWarehouse = $first->warehouseStocks
            ->filter(fn ($stock) => $stock->physical_quantity > 0)
            ->map(fn ($stock) => ($stock->warehouse?->name ?? 'Entrepôt').' : '.$stock->physical_quantity)
            ->implode(' · ');

        return [
            'intent' => 'stock_lookup',
            'answer' => $products->count() === 1
                ? "**{$first->name}** : **{$first->current_stock} {$first->unit}** en stock".($byWarehouse ? " ({$byWarehouse})" : '').'.'
                : "**{$products->count()} produits** correspondent à votre recherche :",
            'table' => $products->count() > 1 ? [
                'columns' => ['Produit', 'Référence', 'Stock', 'Seuil'],
                'rows' => $products->map(fn (Product $product) => [$product->name, (string) $product->sku, (string) $product->current_stock, (string) (int) $product->min_stock])->all(),
            ] : null,
            'suggestions' => ['Quels produits sont bientôt en rupture ?', 'Quels sont les produits les plus vendus ce mois-ci ?'],
        ];
    }

    private function receivables(array $context): array
    {
        $unpaid = Invoice::whereIn('status', ['non_payee', 'partielle'])
            ->when($context['domain'], fn (Builder $query, Domain $domain) => $query->where('domain_id', $domain->id))
            ->with('customer')
            ->orderBy('due_date')
            ->get();

        if ($unpaid->isEmpty()) {
            return ['intent' => 'receivables', 'answer' => 'Aucune facture impayée : toutes les créances clients sont réglées.', 'suggestions' => ["Quel est le chiffre d'affaires ce mois-ci ?"]];
        }

        $balance = fn (Invoice $invoice) => (float) $invoice->total - (float) $invoice->paid_amount;
        $overdue = $unpaid->filter(fn (Invoice $invoice) => $invoice->due_date && Carbon::parse($invoice->due_date)->lt(Carbon::today()));

        return [
            'intent' => 'receivables',
            'answer' => "Vous avez **{$this->money($unpaid->sum($balance))}** à encaisser sur **{$unpaid->count()} facture(s)**, dont **{$this->money($overdue->sum($balance))}** déjà en retard (**{$overdue->count()} facture(s)**).",
            'metrics' => [
                ['label' => 'Total à encaisser', 'value' => $this->money($unpaid->sum($balance))],
                ['label' => 'En retard', 'value' => $this->money($overdue->sum($balance)), 'hint' => $overdue->count().' facture(s)'],
            ],
            'table' => [
                'columns' => ['Facture', 'Client', 'Reste dû', 'Échéance'],
                'rows' => $unpaid->sortByDesc($balance)->take(10)->map(fn (Invoice $invoice) => [
                    $invoice->reference,
                    $invoice->customer?->name ?? '—',
                    $this->money($balance($invoice)),
                    $invoice->due_date
                        ? Carbon::parse($invoice->due_date)->format('d/m/Y').(Carbon::parse($invoice->due_date)->lt(Carbon::today()) ? ' (retard '.(int) Carbon::parse($invoice->due_date)->diffInDays(Carbon::today()).' j)' : '')
                        : '—',
                ])->values()->all(),
            ],
            'suggestions' => ["Quels clients n'ont pas commandé depuis 3 mois ?", 'Combien avons-nous de trésorerie disponible ?'],
        ];
    }

    private function inactiveCustomers(string $text): array
    {
        $months = preg_match('/(\d{1,2}) mois/', $text, $match) ? max(1, (int) $match[1]) : 3;
        $since = Carbon::now()->subMonths($months);

        $customers = Customer::where('status', '!=', 'inactif')
            ->where('code', '!=', 'CLI-PASSAGER')
            ->whereHas('orders')
            ->whereDoesntHave('orders', fn (Builder $query) => $query->where('date', '>=', $since->toDateString()))
            ->withMax('orders', 'date')
            ->withSum('orders', 'total')
            ->orderByDesc('orders_sum_total')
            ->limit(15)
            ->get();

        if ($customers->isEmpty()) {
            return ['intent' => 'inactive_customers', 'answer' => "Tous vos clients ont commandé au cours des {$months} derniers mois.", 'suggestions' => ['Quels sont mes meilleurs clients ?']];
        }

        return [
            'intent' => 'inactive_customers',
            'answer' => "**{$customers->count()} client(s)** n'ont pas commandé depuis plus de **{$months} mois**. Classés par valeur d'achat historique, ce sont les premiers à relancer :",
            'table' => [
                'columns' => ['Client', 'Téléphone', 'Dernière commande', 'Achats cumulés'],
                'rows' => $customers->map(fn (Customer $customer) => [
                    $customer->name,
                    $customer->phone ?: '—',
                    $customer->orders_max_date ? Carbon::parse($customer->orders_max_date)->format('d/m/Y') : '—',
                    $this->money((float) $customer->orders_sum_total),
                ])->all(),
            ],
            'suggestions' => ['Quels sont mes meilleurs clients ?', 'Quelles factures sont impayées ?'],
        ];
    }

    private function customerLookup(string $text): array
    {
        $terms = $this->entityTerms($text);

        $customers = $terms === [] ? collect() : Customer::query()
            ->where(function (Builder $query) use ($terms) {
                foreach ($terms as $term) {
                    $query->where(fn (Builder $inner) => $inner->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"));
                }
            })
            ->limit(5)
            ->get();

        if ($customers->isEmpty()) {
            return $this->topCustomers($this->parseContext('cette annee'));
        }

        $customer = $customers->first();
        $orders = $customer->orders()->whereNotIn('status', self::CANCELLED_ORDER_STATUSES);
        $unpaid = Invoice::where('customer_id', $customer->id)->whereIn('status', ['non_payee', 'partielle'])->get();
        $due = (float) $unpaid->sum(fn (Invoice $invoice) => (float) $invoice->total - (float) $invoice->paid_amount);
        $lastOrderDate = (clone $orders)->max('date');

        return [
            'intent' => 'customer_lookup',
            'answer' => "**{$customer->name}** ({$customer->code}) : **{$this->money((float) (clone $orders)->sum('total'))}** d'achats sur **".(clone $orders)->count().' commande(s)**'
                .($lastOrderDate ? ', dernière commande le **'.Carbon::parse($lastOrderDate)->format('d/m/Y').'**' : '')
                .'. '.($due > 0 ? "Reste dû : **{$this->money($due)}**." : 'Aucun impayé.')
                .($customers->count() > 1 ? ' (Autres clients correspondants : '.$customers->skip(1)->pluck('name')->implode(', ').'.)' : ''),
            'metrics' => [
                ['label' => 'Achats cumulés', 'value' => $this->money((float) (clone $orders)->sum('total'))],
                ['label' => 'Commandes', 'value' => (string) (clone $orders)->count()],
                ['label' => 'Reste dû', 'value' => $this->money($due)],
                ['label' => 'Téléphone', 'value' => $customer->phone ?: '—'],
            ],
            'suggestions' => ['Quels sont mes meilleurs clients ?', 'Quelles factures sont impayées ?'],
        ];
    }

    private function cash(): array
    {
        $registers = CashRegister::where('is_active', true)->orderByDesc('balance')->get();
        $total = (float) $registers->sum('balance');

        return [
            'intent' => 'cash',
            'answer' => "La trésorerie disponible dans les caisses actives est de **{$this->money($total)}**, répartie sur **{$registers->count()} caisse(s)**.",
            'metrics' => [['label' => 'Trésorerie en caisse', 'value' => $this->money($total)]],
            'table' => $registers->isEmpty() ? null : [
                'columns' => ['Caisse', 'Solde'],
                'rows' => $registers->map(fn (CashRegister $register) => [$register->name, $this->money((float) $register->balance)])->all(),
            ],
            'suggestions' => ['Quelles factures sont impayées ?', 'Quelles sont les dépenses ce mois-ci ?'],
        ];
    }

    private function expensesQuery(array $context, bool $previous = false): Builder
    {
        return Expense::query()
            ->whereNotIn('status', ['rejetee', 'rejete', 'refusee', 'annulee'])
            ->whereBetween('date', [($previous ? $context['previousStart'] : $context['start'])->toDateString(), ($previous ? $context['previousEnd'] : $context['end'])->toDateString()])
            ->when($context['domain'], fn (Builder $query, Domain $domain) => $query->where('domain_id', $domain->id));
    }

    private function expenses(array $context): array
    {
        $total = (float) $this->expensesQuery($context)->sum('amount');
        $trend = $this->trend($total, (float) $this->expensesQuery($context, true)->sum('amount'));
        $byCategory = $this->expensesQuery($context)
            ->selectRaw('category, COUNT(*) as expenses_count, SUM(amount) as amount')
            ->groupBy('category')
            ->orderByDesc('amount')
            ->get();

        return [
            'intent' => 'expenses',
            'answer' => "Les dépenses {$this->scopeLabel($context)} {$context['periodLabel']} s'élèvent à **{$this->money($total)}**.".$this->trendSentence($trend, $context['previousLabel']),
            'metrics' => [['label' => 'Dépenses', 'value' => $this->money($total), 'trend' => $trend, 'inverse' => true]],
            'table' => $byCategory->isEmpty() ? null : [
                'columns' => ['Catégorie', 'Nombre', 'Montant'],
                'rows' => $byCategory->map(fn ($row) => [ucfirst(str_replace('_', ' ', (string) ($row->category ?: 'Autre'))), (string) $row->expenses_count, $this->money((float) $row->amount)])->all(),
            ],
            'suggestions' => ['Quel est le bénéfice '.$context['periodLabel'].' ?', "Quel est le chiffre d'affaires ".$context['periodLabel'].' ?'],
        ];
    }

    private function profit(array $context): array
    {
        $revenue = (float) $this->ordersQuery($context)->sum('total');
        $expenses = (float) $this->expensesQuery($context)->sum('amount');
        $result = $revenue - $expenses;
        $previousResult = (float) $this->ordersQuery($context, true)->sum('total') - (float) $this->expensesQuery($context, true)->sum('amount');
        $margin = $revenue > 0 ? round($result / $revenue * 100, 1) : null;

        return [
            'intent' => 'profit',
            'answer' => "Résultat {$this->scopeLabel($context)} {$context['periodLabel']} : **{$this->money($result)}** ({$this->money($revenue)} de ventes − {$this->money($expenses)} de dépenses)"
                .($margin !== null ? ", soit une marge de **{$margin} %**." : '.')
                .$this->trendSentence($this->trend($result, $previousResult), $context['previousLabel'])
                ."\n\n*Estimation basée sur les ventes et les dépenses enregistrées : le coût d'achat des marchandises n'est pas déduit.*",
            'metrics' => [
                ['label' => 'Ventes', 'value' => $this->money($revenue)],
                ['label' => 'Dépenses', 'value' => $this->money($expenses)],
                ['label' => 'Résultat', 'value' => $this->money($result), 'trend' => $this->trend($result, $previousResult)],
                ['label' => 'Marge', 'value' => $margin !== null ? "{$margin} %" : '—'],
            ],
            'suggestions' => ['Quelles sont les dépenses '.$context['periodLabel'].' ?', 'Quels sont les produits les plus vendus '.$context['periodLabel'].' ?'],
        ];
    }

    private function averageBasket(array $context): array
    {
        $count = $this->ordersQuery($context)->count();
        $average = $count ? (float) $this->ordersQuery($context)->avg('total') : 0.0;
        $previousCount = $this->ordersQuery($context, true)->count();
        $previousAverage = $previousCount ? (float) $this->ordersQuery($context, true)->avg('total') : 0.0;
        $trend = $this->trend($average, $previousAverage);

        return [
            'intent' => 'average_basket',
            'answer' => "Le panier moyen {$this->scopeLabel($context)} {$context['periodLabel']} est de **{$this->money($average)}** sur {$count} commande(s).".$this->trendSentence($trend, $context['previousLabel']),
            'metrics' => [
                ['label' => 'Panier moyen', 'value' => $this->money($average), 'trend' => $trend],
                ['label' => 'Commandes', 'value' => (string) $count],
            ],
            'suggestions' => ['Quels sont les produits les plus vendus '.$context['periodLabel'].' ?', "Quel est le chiffre d'affaires ".$context['periodLabel'].' ?'],
        ];
    }

    private function projects(): array
    {
        $projects = TechProject::whereIn('status', ['nouveau', 'en_cours', 'en_recette'])->with('customer')->orderBy('end_date')->limit(10)->get();

        if ($projects->isEmpty()) {
            return ['intent' => 'projects', 'answer' => 'Aucun projet en cours actuellement.', 'suggestions' => ["Quel est le chiffre d'affaires ce mois-ci ?"]];
        }

        $late = $projects->filter(fn (TechProject $project) => $project->end_date && Carbon::parse($project->end_date)->lt(Carbon::today()))->count();

        return [
            'intent' => 'projects',
            'answer' => "**{$projects->count()} projet(s)** en cours".($late ? ", dont **{$late} en retard** sur l'échéance" : '').'.',
            'table' => [
                'columns' => ['Projet', 'Client', 'Avancement', 'Échéance'],
                'rows' => $projects->map(fn (TechProject $project) => [
                    $project->name,
                    $project->customer?->name ?? 'Interne',
                    ($project->progress ?? 0).' %',
                    $project->end_date ? Carbon::parse($project->end_date)->format('d/m/Y') : '—',
                ])->all(),
            ],
            'suggestions' => ["Quel est le chiffre d'affaires de TECH ce mois-ci ?"],
        ];
    }

    private function overview(array $context): array
    {
        $revenue = (float) $this->ordersQuery($context)->sum('total');
        $count = $this->ordersQuery($context)->count();
        $trend = $this->trend($revenue, (float) $this->ordersQuery($context, true)->sum('total'));
        $expenses = (float) $this->expensesQuery($context)->sum('amount');
        $unpaid = Invoice::whereIn('status', ['non_payee', 'partielle'])->get();
        $due = (float) $unpaid->sum(fn (Invoice $invoice) => (float) $invoice->total - (float) $invoice->paid_amount);
        $cash = (float) CashRegister::where('is_active', true)->sum('balance');

        $points = collect([
            $trend !== null && $trend < -10 ? "les ventes reculent de {$trend} %" : null,
            $trend !== null && $trend > 10 ? "les ventes progressent de +{$trend} %" : null,
            $due > 0 ? "{$this->money($due)} restent à encaisser" : null,
            $expenses > $revenue && $revenue > 0 ? 'les dépenses dépassent les ventes' : null,
        ])->filter();

        return [
            'intent' => 'overview',
            'answer' => "Bilan {$this->scopeLabel($context)} {$context['periodLabel']} : **{$this->money($revenue)}** de ventes sur **{$count} commande(s)**."
                .($points->isNotEmpty() ? ' À noter : '.$points->implode(', ').'.' : ''),
            'metrics' => [
                ['label' => 'Ventes', 'value' => $this->money($revenue), 'trend' => $trend],
                ['label' => 'Dépenses', 'value' => $this->money($expenses)],
                ['label' => 'À encaisser', 'value' => $this->money($due)],
                ['label' => 'Trésorerie', 'value' => $this->money($cash)],
            ],
            'suggestions' => ['Quels sont les produits les plus vendus '.$context['periodLabel'].' ?', 'Quels produits sont bientôt en rupture ?', 'Quelles factures sont impayées ?'],
        ];
    }

    private function help(string $query): array
    {
        return [
            'intent' => 'help',
            'answer' => "Je n'ai pas compris « {$query} ». Je réponds aux questions sur les ventes, les produits, le stock, les clients, les factures, la trésorerie, les dépenses et les projets. Précisez si besoin un pôle (PRINT, TECH…) et une période (hier, cette semaine, le mois dernier, en mars…).",
            'suggestions' => self::exampleQuestions()->take(4)->all(),
        ];
    }

    /**
     * Exemples de questions proposées sur la page.
     *
     * @return Collection<int, string>
     */
    public static function exampleQuestions(): Collection
    {
        return collect([
            "Quel est le chiffre d'affaires ce mois-ci ?",
            'Top 5 des produits les plus vendus cette semaine',
            'Quels produits sont bientôt en rupture ?',
            'Quelles factures sont impayées ?',
            'Quels sont mes meilleurs clients cette année ?',
            'Quel est le bénéfice du mois dernier ?',
            'Quels clients ne commandent plus depuis 6 mois ?',
            'Fais-moi le bilan de la semaine',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Studio de génération (modèles de documents)
    |--------------------------------------------------------------------------
    */

    /**
     * Generate creative business content (Studio IA).
     *
     * @param  array<string, mixed>  $params
     * @return array{title: string, content: string, format: string, tags: array<string>}
     */
    public function generateContent(string $type, array $params = []): array
    {
        $domainName = $params['domain'] ?? 'IVOSPHERE';
        $topic = $params['topic'] ?? 'Excellence & Performance';
        $targetAudience = ($params['target'] ?? null) ?: 'Entreprises et Professionnels';

        return match ($type) {
            'commercial_pitch' => [
                'title' => "Proposition commerciale — {$topic}",
                'format' => 'Proposition commerciale',
                'tags' => ['Commercial', $domainName, 'Vente'],
                'content' => "Madame, Monsieur,\n\n"
                    ."Dans le cadre de l'optimisation de vos activités, {$domainName} a le plaisir de vous soumettre une proposition sur mesure dédiée à : {$topic}.\n\n"
                    ."Vos enjeux :\n"
                    ."- Accroître votre productivité avec des livrables de haute précision.\n"
                    ."- Bénéficier d'un accompagnement réactif et d'un interlocuteur unique.\n"
                    ."- Maîtriser vos coûts tout en maximisant votre retour sur investissement.\n\n"
                    ."Notre solution :\n"
                    ."Nous mobilisons toute notre expertise {$domainName} pour {$targetAudience}, avec un calendrier d'exécution garanti.\n\n"
                    ."Prochaine étape :\n"
                    .'Nous vous proposons un échange de 15 minutes pour ajuster les spécifications avant validation.',
            ],

            'product_description' => [
                'title' => "Fiche produit — {$topic}",
                'format' => 'Description catalogue & e-commerce',
                'tags' => ['Catalogue', $domainName, 'SEO'],
                'content' => "{$topic}\n\n"
                    ."L'excellence signée {$domainName} pour vos projets exigeants.\n\n"
                    ."Conçu pour répondre aux standards professionnels les plus stricts, ce produit combine fiabilité, finition soignée et performance durable.\n\n"
                    ."Points forts :\n"
                    ."• Qualité de fabrication et contrôle rigoureux.\n"
                    ."• Parfaitement adapté pour : {$targetAudience}.\n"
                    ."• Garantie et support réactif sous 24 h ouvrées.\n\n"
                    .'Spécifications disponibles sur simple demande auprès de nos conseillers.',
            ],

            'invoice_followup_email' => [
                'title' => 'E-mail de relance — facture échue',
                'format' => 'E-mail de relance courtois et ferme',
                'tags' => ['Finance', 'Recouvrement'],
                'content' => "Objet : Rappel — règlement de votre facture en attente\n\n"
                    ."Madame, Monsieur,\n\n"
                    ."Sauf erreur de notre part, votre facture relative à nos prestations {$domainName} ({$topic}) demeure à ce jour impayée.\n\n"
                    ."Nous vous remercions de bien vouloir régulariser cette situation dans les meilleurs délais, ou de nous transmettre la preuve de paiement correspondante.\n\n"
                    ."Si votre règlement a été émis récemment, merci de ne pas tenir compte de ce message.\n\n"
                    ."Cordialement,\n"
                    .'La Direction financière',
            ],

            'social_post' => [
                'title' => "Publication réseaux sociaux — {$domainName}",
                'format' => 'Post LinkedIn / Facebook',
                'tags' => ['Marketing', 'Réseaux sociaux'],
                'content' => "Innovez avec {$domainName} !\n\n"
                    ."Aujourd'hui, nous mettons en lumière : {$topic}.\n\n"
                    ."Pourquoi {$targetAudience} nous font confiance :\n"
                    ."✅ Un savoir-faire axé sur la qualité\n"
                    ."✅ Des délais tenus à chaque étape\n"
                    ."✅ Un accompagnement de bout en bout\n\n"
                    ."Et vous, quels sont vos défis ce trimestre ? Parlons-en en commentaire ou en message privé.\n\n"
                    .'#IVOSPHERE #'.Str::studly(Str::ascii($domainName)).' #Business',
            ],

            'slogans' => [
                'title' => "Slogans — {$topic}",
                'format' => 'Accroches de campagne',
                'tags' => ['Créatif', 'Branding'],
                'content' => "5 propositions de slogans pour {$domainName} :\n\n"
                    ."1. « {$topic} : l'excellence dans chaque détail. »\n"
                    ."2. « L'ambition sans limite, la signature {$domainName}. »\n"
                    ."3. « Plus qu'une solution : votre standard de performance. »\n"
                    ."4. « Façonnez l'avenir de vos projets avec {$domainName}. »\n"
                    .'5. « Précision. Fiabilité. Impact immédiat. »',
            ],

            'video_script' => [
                'title' => "Script vidéo 30 s — {$topic}",
                'format' => 'Script de tournage',
                'tags' => ['Média', 'Vidéo'],
                'content' => "[00:00 – 00:05] Plan rythmé sur l'activité en atelier ou au bureau. Musique entraînante.\n"
                    ."VOIX OFF : « Vous exigez le meilleur. Nous aussi. »\n\n"
                    ."[00:05 – 00:15] Démonstration de {$topic}, infographie épurée.\n"
                    ."VOIX OFF : « Avec {$domainName}, accédez à des standards pensés pour accélérer vos résultats. »\n\n"
                    ."[00:15 – 00:25] Témoignage client, poignée de main.\n"
                    ."VOIX OFF : « Des clients satisfaits, des projets livrés. Faites le choix de la certitude. »\n\n"
                    ."[00:25 – 00:30] Logo IVOSPHERE animé.\n"
                    .'VOIX OFF : « IVOSPHERE. Contactez-nous dès aujourd’hui. »',
            ],

            'executive_report' => [
                'title' => "Synthèse exécutive — {$topic}",
                'format' => 'Rapport pour la Direction',
                'tags' => ['Direction', 'Rapport'],
                'content' => "SYNTHÈSE EXÉCUTIVE — {$topic}\n"
                    .'Date : '.Carbon::now()->isoFormat('D MMMM YYYY')."\n\n"
                    ."1. Contexte et objectifs\n"
                    ."Ce rapport synthétise les dynamiques opérationnelles et financières observées concernant {$topic}.\n\n"
                    ."2. Faits marquants\n"
                    .'- '.collect($this->overview($this->parseContext('ce mois'))['metrics'])->map(fn (array $metric) => "{$metric['label']} : {$metric['value']}")->implode("\n- ")."\n\n"
                    ."3. Recommandations\n"
                    .'- À compléter selon les priorités de la Direction.',
            ],

            default => [
                'title' => "Document — {$topic}",
                'format' => 'Synthèse générale',
                'tags' => ['Général'],
                'content' => "Document généré pour {$topic} ({$domainName}).",
            ],
        };
    }
}
