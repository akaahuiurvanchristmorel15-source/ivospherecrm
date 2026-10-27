<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationCriterion extends Model
{
    use HasFactory;

    /**
     * Blocs du référentiel d'évaluation des membres et leur poids dans la note du mois.
     *
     * @var array<string, array{label: string, weight: float}>
     */
    public const BLOCKS = [
        'A' => ['label' => 'Performance et productivité', 'weight' => 0.40],
        'B' => ['label' => 'Compétences', 'weight' => 0.25],
        'C' => ['label' => 'Qualités et valeurs professionnelles', 'weight' => 0.25],
        'D' => ['label' => 'Dynamisme et contribution', 'weight' => 0.10],
    ];

    /**
     * Échelle de notation de chaque critère (n.c. = non concerné, la case n'est pas comptée).
     *
     * @var array<int, array{label: string, meaning: string}>
     */
    public const SCALE = [
        5 => ['label' => 'Excellent', 'meaning' => 'Dépasse nettement ce qui est attendu ; modèle pour l\'équipe'],
        4 => ['label' => 'Bon', 'meaning' => 'Au-dessus de ce qui est attendu ; quelques points à parfaire'],
        3 => ['label' => 'Acceptable', 'meaning' => 'Fait ce qui est attendu du poste'],
        2 => ['label' => 'Faible', 'meaning' => 'En dessous de ce qui est attendu ; doit progresser vite'],
        1 => ['label' => 'Insuffisant', 'meaning' => 'Très en dessous ; manquements graves ou répétés'],
    ];

    /**
     * Notes qui doivent être justifiées par une observation datée.
     *
     * @var list<int>
     */
    public const SCORES_REQUIRING_OBSERVATION = [1, 2, 5];

    protected $fillable = [
        'name',
        'code',
        'block',
        'description',
        'level_1',
        'level_3',
        'level_5',
        'evaluator_groups',
        'weight_percentage',
        'calculation_mode',
        'department',
        'is_mandatory',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'weight_percentage' => 'decimal:2',
            'evaluator_groups' => 'array',
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function scores(): HasMany
    {
        return $this->hasMany(MonthlyEvaluationCriterionScore::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeForDepartment(Builder $query, ?string $department): void
    {
        $query->where(function ($q) use ($department) {
            $q->whereNull('department');
            if ($department) {
                $q->orWhere('department', $department);
            }
        });
    }

    /**
     * Critères du référentiel d'évaluation des membres (rattachés à un bloc), dans l'ordre officiel.
     */
    public function scopeReferential(Builder $query): void
    {
        $query->where('is_active', true)->whereNotNull('block')->orderBy('order');
    }

    /**
     * Indique si un groupe d'évaluateurs peut noter ce critère (l'auto-évaluation porte sur les 17 critères).
     */
    public function isRatableBy(string $evaluatorGroup): bool
    {
        return $evaluatorGroup === EvaluationSheet::GROUP_SELF
            || in_array($evaluatorGroup, $this->evaluator_groups ?? [], true);
    }

    public function isAutomatic(): bool
    {
        return $this->calculation_mode === 'automatic';
    }

    public function isManual(): bool
    {
        return $this->calculation_mode === 'manual';
    }
}
