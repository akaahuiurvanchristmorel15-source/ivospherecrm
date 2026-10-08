<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'amount',
        'rate',
        'date',
        'status',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'rate' => 'decimal:2',
            'date' => 'date',
            'paid_at' => 'date',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(InsuranceContract::class, 'contract_id');
    }
}
