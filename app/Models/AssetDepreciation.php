<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciation extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixed_asset_id',
        'year',
        'fiscal_period',
        'base_value',
        'depreciation_amount',
        'accumulated_depreciation',
        'book_value',
        'is_posted',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'base_value' => 'decimal:2',
            'depreciation_amount' => 'decimal:2',
            'accumulated_depreciation' => 'decimal:2',
            'book_value' => 'decimal:2',
            'is_posted' => 'boolean',
            'posted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<FixedAsset, $this>
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }
}
