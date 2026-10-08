<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_schedule_id',
        'date',
        'day_of_week',
        'is_working_day',
        'start_time',
        'end_time',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'day_of_week' => 'integer',
            'is_working_day' => 'boolean',
        ];
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    /**
     * Nom français du jour de la semaine.
     */
    public function getDayNameAttribute(): string
    {
        return match ($this->day_of_week) {
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche',
            default => 'Inconnu',
        };
    }

    public function getDayNameFrAttribute(): string
    {
        return $this->day_name;
    }

    public function getIsWorkingAttribute(): bool
    {
        return (bool) $this->is_working_day;
    }
}
