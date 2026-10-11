<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * Nombre de jours après la date de vente pendant lesquels l'annulation reste possible.
     */
    public const CANCELLATION_WINDOW_DAYS = 3;

    public const STATUS_VALIDATED = 'livrée';

    public const STATUS_CANCELLED = 'annulée';

    /**
     * Statuts considérés comme « validés » (livrée). « livree » est utilisé par le POS.
     *
     * @var array<int, string>
     */
    public const VALIDATED_STATUSES = ['livrée', 'livree'];

    /**
     * Statuts « en cours » pouvant encore être validés ou annulés.
     *
     * @var array<int, string>
     */
    public const PENDING_STATUSES = ['en_attente', 'confirmée', 'en_cours'];

    protected $fillable = [
        'reference', 'quotation_id', 'customer_id', 'domain_id',
        'user_id', 'date', 'delivery_date', 'status',
        'subtotal', 'tax_amount', 'discount', 'total',
        'notes', 'delivery_address',
        'validated_at', 'validated_by',
        'cancelled_at', 'cancelled_by', 'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'delivery_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'validated_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function isValidated(): bool
    {
        return in_array($this->status, self::VALIDATED_STATUSES, true);
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Une vente est définitive si elle est annulée, ou si elle est validée et que le délai de 3 jours est expiré.
     */
    public function isFinal(): bool
    {
        if ($this->isCancelled()) {
            return true;
        }

        if ($this->isValidated() && ! $this->isWithinCancellationWindow()) {
            return true;
        }

        return false;
    }

    public function canBeValidated(): bool
    {
        return ! $this->isValidated() && ! $this->isCancelled();
    }

    /**
     * Date limite (incluse) jusqu'à laquelle la vente peut être annulée.
     */
    public function cancellationDeadline(): CarbonInterface
    {
        $saleDate = $this->date ?? $this->created_at ?? now();

        return $saleDate->copy()->addDays(self::CANCELLATION_WINDOW_DAYS)->endOfDay();
    }

    public function isWithinCancellationWindow(): bool
    {
        return now()->lessThanOrEqualTo($this->cancellationDeadline());
    }

    /**
     * Une vente peut être annulée dans le délai de 3 jours, qu'elle soit en cours ou déjà validée.
     */
    public function canBeCancelled(): bool
    {
        return ! $this->isCancelled() && $this->isWithinCancellationWindow();
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
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
        return $this->hasMany(OrderItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
