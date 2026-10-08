<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'trigger_event',
        'conditions',
        'actions',
        'is_active',
        'last_triggered_at',
        'execution_count',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'actions' => 'array',
            'is_active' => 'boolean',
            'last_triggered_at' => 'datetime',
            'execution_count' => 'integer',
        ];
    }
}
