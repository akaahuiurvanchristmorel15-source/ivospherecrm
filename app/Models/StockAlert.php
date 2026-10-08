<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'min_quantity',
        'current_quantity',
        'status',
        'is_notified',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'min_quantity' => 'integer',
            'current_quantity' => 'integer',
            'is_notified' => 'boolean',
            'notified_at' => 'datetime',
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

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function scopeUnnotified(Builder $query): void
    {
        $query->where('is_notified', false);
    }
}
