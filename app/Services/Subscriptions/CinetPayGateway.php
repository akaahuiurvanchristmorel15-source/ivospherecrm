<?php

namespace App\Services\Subscriptions;

use App\Models\Subscription;
use Illuminate\Http\Client\ConnectionException;
use RuntimeException;

/**
 * Paiement en ligne via CinetPay (Wave, Orange Money, MTN, Moov, carte bancaire).
 * Documentation : https://docs.cinetpay.com
 */
class CinetPayGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'cinetpay';
    }

    public function initiate(Subscription $subscription): string
    {
        $config = $this->config();
        $tenant = $subscription->tenant;

        try {
            $response = PaymentHttp::request()->post($config['base_url'].'/payment', [
                'apikey' => $config['api_key'],
                'site_id' => $config['site_id'],
                'transaction_id' => $subscription->reference,
                'amount' => $subscription->amount,
                'currency' => $subscription->currency,
                'description' => 'Abonnement '.$subscription->plan->name.' ('.$subscription->billingCycleLabel().')',
                'notify_url' => route('offres.payment.notify'),
                'return_url' => route('offres.payment.return', $subscription->reference),
                'channels' => 'ALL',
                'customer_name' => $tenant->name,
                'customer_surname' => $tenant->name,
                'customer_email' => $tenant->email,
                'customer_phone_number' => $tenant->phone,
                'customer_address' => $tenant->address ?: $tenant->city,
                'customer_city' => $tenant->city,
                'customer_country' => 'CI',
                'customer_state' => 'CI',
                'customer_zip_code' => '00225',
            ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('CinetPay injoignable : '.$exception->getMessage(), 0, $exception);
        }

        $paymentUrl = $response->json('data.payment_url');
        if (! $response->successful() || ! $paymentUrl) {
            throw new RuntimeException('CinetPay : initialisation du paiement impossible ('.($response->json('message') ?? $response->status()).').');
        }

        return $paymentUrl;
    }

    public function verify(Subscription $subscription): PaymentResult
    {
        $config = $this->config();

        try {
            $response = PaymentHttp::request()->post($config['base_url'].'/payment/check', [
                'apikey' => $config['api_key'],
                'site_id' => $config['site_id'],
                'transaction_id' => $subscription->reference,
            ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('CinetPay injoignable : '.$exception->getMessage(), 0, $exception);
        }

        $data = (array) $response->json('data', []);
        $status = strtoupper((string) ($data['status'] ?? ''));
        $amount = isset($data['amount']) ? (int) $data['amount'] : null;

        // Un paiement accepté d'un montant inférieur au prix n'active pas l'abonnement.
        if ($status === 'ACCEPTED' && $amount !== null && $amount >= $subscription->amount) {
            return new PaymentResult(PaymentResult::PAID, (string) ($data['operator_id'] ?? $subscription->reference), $data['payment_method'] ?? null, $amount, $data);
        }

        if (in_array($status, ['REFUSED', 'CANCELED', 'CANCELLED'], true)) {
            return new PaymentResult(PaymentResult::FAILED, null, $data['payment_method'] ?? null, $amount, $data);
        }

        return new PaymentResult(PaymentResult::PENDING, null, null, $amount, $data);
    }

    /**
     * @return array{api_key: string, site_id: string, base_url: string}
     */
    private function config(): array
    {
        $config = config('subscriptions.payment.cinetpay');
        if (blank($config['api_key']) || blank($config['site_id'])) {
            throw new RuntimeException('CinetPay : renseignez CINETPAY_API_KEY et CINETPAY_SITE_ID dans le fichier .env.');
        }

        return $config;
    }
}
