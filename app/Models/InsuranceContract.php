<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'customer_id',
        'product_id',
        'user_id',
        'partner',
        'start_date',
        'end_date',
        'premium',
        'frequency',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'premium' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(InsuranceProduct::class, 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(InsuranceCommission::class, 'contract_id');
    }
}
