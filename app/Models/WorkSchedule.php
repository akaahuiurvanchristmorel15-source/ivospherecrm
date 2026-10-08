<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'week_start_date',
        'status',
        'total_working_days',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'week_start_date' => 'date',
            'total_working_days' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(ScheduleDay::class)->orderBy('day_of_week');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Vérifie si le planning ne dépasse pas le nombre maximum de jours travaillés autorisé.
     */
    public function hasExceededMaxDays(int $maxDays = 6): bool
    {
        $workingDaysCount = $this->days()->where('is_working_day', true)->count();

        return $workingDaysCount > $maxDays;
    }

    /**
     * Récupère la programmation pour une date spécifique.
     */
    public function getDayForDate(Carbon|string $date): ?ScheduleDay
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : $date;

        return $this->days()->whereDate('date', $dateStr)->first();
    }

    /**
     * Indique si l'employé est censé travailler à cette date selon ce planning.
     */
    public function isWorkingOn(Carbon|string $date): bool
    {
        $day = $this->getDayForDate($date);

        return $day ? (bool) $day->is_working_day : false;
    }
}
