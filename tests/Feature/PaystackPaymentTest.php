<?php

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Subscriptions\SubscriptionPdfService;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    config([
        'subscriptions.payment.provider' => 'paystack',
        'subscriptions.payment.paystack.secret_key' => 'sk_test_ivosphere',
    ]);
    Mail::fake();
    $this->mock(SubscriptionPdfService::class, function ($mock) {
        $mock->shouldReceive('generate')->andReturnNull();
    });

    $this->plan = SubscriptionPlan::where('code', 'commercial_caisse')->first();
});

function registerForPaystack(): Subscription
{
    test()->post(route('offres.register', test()->plan), [
        'billing_cycle' => 'monthly', 'company_name' => 'Boutique Awa', 'email' => 'contact@awa.ci', 'phone' => '0700000000',
        'admin_name' => 'Awa Koné', 'admin_email' => 'awa@awa.ci', 'password' => 'MotDePasse!2026', 'password_confirmation' => 'MotDePasse!2026', 'terms' => '1',
    ]);

    return Subscription::latest('id')->firstOrFail();
}

function fakePaystackVerify(string $status, int $amountInSubunits, string $currency = 'XOF'): void
{
    Http::fake([
        'api.paystack.co/transaction/initialize' => Http::response(['status' => true, 'data' => ['authorization_url' => 'https://checkout.paystack.com/abc123']]),
        'api.paystack.co/transaction/verify/*' => Http::response(['status' => true, 'data' => [
            'id' => 98765, 'status' => $status, 'amount' => $amountInSubunits, 'currency' => $currency,
            'channel' => 'mobile_money', 'authorization' => ['bank' => 'MTN'],
        ]]),
    ]);
}

test('signup redirects to the Paystack checkout with the amount in subunits', function () {
    fakePaystackVerify('success', 0);

    $this->post(route('offres.register', $this->plan), [
        'billing_cycle' => 'monthly', 'company_name' => 'Boutique Awa', 'email' => 'contact@awa.ci', 'phone' => '0700000000',
        'admin_name' => 'Awa Koné', 'admin_email' => 'awa@awa.ci', 'password' => 'MotDePasse!2026', 'password_confirmation' => 'MotDePasse!2026', 'terms' => '1',
    ])->assertRedirect('https://checkout.paystack.com/abc123');

    $subscription = Subscription::latest('id')->first();

    Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/transaction/initialize')
        && $request->hasHeader('Authorization', 'Bearer sk_test_ivosphere')
        && $request['amount'] === $this->plan->price_monthly * 100
        && $request['currency'] === 'XOF'
        && $request['reference'] === $subscription->reference
        && $request['email'] === 'contact@awa.ci');
    expect($subscription->payment_provider)->toBe('paystack');
});

test('a successful Paystack payment activates the subscription on return', function () {
    fakePaystackVerify('success', 15000 * 100);
    $subscription = registerForPaystack();

    $this->get(route('offres.payment.return', $subscription).'?trxref='.$subscription->reference)->assertOk()->assertSee('Bienvenue sur IVOSPHERE');

    $subscription->refresh();
    expect($subscription->isPaid())->toBeTrue()
        ->and($subscription->payment_method)->toBe('Mobile Money MTN')
        ->and($subscription->payment_transaction_id)->toBe('98765')
        ->and($subscription->tenant->hasActiveSubscription())->toBeTrue();
});

test('an underpaid or failed Paystack transaction does not activate the subscription', function (string $status, int $amount) {
    fakePaystackVerify($status, $amount);
    $subscription = registerForPaystack();

    $this->get(route('offres.payment.return', $subscription))->assertOk();

    expect($subscription->refresh()->isPaid())->toBeFalse();
})->with([
    'montant insuffisant' => ['success', 100 * 100],
    'paiement échoué' => ['failed', 15000 * 100],
]);

test('Paystack webhooks must be signed with the secret key', function () {
    fakePaystackVerify('success', 15000 * 100);
    $subscription = registerForPaystack();
    $payload = json_encode(['event' => 'charge.success', 'data' => ['reference' => $subscription->reference]]);

    $this->call('POST', route('offres.payment.notify'), [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_PAYSTACK_SIGNATURE' => 'faux'], $payload)
        ->assertUnauthorized();
    expect($subscription->refresh()->isPaid())->toBeFalse();

    $signature = hash_hmac('sha512', $payload, 'sk_test_ivosphere');
    $this->call('POST', route('offres.payment.notify'), [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_PAYSTACK_SIGNATURE' => $signature], $payload)
        ->assertOk();
    expect($subscription->refresh()->isPaid())->toBeTrue();
});
