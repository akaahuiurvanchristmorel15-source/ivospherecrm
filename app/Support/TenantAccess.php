<?php

namespace App\Support;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Pages accessibles selon le plan de l'espace abonné (l'espace principal Ivosphere a tout).
 */
class TenantAccess
{
    /** @var array<int, Tenant|null> */
    private static array $tenants = [];

    public static function allows(string $routeName, ?User $user = null): bool
    {
        $user ??= Auth::user();

        if (! $user || $user->belongsToMainTenant()) {
            return true;
        }

        $tenant = self::tenantOf($user);
        if (! $tenant) {
            return false;
        }

        $patterns = $tenant->hasActiveSubscription()
            ? self::allowedRoutes($tenant->modules())
            : config('subscriptions.billing_routes');

        return Str::is($patterns, $routeName);
    }

    public static function tenantOf(User $user): ?Tenant
    {
        return self::$tenants[$user->tenant_id] ??= Tenant::with('plan')->find($user->tenant_id);
    }

    /**
     * @param  list<string>  $modules
     * @return list<string>
     */
    public static function allowedRoutes(array $modules): array
    {
        $catalogue = config('subscriptions.modules');
        $routes = config('subscriptions.always_allowed_routes');

        foreach ($modules as $module) {
            if (($catalogue[$module]['ready'] ?? false) === true) {
                $routes = [...$routes, ...$catalogue[$module]['routes']];
            }
        }

        return $routes;
    }

    /**
     * Oublie les espaces mémorisés (après un changement d'abonnement, et entre deux tests).
     */
    public static function flush(): void
    {
        self::$tenants = [];
    }
}
