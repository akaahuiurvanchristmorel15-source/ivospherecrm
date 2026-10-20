<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * IA générale de l'accueil : répond sur les chiffres de l'entreprise (ventes, stock, finance…)
 * et connaît toutes les pages du menu pour guider l'utilisateur, dans la limite de ses droits.
 */
class GeneralAssistantService
{
    /**
     * Droits requis pour chaque type de réponse chiffrée : la permission IA du domaine
     * et l'accès au module concerné.
     *
     * @var array<string, array{permission: string, route: string}>
     */
    private const INTENT_ACCESS = [
        'revenue' => ['permission' => 'ai.sales', 'route' => 'commercial.index'],
        'top_products' => ['permission' => 'ai.sales', 'route' => 'commercial.index'],
        'top_customers' => ['permission' => 'ai.sales', 'route' => 'commercial.index'],
        'receivables' => ['permission' => 'ai.sales', 'route' => 'commercial.index'],
        'inactive_customers' => ['permission' => 'ai.sales', 'route' => 'commercial.index'],
        'customer_lookup' => ['permission' => 'ai.sales', 'route' => 'commercial.index'],
        'average_basket' => ['permission' => 'ai.sales', 'route' => 'commercial.index'],
        'overview' => ['permission' => 'ai.finance', 'route' => 'finance.index'],
        'stock_low' => ['permission' => 'ai.stock', 'route' => 'stock.index'],
        'stock_lookup' => ['permission' => 'ai.stock', 'route' => 'stock.index'],
        'cash' => ['permission' => 'ai.finance', 'route' => 'finance.index'],
        'expenses' => ['permission' => 'ai.finance', 'route' => 'finance.index'],
        'profit' => ['permission' => 'ai.finance', 'route' => 'finance.index'],
        'projects' => ['permission' => 'ai.projects', 'route' => 'tech.index'],
    ];

    public function __construct(
        protected AiAssistantService $dataAssistant,
        protected AppNavigator $navigator,
        protected BusinessForecastService $forecasts,
        protected AppKnowledgeService $knowledge,
    ) {}

    /**
     * @return array{intent: string, answer: string, metrics: list<array<string, mixed>>, table: array{columns: list<string>, rows: list<list<string>>}|null, links: list<array{label: string, module: string, url: string}>, suggestions?: list<string>}
     */
    public function answer(string $query, User $user): array
    {
        if ($this->isSmallTalk($query)) {
            return $this->introduction($user);
        }

        if ($forecastIntent = $this->forecastIntent($query)) {
            [$permission, $route] = $forecastIntent === 'forecast_stockouts' ? ['ai.stock', 'stock.index'] : ['ai.sales', 'commercial.index'];

            if (! $this->canAsk($user, $permission, $route)) {
                return $this->forbidden();
            }

            return $forecastIntent === 'forecast_stockouts' ? $this->stockoutForecast($query) : $this->salesForecast();
        }

        $isNavigation = $this->navigator->isNavigationQuestion($query);
        $pages = $this->navigator->search($query, $user);
        $module = $this->navigator->moduleMentioned($query, $user);

        if ($isNavigation && $module !== null && ($pages === [] || $this->asksForModuleContent($query))) {
            return $this->moduleAnswer($module);
        }

        if ($isNavigation && $pages !== []) {
            return $this->navigationAnswer($pages);
        }

        if ($knowledge = $this->knowledge->answer($query, $user)) {
            return $knowledge;
        }

        $result = $this->dataAssistant->processQuery($query);

        if ($result['intent'] === 'help') {
            return match (true) {
                $pages !== [] => $this->navigationAnswer($pages),
                $module !== null => $this->moduleAnswer($module),
                default => $result + ['links' => []],
            };
        }

        $access = self::INTENT_ACCESS[$result['intent']] ?? null;
        if ($access !== null && ! $this->canAsk($user, $access['permission'], $access['route'])) {
            return $this->forbidden();
        }

        return $result + ['links' => array_map(fn (array $page) => $this->link($page), array_slice($pages, 0, 2))];
    }

