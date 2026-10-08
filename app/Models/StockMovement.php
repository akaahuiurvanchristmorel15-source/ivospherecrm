<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'domain_id',
        'user_id',
        'type',
        'reason_motif',
        'quantity',
        'stock_before',
        'stock_after',
        'unit_cost',
        'reference',
        'batch_number',
        'destination_warehouse_id',
        'order_id',
        'notes',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'quantity' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeByType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    public function scopeByWarehouse(Builder $query, int $warehouseId): void
    {
        $query->where('warehouse_id', $warehouseId);
    }

    public function scopeByDomain(Builder $query, int $domainId): void
    {
        $query->where('domain_id', $domainId);
    }

    public function scopeForDateRange(Builder $query, string $start, string $end): void
    {
        $query->whereBetween('date', [$start, $end]);
    }
}
