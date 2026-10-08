<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhotoSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'customer_id',
        'domain_id',
        'user_id',
        'photographer',
        'date',
        'location',
        'package',
        'status',
        'price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'date' => 'date',
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

    public function galleries(): HasMany
    {
        return $this->hasMany(PhotoGallery::class, 'session_id');
    }
}
