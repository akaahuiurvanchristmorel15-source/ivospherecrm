<?php

namespace App\Support;

use App\Models\Tenant;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Espace (abonné) courant : celui de l'utilisateur connecté, ou celui forcé explicitement
 * (activation d'un abonnement depuis un webhook, commandes console…).
 */
class TenantContext
{
    private static ?int $forcedTenantId = null;

    public static function id(): ?int
    {
        if (self::$forcedTenantId !== null) {
            return self::$forcedTenantId;
        }

        if (Auth::hasUser()) {
            return Auth::user()->tenant_id;
        }

        return null;
    }

    /**
     * Identifiant à enregistrer sur une nouvelle donnée : l'espace courant, sinon l'espace principal (Ivosphere).
     */
    public static function idForNewRecord(): int
    {
        return self::id() ?? Tenant::MAIN_ID;
    }

    public static function isMain(): bool
    {
        $tenantId = self::id();

        return $tenantId === null || $tenantId === Tenant::MAIN_ID;
    }

    /**
     * Exécute un traitement dans le contexte d'un espace donné.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public static function run(int $tenantId, Closure $callback): mixed
    {
        $previous = self::$forcedTenantId;
        self::$forcedTenantId = $tenantId;

        try {
            return $callback();
        } finally {
            self::$forcedTenantId = $previous;
        }
    }
}
