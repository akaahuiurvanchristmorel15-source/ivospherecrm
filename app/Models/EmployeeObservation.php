<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeObservation extends Model
{
    use HasFactory;

    /**
     * Nature de l'observation => libellé affiché.
     *
     * @var array<string, string>
     */
    public const NATURES = [
        'positive' => 'Positive',
        'a_ameliorer' => 'À améliorer',
        'neutre' => 'Neutre',
    ];

    /**
     * Catégorie de l'observation => libellé affiché.
     *
     * @var array<string, string>
     */
    public const CATEGORIES = [
        'ponctualite' => 'Ponctualité',
        'comportement' => 'Comportement',
        'qualite_travail' => 'Qualité du travail',
        'relation_client' => 'Relation client',
        'esprit_equipe' => 'Esprit d\'équipe',
        'autre' => 'Autre',
    ];

    protected $fillable = [
        'employee_id',
        'author_id',
        'observed_on',
        'nature',
        'category',
        'title',
        'content',
        'visible_to_employee',
    ];

    protected function casts(): array
    {
        return [
            'observed_on' => 'date',
            'visible_to_employee' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getNatureLabelAttribute(): string
    {
        return self::NATURES[$this->nature] ?? $this->nature;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function scopeVisibleToEmployee(Builder $query): void
    {
        $query->where('visible_to_employee', true);
    }

    /**
     * Observations des collaborateurs que l'utilisateur peut suivre (voir EmployeeObservationPolicy).
     */
    public function scopeObservableBy(Builder $query, User $user): void
    {
        $query->whereHas('employee', fn (Builder $employees) => $employees->observableBy($user));
    }
}
