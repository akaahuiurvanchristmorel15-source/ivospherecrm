<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id', 'subscription_plan_id', 'reference', 'billing_cycle', 'amount', 'currency',
        'status', 'payment_provider', 'payment_transaction_id', 'payment_method', 'payment_payload',
        'paid_at', 'starts_at', 'ends_at', 'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'payment_payload' => 'array',
            'paid_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Subscription $subscription) {
            if (empty($subscription->reference)) {
                $subscription->reference = 'ABN-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function billingCycleLabel(): string
    {
        return $this->billing_cycle === 'yearly' ? 'Annuel' : 'Mensuel';
    }
}
