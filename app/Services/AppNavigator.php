<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Connaissance de toutes les pages du menu pour l'assistant IA général :
 * retrouve la bonne page à partir d'une question, en ne proposant que les pages autorisées.
 */
class AppNavigator
{
    /**
     * Mots sans valeur pour la recherche de page.
     *
     * @var list<string>
     */
    private const STOP_WORDS = [
        'le', 'la', 'les', 'un', 'une', 'des', 'de', 'du', 'd', 'l', 'a', 'au', 'aux', 'et', 'ou', 'en', 'je', 'j', 'me', 'm',
        'mon', 'ma', 'mes', 'on', 'il', 'est', 'sont', 'pour', 'sur', 'dans', 'par', 'qui', 'que', 'quoi', 'comment', 'peux',
        'puis', 'veux', 'voudrais', 'faire', 'faut', 'trouve', 'trouver', 'aller', 'ouvrir', 'acceder', 'page', 'menu', 'ce',
        'cette', 'ces', 'se', 'y', 'ai', 'avoir', 'quel', 'quelle', 'quels', 'quelles', 'moi', 'nous', 'vous', 'svp', 's',
        'il', 'te', 'plait', 'montre', 'affiche', 'voir', 'module', 'pole', 'rubrique', 'section', 'gerer', 'gestion',
    ];

    /**
     * Mots qui signalent une question de navigation (où / comment faire).
     *
     * @var list<string>
     */
    private const NAVIGATION_WORDS = [
        'ou', 'comment', 'ouvrir', 'aller', 'acceder', 'trouver', 'trouve', 'creer', 'ajouter', 'nouveau', 'nouvelle', 'nouvel',
        'enregistrer', 'page', 'menu', 'contient', 'emmene', 'telecharger', 'imprimer', 'saisir', 'demander', 'planifier',
    ];

    /**
     * Pages accessibles à l'utilisateur.
     *
     * @return Collection<int, array{route: string, label: string, module: string, url: string, haystack: string, is_action: bool}>
     */
    public function pagesFor(User $user): Collection
    {
        $keywords = config('navigation.assistant.keywords', []);
        $pages = collect();

        foreach (config('navigation.modules', []) as $module) {
            foreach ($module['items'] as $item) {
                $label = $item['label'] === "Vue d'ensemble" ? $module['label'] : $item['label'];
                $pages->push($this->page($item['route'], $label, $module['label'], $keywords[$item['route']] ?? ''));
            }
        }

        foreach (config('navigation.assistant.pages', []) as $item) {
            $pages->push($this->page($item['route'], $item['label'], $item['module'], $item['keywords']));
        }

        return $pages
            ->filter(fn (?array $page) => $page !== null && $this->canAccess($user, $page['route']))
            ->unique('route')
            ->values();
    }

    /**
     * Pages les plus pertinentes pour une question.
     *
     * @return list<array{label: string, module: string, url: string, score: float}>
     */
    public function search(string $query, User $user, int $limit = 4): array
    {
        $terms = $this->terms($query);

        if ($terms === []) {
            return [];
        }

        $wantsAction = (bool) array_intersect($terms, ['creer', 'ajouter', 'nouveau', 'nouvelle', 'nouvel', 'enregistrer', 'demander', 'planifier', 'telecharger']);

        $scored = $this->pagesFor($user)->map(function (array $page) use ($terms, $wantsAction) {
            $words = explode(' ', $page['haystack']);
            $score = 0.0;

            foreach ($terms as $term) {
                if (in_array($term, $words, true)) {
                    $score += 2;
                } elseif (strlen($term) >= 4 && collect($words)->contains(fn (string $word) => strlen($word) >= 4 && (str_starts_with($word, $term) || str_starts_with($term, $word)))) {
                    $score += 1.2;
                }
            }

            if ($score > 0 && $page['is_action'] === $wantsAction) {
                $score += 1;
            }

            return $page + ['score' => $score];
        });

        return $scored
            ->filter(fn (array $page) => $page['score'] >= 2)
            ->sortByDesc('score')
            ->take($limit)
            ->map(fn (array $page) => ['label' => $page['label'], 'module' => $page['module'], 'url' => $page['url'], 'score' => $page['score']])
            ->values()
            ->all();
    }

    /**
     * Pôle cité dans la question (« que contient le pôle finance ? »).
     *
     * @return array{label: string, pages: list<array{label: string, module: string, url: string}>}|null
     */
    public function moduleMentioned(string $query, User $user): ?array
    {
        $text = ' '.$this->normalize($query).' ';

        foreach (config('navigation.modules', []) as $key => $module) {
            $names = array_unique([$this->normalize($key), $this->normalize($module['label'])]);

            if (collect($names)->contains(fn (string $name) => str_contains($text, " {$name} "))) {
                $pages = $this->pagesFor($user)
                    ->where('module', $module['label'])
                    ->map(fn (array $page) => ['label' => $page['label'], 'module' => $page['module'], 'url' => $page['url']])
                    ->values()
                    ->all();

                return $pages === [] ? null : ['label' => $module['label'], 'pages' => $pages];
            }
        }

        return null;
    }

    public function isNavigationQuestion(string $query): bool
    {
        return (bool) array_intersect(explode(' ', $this->normalize($query)), self::NAVIGATION_WORDS);
    }

    /**
     * Pôles visibles par l'utilisateur, pour l'aide.
     *
     * @return list<string>
     */
    public function modulesFor(User $user): array
    {
        return $this->pagesFor($user)->pluck('module')->unique()->reject(fn (string $module) => $module === 'Général')->values()->all();
    }

    /**
     * @return array{route: string, label: string, module: string, url: string, haystack: string, is_action: bool}|null
     */
    private function page(string $route, string $label, string $module, string $keywords): ?array
    {
        if (! Route::has($route)) {
            return null;
        }

        return [
            'route' => $route,
            'label' => $label,
            'module' => $module,
            'url' => route($route),
            'haystack' => $this->normalize("{$label} {$module} {$keywords}"),
            'is_action' => str_ends_with($route, '.create') || str_contains($route, 'catalog'),
        ];
    }

    /**
     * Applique les middlewares role: et permission: de la route (l'administrateur passe partout).
     */
    public function canAccess(User $user, string $routeName): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $route = Route::getRoutes()->getByName($routeName);

        if (! $route instanceof RoutingRoute) {
            return false;
        }

        foreach ($route->gatherMiddleware() as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }

            [$name, $parameters] = array_pad(explode(':', $middleware, 2), 2, '');
            $values = array_filter(explode(',', $parameters));

            if ($name === 'role' && $values !== [] && ! $user->hasAnyRole($values)) {
                return false;
            }

            if ($name === 'permission' && $values !== [] && ! $user->hasAnyPermission($values)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private function terms(string $query): array
    {
        return array_values(array_unique(array_filter(
            explode(' ', $this->normalize($query)),
            fn (string $word) => strlen($word) > 1 && ! in_array($word, self::STOP_WORDS, true)
        )));
    }

    private function normalize(string $text): string
    {
        return trim((string) preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($text)))));
    }
}