    /**
     * Suggestions de l'accueil, adaptées aux modules accessibles.
     *
     * @return list<string>
     */
    public function suggestionsFor(User $user): array
    {
        $suggestions = [];

        if ($this->canAsk($user, 'ai.sales', 'commercial.index')) {
            $suggestions[] = "Quel est le chiffre d'affaires ce mois-ci ?";
            $suggestions[] = 'Quelle est la prévision des ventes des 30 prochains jours ?';
        }
        if ($this->canAsk($user, 'ai.stock', 'stock.index')) {
            $suggestions[] = 'Quels produits seront bientôt en rupture ?';
        }
        if ($this->canAsk($user, 'ai.finance', 'finance.index')) {
            $suggestions[] = 'Quel est le bénéfice du mois dernier ?';
        }
        if ($this->navigator->canAccess($user, 'rh.attendance.index')) {
            $suggestions[] = "Qui est présent aujourd'hui ?";
        }
        if ($this->navigator->canAccess($user, 'commercial.appointments.index')) {
            $suggestions[] = 'Quels sont les rendez-vous de la semaine ?';
        }

        $suggestions[] = 'Où créer un nouveau devis ?';
        $suggestions[] = 'Quel est mon rôle ?';

        return array_slice($suggestions, 0, 7);
    }

    /**
     * Sujets chiffrés autorisés à l'utilisateur, pour l'aide.
     *
     * @return list<string>
     */
    public function allowedTopicsFor(User $user): array
    {
        return array_keys(array_filter([
            'ventes, clients et factures' => $this->canAsk($user, 'ai.sales', 'commercial.index'),
            'stocks' => $this->canAsk($user, 'ai.stock', 'stock.index'),
            'trésorerie, dépenses et bénéfice' => $this->canAsk($user, 'ai.finance', 'finance.index'),
            'projets' => $this->canAsk($user, 'ai.projects', 'tech.index'),
            'présences, congés et effectif' => $this->navigator->canAccess($user, 'rh.attendance.index'),
            'rendez-vous, prospects et devis' => $this->navigator->canAccess($user, 'commercial.prospects.index'),
            'cyber (dépôts, agents)' => $this->navigator->canAccess($user, 'commercial.cyber.index'),
            "paramètres de l'entreprise" => true,
        ]));
    }

    private function canAsk(User $user, string $permission, string $route): bool
    {
        return $user->hasPermission($permission) && $this->navigator->canAccess($user, $route);
    }

    /**
     * @return array{intent: string, answer: string, metrics: list<array<string, mixed>>, table: null, links: list<array{label: string, module: string, url: string}>}
     */
    private function forbidden(): array
    {
        return [
            'intent' => 'forbidden',
            'answer' => "Votre rôle ne vous permet pas d'interroger l'assistant sur ce sujet. Rapprochez-vous d'un administrateur si vous en avez besoin.",
            'metrics' => [],
            'table' => null,
            'links' => [],
        ];
    }

    /**
     * Question de prévision : ventes à venir ou ruptures de stock à venir.
     */
    private function forecastIntent(string $query): ?string
    {
        $text = ' '.$this->normalize($query).' ';

        if (! preg_match('/ (prevision|previsions|prevoir|prevu|prevue|prochains?|prochaines?|seront|sera|vont|quand|estimee?s?|attendue?s?|futures?|a venir) /', $text)) {
            return null;
        }

        return preg_match('/ (rupture|ruptures|stock|stocks|epuise|epuises|commander|reapprovisionner|reappro) /', $text)
            ? 'forecast_stockouts'
            : (preg_match('/ (vente|ventes|chiffre|ca|recette|recettes|vendre|encaisser) /', $text) ? 'forecast_sales' : null);
    }

