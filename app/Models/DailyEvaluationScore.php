<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyEvaluationScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'monthly_evaluation_id',
        'date',
        'is_working_day',
        'punctuality_score',
        'punctuality_notes',
        'tasks_assigned_count',
        'tasks_validated_count',
        'tasks_score',
        'teamwork_score',
        'teamwork_comment',
        'evaluated_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_working_day' => 'boolean',
            'punctuality_score' => 'decimal:2',
            'tasks_assigned_count' => 'integer',
            'tasks_validated_count' => 'integer',
            'tasks_score' => 'decimal:2',
            'teamwork_score' => 'decimal:2',
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

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    /**
     * Score total obtenu pour cette journée (sur 0.66 max).
     */
    public function totalDailyScore(): float
    {
        return (float) $this->punctuality_score + (float) $this->tasks_score + (float) $this->teamwork_score;
    }
}
