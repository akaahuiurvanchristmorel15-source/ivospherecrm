<?php

namespace App\Models;

use Database\Factories\CyberServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CyberService extends Model
{
    /** @use HasFactory<CyberServiceFactory> */
    use HasFactory;

    /**
     * Catégories de prestations proposées au cyber.
     *
     * @var array<string, string>
     */
    public const CATEGORIES = [
        'impression' => 'Impression',
        'photocopie' => 'Photocopie',
        'photo' => 'Photo minute',
        'bureautique' => 'Saisie & scan',
        'finition' => 'Finition',
        'internet' => 'Internet',
        'autre' => 'Autre',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'category',
        'unit',
        'price',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
