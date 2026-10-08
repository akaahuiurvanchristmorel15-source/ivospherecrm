<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceImprovementPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'monthly_evaluation_id',
        'created_by',
        'supervisor_id',
        'title',
        'problem_identified',
        'target_objective',
        'corrective_actions',
        'start_date',
        'end_date',
        'progress_pct',
        'status',
        'final_assessment',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'progress_pct' => 'integer',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'en_cours');
    }
}
