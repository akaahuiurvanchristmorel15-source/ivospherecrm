<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'domain_id',
        'title',
        'target_amount',
        'achieved_amount',
        'target_sales_count',
        'achieved_sales_count',
        'period',
        'start_date',
        'end_date',
        'commission_rate',
        'bonus_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'achieved_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'bonus_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'target_sales_count' => 'integer',
            'achieved_sales_count' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    /**
     * Taux d'atteinte réel (en %)
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->target_amount <= 0) {
            return 0;
        }

        return round(($this->achieved_amount / $this->target_amount) * 100, 1);
    }

    /**
     * Montant restant à réaliser
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->target_amount - $this->achieved_amount);
    }

    /**
     * Prime / Commission totale estimée
     */
    public function getEstimatedCommissionAttribute(): float
    {
        $commissionFromSales = ($this->achieved_amount * ($this->commission_rate ?? 0)) / 100;
        $bonus = ($this->achieved_amount >= $this->target_amount) ? ($this->bonus_amount ?? 0) : 0;

        return round($commissionFromSales + $bonus, 2);
    }

    /**
     * Badge de statut Tailwind
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'atteint' => ['label' => 'Objectif Atteint', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'partiel' => ['label' => 'Partiellement Atteint', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
            'non_atteint' => ['label' => 'Non Atteint', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
            'annule' => ['label' => 'Annulé', 'class' => 'bg-slate-100 text-[#64748B] border-[#E2E8F0]'],
            default => ['label' => 'En Cours', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
        };
    }
}
