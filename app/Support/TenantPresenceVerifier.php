<?php

namespace App\Support;

use Illuminate\Validation\DatabasePresenceVerifier;

/**
 * Les règles « exists » et « unique » sur les tables séparées par espace ne regardent que
 * les données de l'espace courant (un abonné ne peut pas référencer les données d'un autre).
 */
class TenantPresenceVerifier extends DatabasePresenceVerifier
{
    protected function table($table)
    {
        $query = parent::table($table);
        $tenantId = TenantContext::id();

        if ($tenantId !== null && in_array($table, config('subscriptions.tenant_validated_tables', []), true)) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }
}
