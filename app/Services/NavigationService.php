<?php

namespace App\Services;

use App\Support\TenantAccess;
use Illuminate\Support\Facades\Route;

class NavigationService
{
    /**
     * Clé du pôle correspondant à la route courante (ex : "commercial"), ou null.
     */
    public function currentModuleKey(): ?string
    {
        $routeName = Route::currentRouteName() ?? '';
        $prefix = strstr($routeName, '.', true) ?: $routeName;

        return array_key_exists($prefix, config('navigation.modules')) ? $prefix : null;
    }

    /**
     * Pôle courant avec ses sous-pages résolues (URL + état actif).
     *
     * @return array{key: string, label: string, items: list<array{url: string, label: string, active: bool}>}|null
     */
    public function currentModule(): ?array
    {
        $key = $this->currentModuleKey();

        return $key ? $this->module($key) : null;
    }

    /**
     * Pôle donné avec ses sous-pages résolues ; les routes inexistantes sont ignorées.
     *
     * @return array{key: string, label: string, items: list<array{url: string, label: string, active: bool}>}|null
     */
    public function module(string $key): ?array
    {
        $module = config("navigation.modules.{$key}");

        if (! $module) {
            return null;
        }

        $items = collect($module['items'])
            ->filter(fn (array $item): bool => Route::has($item['route']) && TenantAccess::allows($item['route']))
            ->map(fn (array $item): array => [
                'url' => route($item['route']),
                'label' => $item['label'],
                'active' => $this->isActive((array) $item['active']),
            ])
            ->values()
            ->all();

        return ['key' => $key, 'label' => $module['label'], 'items' => $items];
    }

    /**
     * Vrai si la route courante correspond à au moins un motif positif
     * et à aucun motif d'exclusion (préfixés par "!").
     *
     * @param  list<string>  $patterns
     */
    private function isActive(array $patterns): bool
    {
        $request = request();
        $matched = false;

        foreach ($patterns as $pattern) {
            if (str_starts_with($pattern, '!')) {
                if ($request->routeIs(substr($pattern, 1))) {
                    return false;
                }
            } elseif ($request->routeIs($pattern)) {
                $matched = true;
            }
        }

        return $matched;
    }
}
