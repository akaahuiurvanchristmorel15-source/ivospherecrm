<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhysicalInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'warehouse_id',
        'user_id',
        'date',
        'status',
        'notes',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'validated_at' => 'datetime',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PhysicalInventoryItem::class);
    }
}
