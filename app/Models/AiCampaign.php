<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'customer_id',
        'domain_id',
        'user_id',
        'name',
        'brief',
        'status',
        'channels',
        'start_date',
        'end_date',
        'budget',
        'results',
    ];

    protected function casts(): array
    {
        return [
            'channels' => 'array',
            'budget' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(AiCampaignContent::class, 'campaign_id');
    }
}
