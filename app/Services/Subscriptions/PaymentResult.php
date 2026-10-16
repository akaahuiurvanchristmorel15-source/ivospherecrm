<?php

namespace App\Services\Subscriptions;

/**
 * Résultat de la vérification d'un paiement auprès du prestataire.
 */
final class PaymentResult
{
    public const PAID = 'paid';

    public const FAILED = 'failed';

    public const PENDING = 'pending';

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly string $status,
        public readonly ?string $transactionId = null,
        public readonly ?string $method = null,
        public readonly ?int $amount = null,
        public readonly array $payload = [],
    ) {}

    public function isPaid(): bool
    {
        return $this->status === self::PAID;
    }
}
