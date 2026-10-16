<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'code', 'family', 'name', 'tagline', 'modules', 'price_monthly', 'price_yearly',
        'max_users', 'is_available', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'modules' => 'array',
            'price_monthly' => 'integer',
            'price_yearly' => 'integer',
            'max_users' => 'integer',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order');
    }

    public function priceFor(string $billingCycle): int
    {
        return $billingCycle === 'yearly' ? $this->price_yearly : $this->price_monthly;
    }

    /**
     * Modules du plan avec leur libellé et leur description.
     *
     * @return list<array{key: string, label: string, description: string, ready: bool}>
     */
    public function moduleDetails(): array
    {
        $catalogue = config('subscriptions.modules');

        return collect($this->modules)
            ->filter(fn (string $key) => isset($catalogue[$key]))
            ->map(fn (string $key) => [
                'key' => $key,
                'label' => $catalogue[$key]['label'],
                'description' => $catalogue[$key]['description'],
                'ready' => (bool) $catalogue[$key]['ready'],
            ])
            ->values()
            ->all();
    }

    /**
     * Le plan peut être souscrit : proposé à la vente et tous ses modules sont prêts.
     */
    public function canBeSubscribed(): bool
    {
        return $this->is_active
            && $this->is_available
            && collect($this->moduleDetails())->every(fn (array $module) => $module['ready']);
    }
}
