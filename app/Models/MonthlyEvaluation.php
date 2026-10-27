<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyEvaluation extends Model
{
    use HasFactory;

    public const TRIAL_YEAR = 2026;

    public const TRIAL_MONTH = 10;

    public const CONTESTATION_DELAY_HOURS = 48;

    protected $fillable = [
        'employee_id',
        'year',
        'month',
        'is_trial',
        'evaluation_date',
        'total_working_days',
        'max_possible_points',
        'punctuality_points',
        'tasks_points',
        'teamwork_points',
        'raw_score',
        'final_score_30',
        'final_score_20',
        'block_a_score',
        'block_b_score',
        'block_c_score',
        'block_d_score',
        'appreciation',
        'score_progress',
        'status',
        'workflow_step',
        'is_locked',
        'locked_at',
        'locked_by',
        'evaluator_id',
        'manager_comment',
        'strengths',
        'weaknesses',
        'recommendations',
        'future_goals',
        'manager_signature',
        'manager_signed_at',
        'employee_signature',
        'employee_signed_at',
        'contestation',
        'contested_at',
        'contestation_response',
        'contestation_answered_at',
        'contestation_answered_by',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'is_trial' => 'boolean',
            'evaluation_date' => 'date',
            'total_working_days' => 'integer',
            'max_possible_points' => 'decimal:2',
            'punctuality_points' => 'decimal:2',
            'tasks_points' => 'decimal:2',
            'teamwork_points' => 'decimal:2',
            'raw_score' => 'decimal:2',
            'final_score_30' => 'decimal:2',
            'final_score_20' => 'decimal:2',
            'block_a_score' => 'decimal:2',
            'block_b_score' => 'decimal:2',
            'block_c_score' => 'decimal:2',
            'block_d_score' => 'decimal:2',
            'score_progress' => 'decimal:2',
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
            'manager_signed_at' => 'datetime',
            'employee_signed_at' => 'datetime',
            'contested_at' => 'datetime',
            'contestation_answered_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function contestationAnsweredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contestation_answered_by');
    }

    public function dailyScores(): HasMany
    {
        return $this->hasMany(DailyEvaluationScore::class)->orderBy('date');
    }

    public function criteriaScores(): HasMany
    {
        return $this->hasMany(MonthlyEvaluationCriterionScore::class);
    }

    public function sheets(): HasMany
    {
        return $this->hasMany(EvaluationSheet::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(EmployeeGoal::class);
    }

    public function improvementPlans(): HasMany
    {
        return $this->hasMany(PerformanceImprovementPlan::class);
    }

    /**
     * Octobre 2026 est le mois d'essai du référentiel : la note est donnée et expliquée, sans conséquence.
     */
    public static function isTrialMonth(int $year, int $month): bool
    {
        return $year === self::TRIAL_YEAR && $month === self::TRIAL_MONTH;
    }

    /**
     * Recalcule la note du mois selon le référentiel d'évaluation des membres :
     * 1. note de chaque critère = moyenne pondérée des catégories d'évaluateurs qui l'ont noté
     *    (Direction 40, RH 20, responsables de fonction 25, pairs 15), les poids étant ramenés à 100 % ;
     * 2. note de chaque bloc = moyenne des critères notés du bloc, sur 5 ;
     * 3. note du mois sur 20 = 4 × (0,40 A + 0,25 B + 0,25 C + 0,10 D).
     */
    public function recalculateScores(): self
    {
        $criteria = EvaluationCriterion::referential()->get();
        $sheets = $this->sheets()->with('scores')->get();
        $assignedTasks = (int) $this->dailyScores()->sum('tasks_assigned_count');
        $validatedTasks = (int) $this->dailyScores()->sum('tasks_validated_count');

        $criterionScoresByBlock = [];

        foreach ($criteria as $criterion) {
            $scoresByCategory = [];

            foreach ($sheets as $sheet) {
                if ($sheet->category === EvaluationSheet::GROUP_SELF || ! $criterion->isRatableBy($sheet->evaluator_group)) {
                    continue;
                }

                $score = $sheet->scores->firstWhere('evaluation_criterion_id', $criterion->id)?->score;

                if ($score !== null) {
                    $scoresByCategory[$sheet->category][] = $score;
                }
            }

            $justification = null;

            // A1 est calculé à partir des bilans quotidiens, sauf si la Direction l'a noté elle-même
            if ($criterion->isAutomatic() && $scoresByCategory === [] && $assignedTasks > 0) {
                $meritPercentage = (int) round($validatedTasks / $assignedTasks * 100);
                $scoresByCategory['direction'][] = self::meritToScore($meritPercentage);
                $justification = "Mérite moyen du mois : {$meritPercentage} % ({$validatedTasks} tâches validées sur {$assignedTasks}).";
            }

            $criterionScore = self::weightedCriterionScore($scoresByCategory);

            if ($criterionScore === null) {
                $this->criteriaScores()->where('evaluation_criterion_id', $criterion->id)->delete();

                continue;
            }

            $this->criteriaScores()->updateOrCreate(
                ['evaluation_criterion_id' => $criterion->id],
                [
                    'score' => $criterionScore,
                    'max_score' => 5,
                    'weighted_score' => round($criterionScore * (float) $criterion->weight_percentage / 5, 2),
                    'justification' => $justification,
                ]
            );

            $criterionScoresByBlock[$criterion->block][] = $criterionScore;
        }

        $blockScores = [];
        foreach (array_keys(EvaluationCriterion::BLOCKS) as $block) {
            $blockScores[$block] = isset($criterionScoresByBlock[$block])
                ? round(array_sum($criterionScoresByBlock[$block]) / count($criterionScoresByBlock[$block]), 2)
                : null;
        }

        $monthScore = self::monthScoreFromBlocks($blockScores);

        $this->block_a_score = $blockScores['A'];
        $this->block_b_score = $blockScores['B'];
        $this->block_c_score = $blockScores['C'];
        $this->block_d_score = $blockScores['D'];
        $this->final_score_20 = $monthScore ?? 0;
        $this->final_score_30 = round(($monthScore ?? 0) * 1.5, 2);
        $this->appreciation = $monthScore === null ? null : self::computeAppreciation($monthScore);

        $previousEvaluation = $this->previousEvaluation();
        $this->score_progress = $previousEvaluation && $monthScore !== null && $previousEvaluation->isScored()
            ? round($monthScore - (float) $previousEvaluation->final_score_20, 2)
            : 0;

        $this->save();

        return $this;
    }

    /**
     * Moyenne pondérée des catégories d'évaluateurs ; si une catégorie n'a pas noté, les poids des autres sont ramenés à 100 %.
     *
     * @param  array<string, list<int|float>>  $scoresByCategory
     */
    public static function weightedCriterionScore(array $scoresByCategory): ?float
    {
        $weightedSum = 0.0;
        $totalWeight = 0;

        foreach ($scoresByCategory as $category => $scores) {
            $weight = EvaluationSheet::CATEGORIES[$category]['weight'] ?? 0;

            if ($weight === 0 || $scores === []) {
                continue;
            }

            $weightedSum += $weight * array_sum($scores) / count($scores);
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : null;
    }

    /**
     * Note du mois sur 20 à partir des notes de bloc (sur 5). Un bloc sans aucune note n'est pas compté.
     *
     * @param  array<string, float|null>  $blockScores
     */
    public static function monthScoreFromBlocks(array $blockScores): ?float
    {
        $weightedSum = 0.0;
        $totalWeight = 0.0;

        foreach (EvaluationCriterion::BLOCKS as $block => $definition) {
            if (($blockScores[$block] ?? null) === null) {
                continue;
            }

            $weightedSum += $definition['weight'] * $blockScores[$block];
            $totalWeight += $definition['weight'];
        }

        return $totalWeight > 0 ? round(4 * $weightedSum / $totalWeight, 2) : null;
    }

    /**
     * Conversion du mérite moyen du mois (A1) en note : ≥ 85 % = 5, 70–84 % = 4, 55–69 % = 3, 40–54 % = 2, moins de 40 % = 1.
     */
    public static function meritToScore(float $meritPercentage): int
    {
        return match (true) {
            $meritPercentage >= 85 => 5,
            $meritPercentage >= 70 => 4,
            $meritPercentage >= 55 => 3,
            $meritPercentage >= 40 => 2,
            default => 1,
        };
    }

    /**
     * Appréciation du référentiel selon la note sur 20.
     */
    public static function computeAppreciation(float $score20): string
    {
        return match (true) {
            $score20 >= 16.00 => 'Excellent',
            $score20 >= 14.00 => 'Très bien',
            $score20 >= 12.00 => 'Bien',
            $score20 >= 10.00 => 'Passable',
            default => 'Insuffisant',
        };
    }

    /**
     * Ce qui suit la note du mois, selon le référentiel.
     */
    public static function followUpFor(float $score20): string
    {
        return match (true) {
            $score20 >= 16.00 => 'Félicitations écrites ; candidat au titre de membre du mois et aux récompenses du trimestre',
            $score20 >= 14.00 => 'Félicitations ; candidat aux récompenses du trimestre',
            $score20 >= 12.00 => 'Encouragements ; un point fort et un point à progresser fixés',
            $score20 >= 10.00 => 'Plan de progrès sur 2 points précis',
            default => 'Plan de progrès de 30 jours, avec un référent et une formation',
        };
    }

    /**
     * Indique si au moins un critère a été noté (sinon la note du mois n'existe pas encore).
     */
    public function isScored(): bool
    {
        return ! empty($this->attributes['appreciation']);
    }

    public function getAppreciationAttribute(): string
    {
        return $this->attributes['appreciation'] ?? 'Non évalué';
    }

    public function getFollowUpAttribute(): ?string
    {
        return $this->isScored() ? self::followUpFor((float) $this->final_score_20) : null;
    }

    public function getAppreciationBadgeClassAttribute(): string
    {
        return match ($this->appreciation) {
            'Excellent' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'Très bien' => 'bg-blue-100 text-blue-800 border-blue-300',
            'Bien' => 'bg-sky-100 text-sky-800 border-sky-300',
            'Passable' => 'bg-amber-100 text-amber-800 border-amber-300',
            'Insuffisant' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-slate-100 text-slate-600 border-slate-300',
        };
    }

    public function previousEvaluation(): ?self
    {
        $previousMonth = $this->month === 1 ? 12 : $this->month - 1;
        $previousYear = $this->month === 1 ? $this->year - 1 : $this->year;

        return self::where('employee_id', $this->employee_id)
            ->where('year', $previousYear)
            ->where('month', $previousMonth)
            ->first();
    }

    /**
     * Deux mois de suite sous 10 (hors mois d'essai) : entretien formel et mesures prévues par le règlement intérieur.
     */
    public function requiresFormalInterview(): bool
    {
        if ($this->is_trial || ! $this->isScored() || (float) $this->final_score_20 >= 10) {
            return false;
        }

        $previousEvaluation = $this->previousEvaluation();

        return $previousEvaluation !== null
            && ! $previousEvaluation->is_trial
            && $previousEvaluation->isScored()
            && (float) $previousEvaluation->final_score_20 < 10;
    }

    /**
     * Fin du délai de contestation : 48 heures après la clôture de la note.
     */
    public function contestationDeadline(): ?Carbon
    {
        return $this->locked_at?->copy()->addHours(self::CONTESTATION_DELAY_HOURS);
    }

    public function canBeContested(): bool
    {
        return $this->is_locked
            && $this->contested_at === null
            && $this->contestationDeadline()?->isFuture() === true;
    }

    /**
     * Verrouille l'évaluation (empêche toute modification ultérieure sans déverrouillage formel).
     */
    public function lock(int $userId): void
    {
        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $userId,
            'workflow_step' => 'verrouille',
            'status' => 'cloture',
        ]);
    }

    /**
     * Déverrouille l'évaluation pour révision autorisée.
     */
    public function unlock(): void
    {
        $this->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
            'workflow_step' => 'valide_rh',
        ]);
    }

    /**
     * Nom français du mois.
     */
    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];

        return $months[$this->month] ?? 'Mois '.$this->month;
    }
}
