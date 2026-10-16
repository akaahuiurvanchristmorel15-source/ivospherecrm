<?php

namespace App\Services\Subscriptions;

use App\Models\Subscription;

/**
 * Paiement factice pour tester le parcours en local (jamais utilisé en production).
 */
class SimulationGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'simulation';
    }

    public function initiate(Subscription $subscription): string
    {
        return route('offres.payment.simulation', $subscription->reference);
    }

    public function verify(Subscription $subscription): PaymentResult
    {
        $simulated = $subscription->payment_payload['simulated_status'] ?? null;

        return match ($simulated) {
            PaymentResult::PAID => new PaymentResult(PaymentResult::PAID, 'SIM-'.$subscription->reference, 'SIMULATION', $subscription->amount, ['simulated' => true]),
            PaymentResult::FAILED => new PaymentResult(PaymentResult::FAILED),
            default => new PaymentResult(PaymentResult::PENDING),
        };
    }
}
