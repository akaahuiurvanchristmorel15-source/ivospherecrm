<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'year',
        'month',
        'evaluation_date',
        'total_working_days',
        'max_possible_points',
        'punctuality_points',
        'tasks_points',
        'teamwork_points',
        'raw_score',
        'final_score_30',
        'final_score_20',
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
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'evaluation_date' => 'date',
            'total_working_days' => 'integer',
            'max_possible_points' => 'decimal:2',
            'punctuality_points' => 'decimal:2',
            'tasks_points' => 'decimal:2',
            'teamwork_points' => 'decimal:2',
            'raw_score' => 'decimal:2',
            'final_score_30' => 'decimal:2',
            'final_score_20' => 'decimal:2',
            'score_progress' => 'decimal:2',
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
            'manager_signed_at' => 'datetime',
            'employee_signed_at' => 'datetime',
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

    public function dailyScores(): HasMany
    {
        return $this->hasMany(DailyEvaluationScore::class)->orderBy('date');
    }

    public function criteriaScores(): HasMany
    {
        return $this->hasMany(MonthlyEvaluationCriterionScore::class);
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
     * Recalcule automatiquement le score global et la note normalisée sur 30 points.
     */
    /**
     * Recalcule automatiquement le score global (Moyenne des 10 critères / 20)
     * et synchronise l'appréciation automatique ainsi que le score normalisé sur 30 points.
     */
    public function recalculateScores(): self
    {
        $settings = HrSetting::current();

        // 1. Calcul via les 10 matières / critères s'ils existent
        $criteriaScores = $this->criteriaScores()->with('criterion')->get();
        if ($criteriaScores->isNotEmpty()) {
            $totalScoreOn20 = 0.00;
            $count = 0;

            foreach ($criteriaScores as $item) {
                $scoreOn20 = min(20.00, max(0.00, (float) $item->score));
                $weight = (float) ($item->criterion->weight_percentage ?? 10.00);
                $weighted = ($scoreOn20 / 20.00) * $weight;

                $item->update([
                    'score' => $scoreOn20,
                    'max_score' => 20.00,
                    'weighted_score' => round($weighted, 2),
                ]);

                // Synchronisation des points spécifiques pour rétrocompatibilité
                if ($item->criterion?->code === 'punctuality') {
                    $this->punctuality_points = $scoreOn20;
                } elseif (in_array($item->criterion?->code, ['tasks', 'tasks_volume'])) {
                    $this->tasks_points = $scoreOn20;
                } elseif ($item->criterion?->code === 'teamwork') {
                    $this->teamwork_points = $scoreOn20;
                }

                $totalScoreOn20 += $scoreOn20;
                $count++;
            }

            // Formule officielle spécifiée : Moyenne mensuelle = Somme des 10 notes / 10 (sur 20)
            $averageScore20 = $count > 0 ? round($totalScoreOn20 / $count, 2) : 0.00;
            $averageScore20 = min(20.00, max(0.00, $averageScore20));

            $this->final_score_20 = $averageScore20;
            $this->final_score_30 = round($averageScore20 * 1.5, 2); // 20 pts -> 30 pts
            $this->raw_score = $totalScoreOn20;
            $this->max_possible_points = $count * 20.00;
            $this->appreciation = self::computeAppreciation($averageScore20);
        } else {
            // 2. Mode standard journalier (rétrocompatibilité si aucun critère)
            $punctuality = (float) $this->dailyScores()->sum('punctuality_score');
            $tasks = (float) $this->dailyScores()->sum('tasks_score');
            $teamwork = (float) $this->dailyScores()->sum('teamwork_score');
            $rawScore = $punctuality + $tasks + $teamwork;

            $workingDays = (int) $this->total_working_days;
            if ($workingDays <= 0) {
                $workingDays = $this->dailyScores()->where('is_working_day', true)->count();
                $this->total_working_days = $workingDays;
            }

            $dailyMax = $settings->dailyTheoreticalMax(); // Par défaut 0.66
            $maxPossible = round($workingDays * $dailyMax, 2);

            $finalScore30 = 0.00;
            if ($maxPossible > 0) {
                $normalizedMax = (float) $settings->max_evaluation_score; // 30.00
                $finalScore30 = round(($rawScore / $maxPossible) * $normalizedMax, 2);
                $finalScore30 = min($normalizedMax, max(0.00, $finalScore30));
            }

            $this->punctuality_points = $punctuality;
            $this->tasks_points = $tasks;
            $this->teamwork_points = $teamwork;
            $this->raw_score = $rawScore;
            $this->max_possible_points = $maxPossible;
            $this->final_score_30 = $finalScore30;
            $this->final_score_20 = round($finalScore30 / 1.5, 2);
            $this->appreciation = self::computeAppreciation((float) $this->final_score_20);
        }

        // 3. Calcul de la progression par rapport au mois précédent
        $prevMonth = $this->month == 1 ? 12 : $this->month - 1;
        $prevYear = $this->month == 1 ? $this->year - 1 : $this->year;

        $previousEval = self::where('employee_id', $this->employee_id)
            ->where('year', $prevYear)
            ->where('month', $prevMonth)
            ->first();

        if ($previousEval) {
            $prevScore = (float) ($previousEval->final_score_20 ?: ($previousEval->final_score_30 / 1.5));
            $this->score_progress = round(((float) $this->final_score_20) - $prevScore, 2);
        } else {
            $this->score_progress = 0.00;
        }

        $this->save();

        return $this;
    }

    /**
     * Calcule l'appréciation officielle automatique selon le barème sur 20 :
     * 18 à 20 : Excellent
     * 16 à 17,99 : Très bien
     * 14 à 15,99 : Bien
     * 12 à 13,99 : Assez bien
     * 10 à 11,99 : Passable
     * 8 à 9,99 : Insuffisant
     * 0 à 7,99 : Très insuffisant
     */
    public static function computeAppreciation(float $score20): string
    {
        return match (true) {
            $score20 >= 18.00 => 'Excellent',
            $score20 >= 16.00 => 'Très bien',
            $score20 >= 14.00 => 'Bien',
            $score20 >= 12.00 => 'Assez bien',
            $score20 >= 10.00 => 'Passable',
            $score20 >= 8.00 => 'Insuffisant',
            default => 'Très insuffisant',
        };
    }

    public function getAppreciationAttribute(): string
    {
        if (! empty($this->attributes['appreciation'])) {
            return $this->attributes['appreciation'];
        }

        $score = (float) ($this->final_score_20 ?: ($this->final_score_30 / 1.5));

        return self::computeAppreciation($score);
    }

    public function getAppreciationBadgeClassAttribute(): string
    {
        return match ($this->appreciation) {
            'Excellent' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'Très bien' => 'bg-blue-100 text-blue-800 border-blue-300',
            'Bien' => 'bg-sky-100 text-sky-800 border-sky-300',
            'Assez bien' => 'bg-amber-100 text-amber-800 border-amber-300',
            'Passable' => 'bg-orange-100 text-orange-800 border-orange-300',
            'Insuffisant' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-red-200 text-red-900 border-red-400',
        };
    }

    public function getTotalScore200Attribute(): float
    {
        $sum = (float) $this->criteriaScores()->sum('score');
        if ($sum > 0) {
            return round($sum, 2);
        }

        return round(((float) $this->final_score_20) * 10, 2);
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
