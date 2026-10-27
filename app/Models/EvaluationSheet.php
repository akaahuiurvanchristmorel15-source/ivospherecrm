<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Fiche d'évaluation remplie par un évaluateur pour un membre et un mois donnés.
 */
class EvaluationSheet extends Model
{
    public const GROUP_DIRECTION = 'direction';

    public const GROUP_HR = 'rh';

    public const GROUP_PEER = 'pair';

    public const GROUP_SELF = 'auto';

    /**
     * Groupes d'évaluateurs du référentiel et catégorie de pondération de chacun.
     *
     * @var array<string, array{label: string, category: string}>
     */
    public const GROUPS = [
        'direction' => ['label' => 'Direction générale', 'category' => 'direction'],
        'rh' => ['label' => 'Ressources humaines', 'category' => 'rh'],
        'finances' => ['label' => 'Responsable Finances', 'category' => 'fonction'],
        'communication' => ['label' => 'Responsable Communication', 'category' => 'fonction'],
        'commercial' => ['label' => 'Responsable Commercial', 'category' => 'fonction'],
        'technique' => ['label' => 'Responsable Pôle technique', 'category' => 'fonction'],
        'cyber' => ['label' => 'Responsable Cyber', 'category' => 'fonction'],
        'pair' => ['label' => 'Collègue (pair)', 'category' => 'pairs'],
        'auto' => ['label' => 'Auto-évaluation', 'category' => 'auto'],
    ];

    /**
     * Poids de chaque catégorie d'évaluateurs dans la note d'un critère (l'auto-évaluation compte pour 0 %).
     *
     * @var array<string, array{label: string, weight: int}>
     */
    public const CATEGORIES = [
        'direction' => ['label' => 'Direction', 'weight' => 40],
        'rh' => ['label' => 'RH', 'weight' => 20],
        'fonction' => ['label' => 'Responsables', 'weight' => 25],
        'pairs' => ['label' => 'Pairs', 'weight' => 15],
    ];

    protected $fillable = [
        'monthly_evaluation_id',
        'evaluator_id',
        'evaluator_group',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function monthlyEvaluation(): BelongsTo
    {
        return $this->belongsTo(MonthlyEvaluation::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(EvaluationSheetScore::class);
    }

    public function getGroupLabelAttribute(): string
    {
        return self::GROUPS[$this->evaluator_group]['label'] ?? $this->evaluator_group;
    }

    public function getCategoryAttribute(): string
    {
        return self::GROUPS[$this->evaluator_group]['category'] ?? self::GROUP_SELF;
    }

    /**
     * Groupes au titre desquels un utilisateur peut évaluer un membre.
     * Personne ne s'évalue soi-même en tant que responsable : pour sa propre fiche, seule l'auto-évaluation est ouverte.
     *
     * @return list<string>
     */
    public static function groupsAvailableTo(User $user, Employee $employee): array
    {
        if ($employee->user_id === $user->id) {
            return [self::GROUP_SELF];
        }

        $groups = [];

        if ($user->hasRole('administrateur')) {
            $groups[] = self::GROUP_DIRECTION;
        }

        if ($user->hasRole('responsable_rh')) {
            $groups[] = self::GROUP_HR;
        }

        if ($user->hasPermission('evaluations.function')) {
            foreach (self::GROUPS as $group => $definition) {
                if ($definition['category'] === 'fonction') {
                    $groups[] = $group;
                }
            }
        }

        if ($user->employee()->exists()) {
            $groups[] = self::GROUP_PEER;
        }

        return $groups;
    }
}
