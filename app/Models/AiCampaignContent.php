<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiCampaignContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'type',
        'content',
        'media_path',
        'platform',
        'scheduled_at',
        'published_at',
        'status',
        'metrics',
    ];

    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AiCampaign::class, 'campaign_id');
    }
}
