<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'model_type',
        'model_id',
        'current_step_id',
        'status',
        'history',
    ];

    protected function casts(): array
    {
        return [
            'history' => 'array',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(CustomWorkflow::class, 'workflow_id');
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'current_step_id');
    }
}
