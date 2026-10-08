<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentRental extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'equipment_id',
        'customer_id',
        'user_id',
        'start_date',
        'end_date',
        'returned_at',
        'daily_rate',
        'deposit',
        'penalty',
        'condition_before',
        'condition_after',
        'status',
        'total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'returned_at' => 'date',
            'daily_rate' => 'decimal:2',
            'deposit' => 'decimal:2',
            'penalty' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calculateTotal(): float
    {
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);
        $days = max(1, $start->diffInDays($end));

        return (float) ($this->daily_rate * $days) + (float) $this->penalty;
    }
}
