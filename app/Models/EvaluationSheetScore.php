<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Note d'un critère (1 à 5, ou null pour « non concerné ») sur une fiche d'évaluateur.
 */
class EvaluationSheetScore extends Model
{
    protected $fillable = [
        'evaluation_sheet_id',
        'evaluation_criterion_id',
        'score',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    public function sheet(): BelongsTo
    {
        return $this->belongsTo(EvaluationSheet::class, 'evaluation_sheet_id');
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriterion::class, 'evaluation_criterion_id');
    }
}
