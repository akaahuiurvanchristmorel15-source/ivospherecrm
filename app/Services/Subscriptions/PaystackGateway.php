<?php

namespace App\Services\Subscriptions;

use App\Models\Subscription;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Paiement en ligne via Paystack (carte bancaire et Mobile Money).
 * Documentation : https://paystack.com/docs/api/transaction
 */
class PaystackGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'paystack';
    }

    public function initiate(Subscription $subscription): string
    {
        $config = $this->config();
        $subscription->loadMissing(['tenant', 'plan']);

        try {
            $response = Http::withToken($config['secret_key'])->acceptJson()->timeout(20)
                ->post($config['base_url'].'/transaction/initialize', [
                    'email' => $subscription->tenant->email,
                    'amount' => $this->toSubunit($subscription->amount),
                    'currency' => $subscription->currency,
                    'reference' => $subscription->reference,
                    'callback_url' => route('offres.payment.return', $subscription->reference),
                    'channels' => $config['channels'],
                    'metadata' => [
                        'subscription_reference' => $subscription->reference,
                        'tenant' => $subscription->tenant->name,
                        'plan' => $subscription->plan->name,
                        'billing_cycle' => $subscription->billingCycleLabel(),
                    ],
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Paystack injoignable : '.$exception->getMessage(), 0, $exception);
        }

        $paymentUrl = $response->json('data.authorization_url');
        if (! $response->successful() || ! $response->json('status') || ! $paymentUrl) {
            throw new RuntimeException('Paystack : initialisation du paiement impossible ('.($response->json('message') ?? $response->status()).').');
        }

        return $paymentUrl;
    }

    public function verify(Subscription $subscription): PaymentResult
    {
        $config = $this->config();

        try {
            $response = Http::withToken($config['secret_key'])->acceptJson()->timeout(20)
                ->get($config['base_url'].'/transaction/verify/'.rawurlencode($subscription->reference));
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Paystack injoignable : '.$exception->getMessage(), 0, $exception);
        }

        $data = (array) $response->json('data', []);
        $status = strtolower((string) ($data['status'] ?? ''));
        $paidAmount = isset($data['amount']) ? intdiv((int) $data['amount'], max(1, $config['amount_multiplier'])) : null;
        $sameCurrency = strtoupper((string) ($data['currency'] ?? '')) === strtoupper($subscription->currency);

        // Un paiement réussi d'un montant ou d'une devise différents n'active pas l'abonnement.
        if ($response->successful() && $status === 'success' && $sameCurrency && $paidAmount !== null && $paidAmount >= $subscription->amount) {
            return new PaymentResult(PaymentResult::PAID, (string) ($data['id'] ?? $subscription->reference), $this->methodLabel($data), $paidAmount, $data);
        }

        if (in_array($status, ['failed', 'abandoned', 'reversed'], true)) {
            return new PaymentResult(PaymentResult::FAILED, null, $this->methodLabel($data), $paidAmount, $data);
        }

        return new PaymentResult(PaymentResult::PENDING, null, null, $paidAmount, $data);
    }

    /**
     * Les notifications Paystack sont signées (HMAC SHA-512 du contenu avec la clé secrète).
     */
    public function hasValidSignature(Request $request): bool
    {
        $signature = (string) $request->header('x-paystack-signature');
        $secret = (string) config('subscriptions.payment.paystack.secret_key');

        return $signature !== '' && $secret !== '' && hash_equals(hash_hmac('sha512', $request->getContent(), $secret), $signature);
    }

    /**
     * Paystack attend le montant dans la plus petite unité de la devise (×100, y compris pour le XOF).
     */
    private function toSubunit(int $amount): int
    {
        return $amount * $this->config()['amount_multiplier'];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function methodLabel(array $data): ?string
    {
        $channel = $data['channel'] ?? null;

        return match ($channel) {
            'card' => trim('Carte '.($data['authorization']['brand'] ?? '')),
            'mobile_money' => trim('Mobile Money '.($data['authorization']['bank'] ?? '')),
            'bank', 'bank_transfer' => 'Virement bancaire',
            null => null,
            default => ucfirst(str_replace('_', ' ', (string) $channel)),
        };
    }

    /**
     * @return array{secret_key: string, base_url: string, channels: list<string>, amount_multiplier: int}
     */
    private function config(): array
    {
        $config = config('subscriptions.payment.paystack');
        if (blank($config['secret_key'] ?? null)) {
            throw new RuntimeException('Paystack : renseignez PAYSTACK_SECRET_KEY dans le fichier .env.');
        }

        return $config;
    }
}
