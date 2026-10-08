<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'name',
        'type',
        'party_name',
        'party_type',
        'party_id',
        'start_date',
        'end_date',
        'amount',
        'currency',
        'status',
        'document_path',
        'alert_days',
        'notes',
        'user_id',
        'domain_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'amount' => 'decimal:2',
            'alert_days' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (! $this->end_date) {
            return null;
        }

        return (int) Carbon::now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false);
    }

    public function isExpiringSoon(): bool
    {
        $days = $this->days_remaining;

        return $days !== null && $days >= 0 && $days <= 30;
    }
}
