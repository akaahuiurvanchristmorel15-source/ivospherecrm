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
     * Page dont l'accès conditionne chaque type de réponse chiffrée.
     *
     * @var array<string, string>
     */
    private const INTENT_ROUTES = [
        'revenue' => 'commercial.index',
        'top_products' => 'commercial.index',
        'top_customers' => 'commercial.index',
        'receivables' => 'commercial.index',
        'inactive_customers' => 'commercial.index',
        'customer_lookup' => 'commercial.index',
        'average_basket' => 'commercial.index',
        'overview' => 'commercial.index',
        'stock_low' => 'stock.index',
        'stock_lookup' => 'stock.index',
        'cash' => 'finance.index',
        'expenses' => 'finance.index',
        'profit' => 'finance.index',
        'projects' => 'tech.index',
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

        $requiredRoute = self::INTENT_ROUTES[$result['intent']] ?? null;
        if ($requiredRoute !== null && ! $this->navigator->canAccess($user, $requiredRoute)) {
            return [
                'intent' => 'forbidden',
                'answer' => "Cette information relève d'un module auquel votre rôle n'a pas accès. Rapprochez-vous de votre responsable si vous en avez besoin.",
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

        if ($this->navigator->canAccess($user, 'commercial.index')) {
            $suggestions[] = "Quel est le chiffre d'affaires ce mois-ci ?";
            $suggestions[] = 'Quelles factures sont impayées ?';
        }
        if ($this->navigator->canAccess($user, 'stock.index')) {
            $suggestions[] = 'Quels produits sont bientôt en rupture ?';
        }
        if ($this->navigator->canAccess($user, 'finance.index')) {
            $suggestions[] = 'Quel est le bénéfice du mois dernier ?';
        }

        $suggestions[] = 'Où créer un nouveau devis ?';
        $suggestions[] = 'Comment demander un congé ?';

        return array_slice($suggestions, 0, 5);
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

        return [
            'intent' => 'introduction',
            'answer' => "Je suis l'assistant IVOSPHERE. Je connais vos chiffres (ventes, clients, stock, trésorerie…) et toutes les pages de l'application : demandez-moi un indicateur ou où faire une action."
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
