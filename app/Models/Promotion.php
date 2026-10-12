<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'min_amount',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function isValid(?float $cartTotal = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $today = now()->startOfDay();

        if ($this->start_date && $today->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $today->gt($this->end_date)) {
            return false;
        }

        if ($cartTotal !== null && $cartTotal < (float) $this->min_amount) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if (! $this->isValid($subtotal)) {
            return 0.0;
        }

        if ($this->type === 'percent') {
            return round(($subtotal * (float) $this->value) / 100, 2);
        }

        return min((float) $this->value, $subtotal);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Clients auxquels le code promo est réservé (aucun = code utilisable par tous).
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class)
            ->withPivot(['used_at', 'order_id'])
            ->withTimestamps();
    }

    /**
     * Le code est-il réservé à des clients précis ?
     */
    public function isRestricted(): bool
    {
        return $this->customers()->exists();
    }

    /**
     * Le client peut-il utiliser ce code ? (code public, ou attribué au client et pas encore utilisé)
     */
    public function canBeUsedBy(?Customer $customer): bool
    {
        if (! $this->isRestricted()) {
            return true;
        }

        if ($customer === null) {
            return false;
        }

        return $this->customers()
            ->whereKey($customer->id)
            ->wherePivotNull('used_at')
            ->exists();
    }

    /**
     * Marque le code comme utilisé par le client. Retourne false s'il avait déjà été utilisé.
     */
    public function markAsUsedBy(Customer $customer, Order $order): bool
    {
        return $this->customers()
            ->wherePivotNull('used_at')
            ->updateExistingPivot($customer->id, ['used_at' => now(), 'order_id' => $order->id]) > 0;
    }
}
