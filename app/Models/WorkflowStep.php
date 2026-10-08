<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'step_order',
        'name',
        'role_id',
        'time_limit_hours',
    ];

    protected function casts(): array
    {
        return [
            'step_order' => 'integer',
            'time_limit_hours' => 'integer',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(CustomWorkflow::class, 'workflow_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
