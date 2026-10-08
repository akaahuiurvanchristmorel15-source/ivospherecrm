<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'monthly_evaluation_id',
        'assigned_by',
        'title',
        'description',
        'start_date',
        'due_date',
        'progress_pct',
        'status',
        'result_notes',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'progress_pct' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function monthlyEvaluation(): BelongsTo
    {
        return $this->belongsTo(MonthlyEvaluation::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'atteint';
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', ['a_faire', 'en_cours']);
    }

    public function scopeAchieved(Builder $query): void
    {
        $query->where('status', 'atteint');
    }
}
