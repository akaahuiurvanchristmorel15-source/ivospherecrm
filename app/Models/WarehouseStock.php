<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'physical_quantity',
        'reserved_quantity',
        'incoming_quantity',
        'location_aisle',
    ];

    protected function casts(): array
    {
        return [
            'physical_quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'incoming_quantity' => 'integer',
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

    /**
     * Stock disponible réel = Stock physique - Stock réservé
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->physical_quantity - $this->reserved_quantity);
    }

    /**
     * Valeur d'achat immobilisée dans cet entrepôt
     */
    public function getPurchaseValueAttribute(): float
    {
        return (float) ($this->physical_quantity * ($this->product->purchase_price ?? 0));
    }

    /**
     * Valeur potentielle de vente
     */
    public function getSellingValueAttribute(): float
    {
        return (float) ($this->physical_quantity * ($this->product->selling_price ?? 0));
    }
}
