<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type', // caisse, banque, mobile_money, autre
        'domain_id',
        'institution_name',
        'account_number',
        'balance',
        'initial_balance',
        'currency',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'initial_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function transfersOut(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'from_account_id');
    }

    public function transfersIn(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'to_account_id');
    }

    public function cashRegisterSessions(): HasMany
    {
        return $this->hasMany(CashRegisterSession::class);
    }

    public function bankReconciliations(): HasMany
    {
        return $this->hasMany(BankReconciliation::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeByType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    public function getTypeBadgeAttribute(): array
    {
        return match ($this->type) {
            'banque' => ['label' => 'Banque', 'class' => 'bg-blue-50 text-[#0066FF] border-blue-200'],
            'mobile_money' => ['label' => 'Mobile Money', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
            'caisse' => ['label' => 'Caisse', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            default => ['label' => 'Autre', 'class' => 'bg-slate-50 text-slate-700 border-slate-200'],
        };
    }
}
