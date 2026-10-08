<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'product_id',
        'warehouse_id',
        'supplier_id',
        'user_id',
        'quantity',
        'estimated_unit_price',
        'urgency',
        'reason',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'estimated_unit_price' => 'decimal:2',
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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getEstimatedTotalAttribute(): float
    {
        return (float) ($this->quantity * $this->estimated_unit_price);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'receptionne' => ['label' => 'Réceptionné', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'commande_passee' => ['label' => 'Commandé', 'class' => 'bg-blue-50 text-[#0066FF] border-blue-200'],
            'approuve_achat' => ['label' => 'Validé Finance/Achat', 'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
            'rejete' => ['label' => 'Rejeté', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
            default => ['label' => 'En attente Approbation', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
        };
    }
}
