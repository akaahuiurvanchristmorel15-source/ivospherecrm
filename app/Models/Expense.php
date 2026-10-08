<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'domain_id',
        'user_id',
        'cash_register_id',
        'financial_account_id',
        'supplier_id',
        'category',
        'description',
        'amount',
        'date',
        'payment_method',
        'status',
        'required_approval_level',
        'approved_by',
        'receipt_path',
        'notes',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeByDomain(Builder $query, $domainId): void
    {
        if ($domainId) {
            $query->where('domain_id', $domainId);
        }
    }

    public function scopeForDateRange(Builder $query, $startDate, $endDate): void
    {
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }
    }

    public function scopeByStatus(Builder $query, $status): void
    {
        if ($status) {
            $query->where('status', $status);
        }
    }
}
