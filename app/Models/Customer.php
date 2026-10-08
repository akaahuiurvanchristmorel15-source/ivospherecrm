<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain_id',
        'user_id',
        'commercial_id',
        'code',
        'type',
        'category',
        'name',
        'company',
        'contact_person',
        'nif',
        'email',
        'phone',
        'phone2',
        'whatsapp',
        'address',
        'city',
        'country',
        'loyalty_points',
        'loyalty_level',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'loyalty_points' => 'integer',
        ];
    }

    public function getTotalSpentAttribute(): float
    {
        return (float) $this->invoices()->whereIn('status', ['partielle', 'payee'])->sum('paid_amount');
    }

    public function getCompanyNameAttribute(): ?string
    {
        return $this->company ?: $this->name;
    }

    public function getFirstNameAttribute(): ?string
    {
        $parts = explode(' ', trim((string) ($this->name ?? '')));

        return $parts[0] ?? '';
    }

    public function getLastNameAttribute(): ?string
    {
        $parts = explode(' ', trim((string) ($this->name ?? '')));
        array_shift($parts);

        return implode(' ', $parts);
    }

    public function getLoyaltyBadgeColorAttribute(): string
    {
        return match ($this->loyalty_level) {
            'PREMIUM' => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
            'GOLD' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
            'SILVER' => 'bg-slate-400/20 text-slate-200 border-slate-400/30',
            default => 'bg-amber-700/20 text-amber-500 border-amber-700/30',
        };
    }

    public function addLoyaltyPoints(float|int $spentAmount): void
    {
        $points = (int) floor($spentAmount / 1000);
        if ($points <= 0) {
            return;
        }

        $this->loyalty_points += $points;

        if ($this->loyalty_points >= 5000) {
            $this->loyalty_level = 'PREMIUM';
        } elseif ($this->loyalty_points >= 2000) {
            $this->loyalty_level = 'GOLD';
        } elseif ($this->loyalty_points >= 500) {
            $this->loyalty_level = 'SILVER';
        } else {
            $this->loyalty_level = 'BRONZE';
        }

        $this->save();
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commercial(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(CommercialAppointment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', 'inactif');
    }

    public function scopeByDomain(Builder $query, $domainId): Builder
    {
        return $query->where('domain_id', $domainId);
    }
}