    /**
     * @return array{intent: string, answer: string, metrics: list<array<string, mixed>>, table: array{columns: list<string>, rows: list<list<string>>}|null, links: list<array{label: string, module: string, url: string}>}
     */
    private function salesForecast(): array
    {
        $sales = $this->forecasts->latest()['sales'];
        $trend = $sales['trend_pct'];

        if ((float) $sales['next_30_days'] <= 0) {
            return ['intent' => 'forecast_sales', 'answer' => 'Pas encore assez de ventes enregistrées pour établir une prévision fiable.', 'metrics' => [], 'table' => null, 'links' => []];
        }

        return [
            'intent' => 'forecast_sales',
            'answer' => "D'après l'historique des ventes, je prévois environ **{$this->money((float) $sales['next_7_days'])}** sur les 7 prochains jours et **{$this->money((float) $sales['next_30_days'])}** sur les 30 prochains jours"
                .($trend !== null ? ', soit **'.($trend > 0 ? '+' : '')."{$trend} %** par rapport aux 30 derniers jours" : '').'.',
            'metrics' => [
                ['label' => '7 prochains jours', 'value' => $this->money((float) $sales['next_7_days'])],
                ['label' => '30 prochains jours', 'value' => $this->money((float) $sales['next_30_days'])],
                ['label' => '30 derniers jours', 'value' => $this->money((float) $sales['last_30_days'])],
            ],
            'table' => $sales['by_domain'] !== [] ? [
                'columns' => ['Pôle', 'Prévision 30 jours'],
                'rows' => array_map(fn (array $row) => [(string) $row['domain'], $this->money((float) $row['next_30_days'])], $sales['by_domain']),
            ] : null,
            'links' => [],
        ];
    }

    /**
     * @return array{intent: string, answer: string, metrics: list<array<string, mixed>>, table: array{columns: list<string>, rows: list<list<string>>}|null, links: list<array{label: string, module: string, url: string}>}
     */
    private function stockoutForecast(string $query): array
    {
        $stockouts = collect($this->forecasts->latest()['stockouts']);
        $text = ' '.$this->normalize($query).' ';

        $product = $stockouts->first(function (array $item) use ($text) {
            $words = array_filter(explode(' ', $this->normalize($item['name'])), fn (string $word) => strlen($word) > 2);

            return $words !== [] && collect($words)->every(fn (string $word) => str_contains($text, " {$word} "));
        });

        if ($product !== null) {
            return [
                'intent' => 'forecast_stockouts',
                'answer' => "**{$product['name']}** : il reste **".(int) $product['stock'].'** en stock pour environ **'.$product['daily_rate'].'** vendu(s) par jour. '
                    .'Rupture estimée **'.($product['days_left'] < 1 ? "dès aujourd'hui" : 'le '.Carbon::parse($product['stockout_date'])->format('d/m/Y').' (dans '.(int) floor($product['days_left']).' jour(s))').'**'
                    .($product['reorder_qty'] > 0 ? ". Pour tenir 30 jours, commandez environ **{$product['reorder_qty']}** unité(s)." : '.'),
                'metrics' => [],
                'table' => null,
                'links' => [],
            ];
        }

        $soon = $stockouts->filter(fn (array $item) => $item['days_left'] <= 30)->take(15)->values();

        if ($soon->isEmpty()) {
            return ['intent' => 'forecast_stockouts', 'answer' => 'Au rythme actuel des ventes, aucun produit ne devrait tomber en rupture dans les 30 prochains jours.', 'metrics' => [], 'table' => null, 'links' => []];
        }

        return [
            'intent' => 'forecast_stockouts',
            'answer' => "Au rythme actuel des ventes, **{$soon->count()} produit(s)** risquent la rupture dans les 30 prochains jours. Les plus urgents :",
            'metrics' => [],
            'table' => [
                'columns' => ['Produit', 'Stock', 'Ventes / jour', 'Rupture estimée', 'À commander'],
                'rows' => $soon->map(fn (array $item) => [
                    $item['name'],
                    (string) (int) $item['stock'],
                    (string) $item['daily_rate'],
                    $item['days_left'] < 1 ? "Aujourd'hui" : Carbon::parse($item['stockout_date'])->format('d/m').' ('.(int) floor($item['days_left']).' j)',
                    (string) $item['reorder_qty'],
                ])->all(),
            ],
            'links' => [],
        ];
    }

