<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'customer_id', 'domain_id', 'user_id',
        'date', 'valid_until', 'status', 'subtotal',
        'tax_amount', 'discount', 'total', 'notes', 'conditions',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
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

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function calculateTotals(): void
    {
        $subtotal = 0;
        $tax_amount = 0;
        $discount = 0;

        foreach ($this->items as $item) {
            $subtotal += $item->quantity * $item->unit_price;
            $tax_amount += $item->tax_amount;
            $discount += $item->discount;
        }

        $this->subtotal = $subtotal;
        $this->tax_amount = $tax_amount;
        $this->discount = $discount;
        $this->total = $subtotal + $tax_amount - $discount;

        $this->save();
    }
}
