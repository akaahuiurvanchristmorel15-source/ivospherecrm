<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'domain_id',
        'name',
        'category',
        'serial_number',
        'condition',
        'daily_rate',
        'value',
        'status',
        'is_available',
        'image',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'daily_rate' => 'decimal:2',
            'value' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(EquipmentRental::class, 'equipment_id');
    }
}
