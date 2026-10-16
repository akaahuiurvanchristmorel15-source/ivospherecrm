<?php

namespace App\Services\Subscriptions;

use App\Models\Subscription;

interface PaymentGateway
{
    public function name(): string;

    /**
     * Prépare le paiement et renvoie l'adresse de la page de paiement.
     */
    public function initiate(Subscription $subscription): string;

    /**
     * Interroge le prestataire : seule source fiable de l'état du paiement.
     */
    public function verify(Subscription $subscription): PaymentResult;
}
