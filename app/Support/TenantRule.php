<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

/**
 * Règles de validation limitées à l'espace courant.
 */
class TenantRule
{
    /**
     * Valeur unique dans l'espace courant (deux abonnés peuvent utiliser le même code).
     */
    public static function unique(string $table, string $column, mixed $ignoreId = null): Unique
    {
        $rule = Rule::unique($table, $column)->where('tenant_id', TenantContext::idForNewRecord());

        return $ignoreId !== null ? $rule->ignore($ignoreId) : $rule;
    }
}
