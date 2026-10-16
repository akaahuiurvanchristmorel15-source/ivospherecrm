<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'domain_id',
        'name',
        'code',
        'label_number',
        'label_color',
        'address',
        'description',
        'manager',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'label_text_color',
    ];

    /**
     * Palette de couleurs attribuées par défaut aux étiquettes d'entrepôt (cyclique).
     *
     * @var list<string>
     */
    public const LABEL_COLORS = [
        '#2563EB',
        '#DC2626',
        '#16A34A',
        '#F59E0B',
        '#9333EA',
        '#0891B2',
        '#DB2777',
        '#EA580C',
        '#4F46E5',
        '#65A30D',
        '#0F766E',
        '#78716C',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'label_number' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Warehouse $warehouse) {
            if (blank($warehouse->label_number)) {
                $warehouse->label_number = static::nextLabelNumber();
            }

            if (blank($warehouse->label_color)) {
                $warehouse->label_color = static::defaultLabelColor($warehouse->label_number);
            }
        });
    }

    /**
     * Plus petit numéro d'étiquette libre (à partir de 1).
     */
    public static function nextLabelNumber(): int
    {
        $usedNumbers = static::query()->whereNotNull('label_number')->pluck('label_number')->flip();

        $number = 1;
        while ($usedNumbers->has($number)) {
            $number++;
        }

        return $number;
    }

    public static function defaultLabelColor(int $labelNumber): string
    {
        return self::LABEL_COLORS[(max(1, $labelNumber) - 1) % count(self::LABEL_COLORS)];
    }

    /**
     * Couleur du texte (noir ou blanc) lisible sur le fond de l'étiquette.
     */
    protected function labelTextColor(): Attribute
    {
        return Attribute::get(function (): string {
            $hex = ltrim($this->label_color ?: static::defaultLabelColor((int) $this->label_number), '#');
            [$red, $green, $blue] = array_map('hexdec', str_split($hex, 2));
            $luminance = (0.299 * $red + 0.587 * $green + 0.114 * $blue) / 255;

            return $luminance > 0.6 ? '#0B0F14' : '#FFFFFF';
        });
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function stockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeByDomain(Builder $query, int $domainId): void
    {
        $query->where('domain_id', $domainId);
    }
}
