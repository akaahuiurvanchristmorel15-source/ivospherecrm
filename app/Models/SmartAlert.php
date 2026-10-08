<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmartAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'description',
        'priority',
        'domain_id',
        'assigned_to',
        'status',
        'action_url',
        'due_date',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
