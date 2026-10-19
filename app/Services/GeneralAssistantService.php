<?php

namespace App\Services;

use App\Models\User;
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
    ) {}

    /**
     * @return array{intent: string, answer: string, metrics: list<array<string, mixed>>, table: array{columns: list<string>, rows: list<list<string>>}|null, links: list<array{label: string, module: string, url: string}>, suggestions?: list<string>}
     */
    public function answer(string $query, User $user): array
    {
        if ($this->isSmallTalk($query)) {
            return $this->introduction($user);
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
            return [
                'intent' => 'forbidden',
                'answer' => "Votre rôle ne vous permet pas d'interroger l'assistant sur ce sujet. Rapprochez-vous d'un administrateur si vous en avez besoin.",
                'metrics' => [],
                'table' => null,
                'links' => [],
            ];
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
            $suggestions[] = 'Quelles factures sont impayées ?';
        }
        if ($this->canAsk($user, 'ai.stock', 'stock.index')) {
            $suggestions[] = 'Quels produits sont bientôt en rupture ?';
        }
        if ($this->canAsk($user, 'ai.finance', 'finance.index')) {
            $suggestions[] = 'Quel est le bénéfice du mois dernier ?';
        }

        $suggestions[] = 'Où créer un nouveau devis ?';
        $suggestions[] = 'Comment demander un congé ?';

        return array_slice($suggestions, 0, 5);
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
        ]));
    }

    private function canAsk(User $user, string $permission, string $route): bool
    {
        return $user->hasPermission($permission) && $this->navigator->canAccess($user, $route);
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
