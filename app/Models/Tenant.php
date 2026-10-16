<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Espace d'une entreprise abonnée. L'espace n° 1 est Ivosphere (espace principal).
 */
class Tenant extends Model
{
    public const MAIN_ID = 1;

    protected $fillable = [
        'name', 'slug', 'is_main', 'email', 'phone', 'city', 'country', 'address',
        'subscription_plan_id', 'status', 'subscription_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'subscription_ends_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function latestSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * L'espace peut être utilisé : abonnement actif et non échu.
     */
    public function hasActiveSubscription(): bool
    {
        if ($this->is_main) {
            return true;
        }

        return $this->status === 'active'
            && $this->subscription_ends_at !== null
            && $this->subscription_ends_at->isFuture();
    }

    /**
     * Modules inclus dans le plan de l'espace (tous pour l'espace principal).
     *
     * @return list<string>
     */
    public function modules(): array
    {
        if ($this->is_main) {
            return array_keys(config('subscriptions.modules'));
        }

        return $this->plan?->modules ?? [];
    }

    public function hasModule(string $module): bool
    {
        return in_array($module, $this->modules(), true);
    }

    /**
     * Libellé et couleur (variante de badge) de l'état de l'espace.
     *
     * @return array{label: string, variant: string}
     */
    public function statusBadge(): array
    {
        return match (true) {
            $this->status === 'pending' => ['label' => 'En attente de paiement', 'variant' => 'warning'],
            $this->status === 'suspended' => ['label' => 'Suspendu', 'variant' => 'danger'],
            $this->hasActiveSubscription() => ['label' => 'Actif', 'variant' => 'success'],
            default => ['label' => 'Expiré', 'variant' => 'danger'],
        };
    }

    public function daysRemaining(): int
    {
        if (! $this->subscription_ends_at || $this->subscription_ends_at->isPast()) {
            return 0;
        }

        return (int) ceil(now()->diffInDays($this->subscription_ends_at));
    }
}
