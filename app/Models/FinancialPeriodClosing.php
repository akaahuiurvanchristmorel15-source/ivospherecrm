<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPeriodClosing extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'period_label',
        'closed_by',
        'closed_at',
        'is_locked',
        'total_revenues',
        'total_expenses',
        'net_result',
        'closing_cash_balance',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'closed_at' => 'datetime',
            'is_locked' => 'boolean',
            'total_revenues' => 'decimal:2',
            'total_expenses' => 'decimal:2',
            'net_result' => 'decimal:2',
            'closing_cash_balance' => 'decimal:2',
        ];
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
