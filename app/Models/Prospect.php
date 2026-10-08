<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prospect extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain_id',
        'assigned_to',
        'commercial_id',
        'name',
        'company',
        'email',
        'phone',
        'source',
        'status',
        'stage',
        'probability',
        'estimated_value',
        'notes',
        'next_follow_up',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'probability' => 'integer',
            'next_follow_up' => 'date',
        ];
    }

    public function getWeightedValueAttribute(): float
    {
        return ((float) $this->estimated_value * (int) $this->probability) / 100;
    }

    public function getStageLabelAttribute(): string
    {
        return match ($this->stage) {
            'contacte' => 'Contacté',
            'interesse' => 'Intéressé',
            'devis_envoye' => 'Devis envoyé',
            'negociation' => 'Négociation',
            'gagne' => 'Gagné',
            'perdu' => 'Perdu',
            default => 'Nouveau',
        };
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function commercial(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(CommercialAppointment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', 'perdu')->where('stage', '!=', 'perdu');
    }

    public function scopeByStage(Builder $query, string $stage): Builder
    {
        return $query->where('stage', $stage);
    }

    public function scopeByDomain(Builder $query, $domainId): Builder
    {
        return $query->where('domain_id', $domainId);
    }
}
