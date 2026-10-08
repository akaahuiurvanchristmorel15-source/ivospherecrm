<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'domain_id',
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'department',
        'date_of_birth',
        'gender',
        'address',
        'national_id',
        'hire_date',
        'contract_type',
        'salary',
        'status',
        'photo',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'salary' => 'decimal:2',
            'date_of_birth' => 'date',
            'hire_date' => 'date',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'actif';
    }

    public function getMatriculeAttribute(): ?string
    {
        return $this->employee_code ?? ('EMP-'.str_pad($this->id, 4, '0', STR_PAD_LEFT));
    }

    /**
     * Génère automatiquement un code / matricule employé unique.
     */
    public static function generateEmployeeCode(?string $prefix = 'EMP'): string
    {
        $prefix = $prefix ?: 'EMP';
        $number = 1;

        while (
            self::where('employee_code', sprintf('%s-%04d', $prefix, $number))
                ->orWhere('employee_code', sprintf('%s%04d', $prefix, $number))
                ->exists()
        ) {
            $number++;
        }

        return sprintf('%s-%04d', $prefix, $number);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(EmployeeContract::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function salesTargets(): HasMany
    {
        return $this->hasMany(SalesTarget::class);
    }

    public function dailyTaskSheets(): HasMany
    {
        return $this->hasMany(DailyTaskSheet::class);
    }

    public function workSchedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class);
    }

    public function monthlyEvaluations(): HasMany
    {
        return $this->hasMany(MonthlyEvaluation::class);
    }

    public function dailyEvaluationScores(): HasMany
    {
        return $this->hasMany(DailyEvaluationScore::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(EmployeeGoal::class);
    }

    public function improvementPlans(): HasMany
    {
        return $this->hasMany(PerformanceImprovementPlan::class);
    }

    public function todayAttendance(): HasOne
    {
        return $this->hasOne(Attendance::class)->whereDate('date', today());
    }

    public function currentWeekSchedule(): ?WorkSchedule
    {
        $monday = now()->startOfWeek()->toDateString();

        return $this->workSchedules()
            ->whereDate('week_start_date', $monday)
            ->first();
    }

    public function activeSalesTarget(): ?SalesTarget
    {
        return $this->salesTargets()
            ->where('status', 'en_cours')
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date', '>=', now()->toDateString())
            ->latest()
            ->first();
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'actif');
    }

    public function scopeByDomain(Builder $query, int $domainId): void
    {
        $query->where('domain_id', $domainId);
    }
}
