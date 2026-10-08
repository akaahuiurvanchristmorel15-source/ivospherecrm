<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'check_in_at',
        'check_out_at',
        'check_out_type',
        'latitude',
        'longitude',
        'accuracy_meters',
        'distance_meters',
        'location_verified',
        'qr_type',
        'device_fingerprint',
        'device_info',
        'ip_address',
        'delay_minutes',
        'punctuality_score',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'location_verified' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'accuracy_meters' => 'decimal:2',
            'distance_meters' => 'decimal:2',
            'delay_minutes' => 'integer',
            'punctuality_score' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeForDate(Builder $query, $date): void
    {
        $query->where('date', $date);
    }

    public function scopeToday(Builder $query): void
    {
        $query->whereDate('date', today());
    }

    public function scopePendingCheckout(Builder $query): void
    {
        $query->whereNotNull('check_in')->whereNull('check_out');
    }

    public function scopeForMonth(Builder $query, $year, $month): void
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeLate(Builder $query): void
    {
        $query->where(function (Builder $q) {
            $q->where('status', 'retard')
                ->orWhere('delay_minutes', '>', 0);
        });
    }

    /**
     * Indique si la sortie a été clôturée automatiquement par le système.
     */
    public function isAutomaticCheckout(): bool
    {
        return $this->check_out_type === 'automatic';
    }

    /**
     * Indique si l'employé a déjà pointé son arrivée.
     */
    public function isCheckedIn(): bool
    {
        return ! empty($this->check_in) || ! empty($this->check_in_at);
    }

    /**
     * Indique si l'employé a déjà clôturé sa journée (départ).
     */
    public function isCheckedOut(): bool
    {
        return ! empty($this->check_out) || ! empty($this->check_out_at);
    }

    public function getCheckInTimeAttribute(): ?string
    {
        return $this->check_in ?? ($this->check_in_at ? $this->check_in_at->format('H:i:s') : null);
    }

    public function getCheckOutTimeAttribute(): ?string
    {
        return $this->check_out ?? ($this->check_out_at ? $this->check_out_at->format('H:i:s') : null);
    }

    public function getIsLateAttribute(): bool
    {
        return $this->status === 'retard' || ($this->delay_minutes ?? 0) > 0;
    }

    public function getLateMinutesAttribute(): int
    {
        return (int) ($this->delay_minutes ?? 0);
    }
}
