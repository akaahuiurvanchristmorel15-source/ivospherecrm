<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'supplier_id',
        'domain_id',
        'user_id',
        'date',
        'due_date',
        'total_amount',
        'paid_amount',
        'status', // non_payee, partielle, payee
        'attachment_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'due_date' => 'date',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'payee' && $this->due_date && $this->due_date->isPast();
    }

    public function scopeUnpaid(Builder $query): void
    {
        $query->whereIn('status', ['non_payee', 'partielle']);
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->unpaid()->where('due_date', '<', now()->toDateString());
    }
}
