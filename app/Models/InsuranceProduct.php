<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'partner',
        'type',
        'description',
        'premium_range',
        'commission_rate',
        'status',
        'conditions',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
        ];
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(InsuranceContract::class, 'product_id');
    }
}
