<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TechProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'customer_id',
        'domain_id',
        'user_id',
        'name',
        'type',
        'description',
        'status',
        'start_date',
        'end_date',
        'budget',
        'spent',
        'progress',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'spent' => 'decimal:2',
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

    public function tasks(): HasMany
    {
        return $this->hasMany(TechProjectTask::class, 'project_id');
    }
}
