<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRegisterSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'financial_account_id',
        'user_id',
        'opened_at',
        'closed_at',
        'opening_balance',
        'total_inflow',
        'total_outflow',
        'theoretical_balance',
        'real_balance',
        'discrepancy',
        'discrepancy_reason',
        'status',
        'validated_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_balance' => 'decimal:2',
            'total_inflow' => 'decimal:2',
            'total_outflow' => 'decimal:2',
            'theoretical_balance' => 'decimal:2',
            'real_balance' => 'decimal:2',
            'discrepancy' => 'decimal:2',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'financial_account_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'ouverte';
    }
}
