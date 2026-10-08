<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationCriterion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
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

    public function isAutomatic(): bool
    {
        return $this->calculation_mode === 'automatic';
    }

    public function isManual(): bool
    {
        return $this->calculation_mode === 'manual';
    }
}
