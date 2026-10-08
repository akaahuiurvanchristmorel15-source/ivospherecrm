<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicalInventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'physical_inventory_id',
        'product_id',
        'system_quantity',
        'real_quantity',
        'discrepancy',
        'unit_cost',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'system_quantity' => 'integer',
            'real_quantity' => 'integer',
            'discrepancy' => 'integer',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(PhysicalInventory::class, 'physical_inventory_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
