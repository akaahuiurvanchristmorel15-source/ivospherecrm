<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'brand_id', 'domain_id', 'supplier_id',
        'name', 'sku', 'barcode', 'description', 'purchase_price',
        'selling_price', 'tax_rate', 'unit', 'min_stock', 'max_stock',
        'image', 'is_active', 'status',
    ];

    protected $appends = [
        'current_stock',
        'available_stock',
        'ean',
        'formatted_ean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->barcode)) {
                $product->barcode = static::generateEan13();
            }
        });

        static::saving(function (Product $product) {
            if ($product->purchase_price === null) {
                $product->purchase_price = 0;
            }
            if ($product->selling_price === null) {
                $product->selling_price = 0;
            }
            if ($product->tax_rate === null) {
                $product->tax_rate = (float) Setting::get('default_tax_rate', 0);
            }
            if ($product->min_stock === null) {
                $product->min_stock = 0;
            }
            if (empty($product->unit)) {
                $product->unit = 'pièce';
            }
        });
    }

    /**
     * Génère un code EAN-13 GS1 standard avec clé de contrôle (préfixe 618 Côte d'Ivoire).
     */
    public static function generateEan13(?int $seed = null): string
    {
        $prefix = '618';
        $body = str_pad((string) ($seed ?? random_int(100000000, 999999999)), 9, '0', STR_PAD_LEFT);
        $twelve = substr($prefix.$body, 0, 12);

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $twelve[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        $checksum = (10 - ($sum % 10)) % 10;

        return $twelve.$checksum;
    }

    /**
     * Accessor pour l'EAN intégré (retourne un EAN-13 valide calculé avec clé de contrôle).
     */
    public function getEanAttribute(): string
    {
        $code = trim((string) $this->barcode);

        if (! empty($code)) {
            if (strlen($code) === 13 && ctype_digit($code)) {
                return $code;
            }

            if (ctype_digit($code)) {
                // S'il commence par 618 ou un autre préfixe, on ajuste à 12 chiffres puis ajoute la clé de contrôle
                $twelve = substr(str_pad($code, 12, '0', STR_PAD_RIGHT), 0, 12);
                $sum = 0;
                for ($i = 0; $i < 12; $i++) {
                    $sum += (int) $twelve[$i] * ($i % 2 === 0 ? 1 : 3);
                }
                $checksum = (10 - ($sum % 10)) % 10;

                return $twelve.$checksum;
            }

            return $code;
        }

        return static::generateEan13($this->id);
    }

    /**
     * Accessor pour l'affichage espacé lisible de l'EAN (ex: 6 184031 981867).
     */
    public function getFormattedEanAttribute(): string
    {
        $ean = $this->ean;
        if (strlen($ean) === 13) {
            return substr($ean, 0, 1).' '.substr($ean, 1, 6).' '.substr($ean, 7, 6);
        }

        return $ean;
    }

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getMarginAttribute(): float
    {
        return (float) ($this->selling_price - $this->purchase_price);
    }

    public function getUnitPriceAttribute(): float
    {
        return (float) ($this->selling_price ?? 0);
    }

    public function getCostPriceAttribute(): float
    {
        return (float) ($this->purchase_price > 0 ? $this->purchase_price : ($this->selling_price * 0.65));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function getCurrentStockAttribute(): int
    {
        $hasWarehouseStocks = $this->relationLoaded('warehouseStocks')
            ? $this->warehouseStocks->isNotEmpty()
            : $this->warehouseStocks()->exists();

        if ($hasWarehouseStocks) {
            return $this->relationLoaded('warehouseStocks')
                ? (int) $this->warehouseStocks->sum('physical_quantity')
                : (int) $this->warehouseStocks()->sum('physical_quantity');
        }

        $last = $this->relationLoaded('movements')
            ? $this->movements->sortByDesc('id')->first()
            : $this->movements()->latest('id')->first();

        return $last ? (int) $last->stock_after : 0;
    }

    public function getReservedStockAttribute(): int
    {
        return $this->relationLoaded('warehouseStocks')
            ? (int) $this->warehouseStocks->sum('reserved_quantity')
            : (int) $this->warehouseStocks()->sum('reserved_quantity');
    }

    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->current_stock - $this->reserved_stock);
    }

    public function getIncomingStockAttribute(): int
    {
        return (int) $this->warehouseStocks()->sum('incoming_quantity');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByDomain(Builder $query, $domainId): Builder
    {
        return $query->where('domain_id', $domainId);
    }
}
