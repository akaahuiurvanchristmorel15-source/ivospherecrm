<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'asset_category_id',
        'domain_id',
        'description',
        'brand',
        'model',
        'serial_number',
        'acquisition_date',
        'supplier_id',
        'purchase_price',
        'additional_fees',
        'acquisition_value',
        'residual_value',
        'useful_life_years',
        'depreciation_method',
        'depreciation_start_date',
        'location',
        'warehouse_id',
        'responsible_employee_id',
        'responsible_department',
        'status',
        'condition',
        'is_rental_eligible',
        'rental_price_per_day',
        'rental_deposit_amount',
        'purchase_document_path',
        'photo_path',
        'notes',
        'disposed_at',
        'disposal_price',
        'disposal_reason',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'depreciation_start_date' => 'date',
            'disposed_at' => 'date',
            'purchase_price' => 'decimal:2',
            'additional_fees' => 'decimal:2',
            'acquisition_value' => 'decimal:2',
            'residual_value' => 'decimal:2',
            'rental_price_per_day' => 'decimal:2',
            'rental_deposit_amount' => 'decimal:2',
            'disposal_price' => 'decimal:2',
            'useful_life_years' => 'integer',
            'is_rental_eligible' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FixedAsset $asset) {
            if (empty($asset->code)) {
                $asset->code = static::generateCode($asset->domain_id);
            }
            if (empty($asset->acquisition_value) || $asset->acquisition_value <= 0) {
                $asset->acquisition_value = (float) $asset->purchase_price + (float) $asset->additional_fees;
            }
            if (empty($asset->depreciation_start_date)) {
                $asset->depreciation_start_date = $asset->acquisition_date;
            }
        });

        static::saved(function (FixedAsset $asset) {
            // Génère ou actualise automatiquement l'échéancier prévisionnel si aucun n'existe
            if ($asset->depreciations()->count() === 0 && $asset->depreciation_method !== 'non_amortissable' && $asset->useful_life_years > 0) {
                $asset->generateDepreciationSchedule();
            }
        });
    }

    /**
     * @return BelongsTo<AssetCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    /**
     * @return BelongsTo<Domain, $this>
     */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function responsibleEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'responsible_employee_id');
    }

    /**
     * @return HasMany<AssetDepreciation, $this>
     */
    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class)->orderBy('year');
    }

    /**
     * @return HasMany<AssetUsage, $this>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(AssetUsage::class)->latest('date');
    }

    /**
     * @return HasMany<AssetRental, $this>
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(AssetRental::class)->latest('start_date');
    }

    /**
     * @return HasMany<AssetMaintenance, $this>
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class)->latest('maintenance_date');
    }

    // ── SCOPES ─────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['hors_service', 'vendu', 'cede']);
    }

    public function scopeRentalEligible(Builder $query): Builder
    {
        return $query->where('is_rental_eligible', true);
    }

    public function scopeByDomain(Builder $query, ?int $domainId): Builder
    {
        return $domainId ? $query->where('domain_id', $domainId) : $query;
    }

    // ── CALCULS COMPTABLES & FINANCIERS ────────────────────────────────────

    /**
     * Amortissement annuel théorique (méthode linéaire).
     */
    public function getAnnualDepreciationAttribute(): float
    {
        if ($this->depreciation_method === 'non_amortissable' || $this->useful_life_years <= 0) {
            return 0.0;
        }

        $base = (float) $this->acquisition_value - (float) $this->residual_value;

        return round(max(0, $base) / $this->useful_life_years, 2);
    }

    /**
     * Amortissement mensuel théorique.
     */
    public function getMonthlyDepreciationAttribute(): float
    {
        return round($this->annual_depreciation / 12, 2);
    }

    /**
     * Amortissements cumulés calculés au jour J.
     */
    public function getAccumulatedDepreciationAttribute(): float
    {
        if ($this->depreciation_method === 'non_amortissable' || $this->useful_life_years <= 0) {
            return 0.0;
        }

        $startDate = $this->depreciation_start_date ?? $this->acquisition_date;
        if (! $startDate) {
            return 0.0;
        }

        $now = now();
        if ($now->lessThan($startDate)) {
            return 0.0;
        }

        $monthsPassed = $startDate->diffInMonths($now);
        $totalMonths = $this->useful_life_years * 12;
        $ratio = min(1.0, $monthsPassed / max(1, $totalMonths));

        $base = (float) $this->acquisition_value - (float) $this->residual_value;

        return round(max(0, $base) * $ratio, 2);
    }

    /**
     * Valeur Nette Comptable (VNC) / Valeur actuelle.
     */
    public function getNetBookValueAttribute(): float
    {
        return max((float) $this->residual_value, round((float) $this->acquisition_value - $this->accumulated_depreciation, 2));
    }

    /**
     * Chiffre d'affaires total généré par les prestations / utilisations directes.
     */
    public function getTotalUsageRevenueAttribute(): float
    {
        return (float) $this->usages()->sum('revenue_generated');
    }

    /**
     * Chiffre d'affaires total généré par les locations clients.
     */
    public function getTotalRentalRevenueAttribute(): float
    {
        return (float) $this->rentals()->where('status', '!=', 'annule')->sum('total_amount');
    }

    /**
     * Chiffre d'affaires global généré par l'actif (Prestations + Locations).
     */
    public function getTotalRevenueGeneratedAttribute(): float
    {
        return round($this->total_usage_revenue + $this->total_rental_revenue, 2);
    }

    /**
     * Total des coûts de maintenance et réparation subis par l'actif.
     */
    public function getTotalMaintenanceCostsAttribute(): float
    {
        return (float) $this->maintenances()->sum('cost');
    }

    /**
     * Rentabilité nette / Contribution estimée de l'actif :
     * CA généré - Coûts de maintenance - Amortissements cumulés
     */
    public function getNetProfitabilityAttribute(): float
    {
        return round($this->total_revenue_generated - $this->total_maintenance_costs - $this->accumulated_depreciation, 2);
    }

    /**
     * Taux de retour / ROI sur l'actif par rapport à sa valeur d'acquisition.
     */
    public function getRoiPercentageAttribute(): float
    {
        $val = (float) $this->acquisition_value;
        if ($val <= 0) {
            return 0.0;
        }

        return round(($this->total_revenue_generated / $val) * 100, 1);
    }

    /**
     * Diagnostic de rentabilité de l'actif.
     */
    public function getProfitabilityStatusAttribute(): string
    {
        $ca = $this->total_revenue_generated;
        $val = (float) $this->acquisition_value;

        if ($ca >= ($val * 1.5)) {
            return 'tres_rentable';
        }
        if ($ca >= $val) {
            return 'rentable';
        }
        if ($ca > 0) {
            return 'en_amortissement';
        }

        return 'support_interne';
    }

    /**
     * Libellé lisible du statut de rentabilité.
     */
    public function getProfitabilityLabelAttribute(): string
    {
        return match ($this->profitability_status) {
            'tres_rentable' => 'Très Rentable (>150% amorti)',
            'rentable' => 'Rentable (Capital récupéré)',
            'en_amortissement' => 'En cours d\'amortissement',
            default => 'Équipement Support / Interne',
        };
    }

    // ── ÉCHÉANCIER D'AMORTISSEMENT AUTOMATIQUE ──────────────────────────────

    /**
     * Génère l'échéancier prévisionnel des dotations d'amortissements annuel.
     */
    public function generateDepreciationSchedule(): void
    {
        if ($this->depreciation_method === 'non_amortissable' || $this->useful_life_years <= 0) {
            return;
        }

        $this->depreciations()->delete();

        $start = $this->depreciation_start_date ?? $this->acquisition_date ?? now();
        $base = (float) $this->acquisition_value - (float) $this->residual_value;
        $annualAmount = round($base / $this->useful_life_years, 2);

        $accumulated = 0;
        $currentYear = (int) $start->format('Y');

        for ($i = 1; $i <= $this->useful_life_years; $i++) {
            $year = $currentYear + ($i - 1);
            $dotation = ($i === $this->useful_life_years) ? max(0, $base - $accumulated) : $annualAmount;
            $accumulated += $dotation;
            $vnc = max((float) $this->residual_value, (float) $this->acquisition_value - $accumulated);

            $this->depreciations()->create([
                'year' => $year,
                'fiscal_period' => "Année {$i} ({$year})",
                'base_value' => $base,
                'depreciation_amount' => $dotation,
                'accumulated_depreciation' => $accumulated,
                'book_value' => $vnc,
                'is_posted' => $year < (int) now()->format('Y'),
                'posted_at' => $year < (int) now()->format('Y') ? Carbon::create($year, 12, 31) : null,
            ]);
        }
    }

    /**
     * Génère une référence unique pour l'actif selon le domaine.
     */
    public static function generateCode(?int $domainId = null): string
    {
        $prefix = 'IMM-ACT';
        if ($domainId) {
            $domain = Domain::find($domainId);
            if ($domain) {
                $code = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $domain->name), 0, 3));
                $prefix = 'IMM-'.($code ?: 'GEN');
            }
        }

        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('%s-%s-%03d', $prefix, $year, $count);
    }
}
