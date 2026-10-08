<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_number',
        'serial_number',
        'imei',
        'manufacturing_date',
        'expiration_date',
        'warranty_months',
        'quantity',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'manufacturing_date' => 'date',
            'expiration_date' => 'date',
            'warranty_months' => 'integer',
            'quantity' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (! $this->expiration_date) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->expiration_date, false);
    }
}