    private function normalize(string $text): string
    {
        return trim((string) preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($text)))));
    }

    private function money(float $amount): string
    {
        return number_format($amount, 0, ',', ' ').' FCFA';
    }

    private function isSmallTalk(string $query): bool
    {
        $text = trim((string) preg_replace('/[^a-z ]+/', ' ', Str::lower(Str::ascii($query))));

        return (bool) preg_match('/^(bonjour|bonsoir|salut|hello|coucou|aide|help|merci)( .*)?$|que (peux|sais) tu faire|qui es tu|tu peux faire quoi/', $text)
            && str_word_count($text) <= 6;
    }

    private function asksForModuleContent(string $query): bool
    {
        return (bool) preg_match('/contient|contenu|dans le (pole|module|menu)|que (puis|peut)|quoi faire|liste/i', Str::ascii($query));
    }

    /**
     * @return array{intent: string, answer: string, metrics: list<array<string, mixed>>, table: null, links: list<array{label: string, module: string, url: string}>, suggestions: list<string>}
     */
    private function introduction(User $user): array
    {
        $modules = $this->navigator->modulesFor($user);
        $topics = $this->allowedTopicsFor($user);

        return [
            'intent' => 'introduction',
            'answer' => "Je suis l'assistant IVOSPHERE. Je connais toutes les pages de l'application auxquelles vous avez accès : demandez-moi où faire une action."
                .($topics !== [] ? "\n\nJe peux aussi vous donner vos chiffres sur : **".implode('**, **', $topics).'**.' : '')
                .($modules !== [] ? "\n\nVos modules : **".implode('**, **', $modules).'**.' : ''),
            'metrics' => [],
            'table' => null,
            'links' => [],
            'suggestions' => $this->suggestionsFor($user),
        ];
    }

    /**
     * @param  list<array{label: string, module: string, url: string, score: float}>  $pages
     * @return array{intent: string, answer: string, metrics: list<array<string, mixed>>, table: null, links: list<array{label: string, module: string, url: string}>}
     */
    private function navigationAnswer(array $pages): array
    {
        $best = $pages[0];
        $others = array_slice($pages, 1);

        return [
            'intent' => 'navigation',
            'answer' => "Rendez-vous dans **{$best['module']} › {$best['label']}**."
                .($others !== [] ? ' Ces pages peuvent aussi vous intéresser :' : ''),
            'metrics' => [],
            'table' => null,
            'links' => array_map(fn (array $page) => $this->link($page), $pages),
        ];
    }

    /**
     * @param  array{label: string, pages: list<array{label: string, module: string, url: string}>}  $module
     * @return array{intent: string, answer: string, metrics: list<array<string, mixed>>, table: null, links: list<array{label: string, module: string, url: string}>}
     */
    private function moduleAnswer(array $module): array
    {
        return [
            'intent' => 'module',
            'answer' => "Le module **{$module['label']}** contient **".count($module['pages']).' pages** :',
            'metrics' => [],
            'table' => null,
            'links' => array_map(fn (array $page) => $this->link($page), $module['pages']),
        ];
    }

    /**
     * @param  array{label: string, module: string, url: string}  $page
     * @return array{label: string, module: string, url: string}
     */
    private function link(array $page): array
    {
        return ['label' => $page['label'], 'module' => $page['module'], 'url' => $page['url']];
    }
}
