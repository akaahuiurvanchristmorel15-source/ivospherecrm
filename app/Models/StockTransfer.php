<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'product_id',
        'source_warehouse_id',
        'destination_warehouse_id',
        'user_id',
        'quantity',
        'reason',
        'status',
        'shipped_at',
        'received_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'shipped_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'receptionne' => ['label' => 'Réceptionné', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'expedie' => ['label' => 'Expédié', 'class' => 'bg-blue-50 text-[#0066FF] border-blue-200'],
            'valide' => ['label' => 'Validé', 'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
            'annule' => ['label' => 'Annulé', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
            'brouillon' => ['label' => 'Brouillon', 'class' => 'bg-slate-100 text-[#64748B] border-[#E2E8F0]'],
            default => ['label' => 'En attente', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
        };
    }
}
