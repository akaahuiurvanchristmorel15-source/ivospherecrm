<?php

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Support\TenantAccess;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    config([
        'subscriptions.payment.provider' => 'simulation',
        'services.google.client_id' => 'client-id-test',
        'services.google.client_secret' => 'client-secret-test',
    ]);
    Mail::fake();
    TenantAccess::flush();

    $this->plan = SubscriptionPlan::where('code', 'commercial_caisse')->firstOrFail();
});

function googleSignupData(array $overrides = []): array
{
    return array_merge([
        'billing_cycle' => 'monthly',
        'company_name' => 'Boutique Awa',
        'email' => 'contact@awa.ci',
        'phone' => '0700000000',
        'admin_name' => 'Awa Koné',
        'admin_email' => 'awa@awa.ci',
        'password' => 'MotDePasse!2026',
        'password_confirmation' => 'MotDePasse!2026',
        'terms' => '1',
    ], $overrides);
}

function fakeGoogle(bool $emailVerified = true): void
{
    Http::fake([
        'oauth2.googleapis.com/token' => Http::response(['access_token' => 'google-token']),
        'openidconnect.googleapis.com/v1/userinfo' => Http::response([
            'email' => 'Awa.Kone@gmail.com',
            'email_verified' => $emailVerified,
            'name' => 'Awa Koné',
        ]),
    ]);
}

function verifyWithGoogle(SubscriptionPlan $plan): TestResponse
{
    $redirect = test()->get(route('offres.google.redirect', ['plan' => $plan, 'cycle' => 'yearly']));
    parse_str((string) parse_url($redirect->headers->get('Location'), PHP_URL_QUERY), $query);

    return test()->get(route('offres.google.callback', ['state' => $query['state'], 'code' => 'auth-code']));
}

test('signup page offers google verification only when it is configured', function () {
    $this->get(route('offres.signup', $this->plan))->assertOk()->assertSee('Continuer avec Google');

    config(['services.google.client_id' => null]);

    $this->get(route('offres.signup', $this->plan))->assertOk()->assertDontSee('Continuer avec Google');
    $this->get(route('offres.google.redirect', $this->plan))->assertNotFound();
});

test('google redirect asks only for the email and profile', function () {
    $location = $this->get(route('offres.google.redirect', $this->plan))->assertRedirect()->headers->get('Location');

    expect($location)->toStartWith('https://accounts.google.com/o/oauth2/v2/auth?')
        ->toContain('client_id=client-id-test')
        ->toContain('scope=openid+email+profile')
        ->toContain(urlencode(route('offres.google.callback')));
});

test('a verified google email is locked on the signup form and marks the administrator as verified', function () {
    fakeGoogle();

    verifyWithGoogle($this->plan)
        ->assertRedirect(route('offres.signup', ['plan' => $this->plan->code, 'cycle' => 'yearly']));

    $this->get(route('offres.signup', $this->plan))
        ->assertOk()
        ->assertSee('E-mail vérifié par Google')
        ->assertSee('awa.kone@gmail.com')
        ->assertDontSee('Continuer avec Google');

    $this->post(route('offres.register', $this->plan), googleSignupData([
        'email' => 'awa.kone@gmail.com',
        'admin_email' => 'awa.kone@gmail.com',
    ]))->assertRedirect();

    $admin = User::withoutGlobalScopes()->where('email', 'awa.kone@gmail.com')->firstOrFail();

    expect($admin->email_verified_at)->not->toBeNull()
        ->and(Subscription::count())->toBe(1);
});

test('an email typed by hand is not marked as verified', function () {
    $this->post(route('offres.register', $this->plan), googleSignupData())->assertRedirect();

    expect(User::withoutGlobalScopes()->where('email', 'awa@awa.ci')->value('email_verified_at'))->toBeNull();
});

test('google callback rejects a forged state', function () {
    fakeGoogle();
    $this->get(route('offres.google.redirect', $this->plan));

    $this->get(route('offres.google.callback', ['state' => 'forged', 'code' => 'auth-code']))
        ->assertRedirect(route('offres.index'));

    Http::assertNothingSent();
    expect(session('offer_google_identity'))->toBeNull();
});

test('an email google has not verified is refused', function () {
    fakeGoogle(emailVerified: false);

    verifyWithGoogle($this->plan)->assertSessionHas('error');

    expect(session('offer_google_identity'))->toBeNull();
});
