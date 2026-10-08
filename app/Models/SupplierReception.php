<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierReception extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'purchase_request_id',
        'supplier_id',
        'product_id',
        'warehouse_id',
        'user_id',
        'ordered_quantity',
        'received_quantity',
        'missing_quantity',
        'damaged_quantity',
        'batch_number',
        'expiration_date',
        'quality_status',
        'received_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'ordered_quantity' => 'integer',
            'received_quantity' => 'integer',
            'missing_quantity' => 'integer',
            'damaged_quantity' => 'integer',
            'expiration_date' => 'date',
            'received_at' => 'date',
        ];
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
