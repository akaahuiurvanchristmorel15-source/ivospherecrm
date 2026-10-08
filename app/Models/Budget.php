<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain_id',
        'name',
        'category',
        'amount',
        'spent',
        'period_start',
        'period_end',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'spent' => 'decimal:2',
            'period_start' => 'date',
            'period_end' => 'date',
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'actif');
    }

    public function scopeByDomain(Builder $query, $domainId): void
    {
        if ($domainId) {
            $query->where('domain_id', $domainId);
        }
    }

    public function scopeCurrentPeriod(Builder $query): void
    {
        $today = Carbon::today();
        $query->where('period_start', '<=', $today)->where('period_end', '>=', $today);
    }

    public function getRemainingAttribute(): float
    {
        return $this->amount - $this->spent;
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }
        $percent = ($this->spent / $this->amount) * 100;

        return min(100, max(0, $percent));
    }
}
