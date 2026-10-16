<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Scopes\TenantScope;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    /**
     * Paramètres de l'espace courant ; ceux d'Ivosphere pour un visiteur non connecté.
     *
     * @return Builder<static>
     */
    public static function forCurrentTenant(): Builder
    {
        return static::withoutGlobalScope(TenantScope::class)->where('tenant_id', TenantContext::idForNewRecord());
    }

    /**
     * Get a setting by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::forCurrentTenant()->where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int) $setting->value,
            'float' => (float) $setting->value,
            'json' => json_decode($setting->value, true) ?? $default,
            default => $setting->value,
        };
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): static
    {
        $formattedValue = is_array($value) ? json_encode($value) : (string) $value;

        return static::forCurrentTenant()->updateOrCreate(
            ['key' => $key, 'tenant_id' => TenantContext::idForNewRecord()],
            [
                'value' => $formattedValue,
                'group' => $group,
                'type' => $type,
            ]
        );
    }
}
