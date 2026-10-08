<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'customer_id',
        'domain_id',
        'user_id',
        'order_id',
        'type',
        'format_id',
        'support_id',
        'finishing_id',
        'quantity',
        'unit_price',
        'total',
        'file_path',
        'specifications',
        'status',
        'deadline',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
            'deadline' => 'date',
            'completed_at' => 'date',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function format(): BelongsTo
    {
        return $this->belongsTo(PrintFormat::class, 'format_id');
    }

    public function support(): BelongsTo
    {
        return $this->belongsTo(PrintSupport::class, 'support_id');
    }

    public function finishing(): BelongsTo
    {
        return $this->belongsTo(PrintFinishing::class, 'finishing_id');
    }
}
