<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyEvaluationCriterionScore extends Model
{
    use HasFactory;

    protected $table = 'monthly_evaluation_criteria_scores';

    protected $fillable = [
        'monthly_evaluation_id',
        'evaluation_criterion_id',
        'score',
        'max_score',
        'weighted_score',
        'justification',
        'comments',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'weighted_score' => 'decimal:2',
        ];
    }

    public function monthlyEvaluation(): BelongsTo
    {
        return $this->belongsTo(MonthlyEvaluation::class);
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriterion::class, 'evaluation_criterion_id');
    }
}
