<?php

use App\Mail\SubscriptionConfirmed;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Subscriptions\SubscriptionPdfService;
use App\Support\TenantAccess;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    config(['subscriptions.payment.provider' => 'simulation']);
    Mail::fake();
    TenantAccess::flush();

    // Pas de Chromium pendant les tests : le PDF est remplacé par un contenu factice.
    $this->mock(SubscriptionPdfService::class, function ($mock) {
        $mock->shouldReceive('generate')->andReturnUsing(function (Subscription $subscription) {
            $path = 'subscriptions/Abonnement-'.$subscription->reference.'.pdf';
            Storage::disk('local')->put($path, '%PDF-test');
            $subscription->forceFill(['pdf_path' => $path])->save();

            return $path;
        });
        $mock->shouldReceive('html')->andReturn('<html>recapitulatif</html>');
    });
});

function signupData(array $overrides = []): array
{
    return array_merge([
        'billing_cycle' => 'monthly',
        'company_name' => 'Boutique Grâce',
        'email' => 'contact@grace.ci',
        'phone' => '0700000000',
        'city' => 'Abidjan',
        'admin_name' => 'Grâce Kouamé',
        'admin_email' => 'grace@grace.ci',
        'password' => 'MotDePasse!2026',
        'password_confirmation' => 'MotDePasse!2026',
        'terms' => '1',
    ], $overrides);
}

function subscribeAndPay(string $outcome = 'paid', array $overrides = []): Subscription
{
    $plan = SubscriptionPlan::where('code', 'commercial_caisse')->firstOrFail();

    test()->post(route('offres.register', $plan), signupData($overrides))->assertRedirect();
    $subscription = Subscription::latest('id')->firstOrFail();

    test()->post(route('offres.payment.simulate', $subscription), ['outcome' => $outcome])
        ->assertRedirect(route('offres.payment.return', $subscription));
    test()->get(route('offres.payment.return', $subscription))->assertOk();

    return $subscription->refresh();
}

test('signup refuses disposable or test email addresses', function () {
    $plan = SubscriptionPlan::where('code', 'commercial_caisse')->first();

    $this->post(route('offres.register', $plan), signupData(['email' => 'contact@yopmail.com', 'admin_email' => 'grace@example.com']))
        ->assertSessionHasErrors(['email', 'admin_email']);

    expect(Subscription::count())->toBe(0);
});

test('signup refuses email domains that cannot receive mail', function () {
    config(['subscriptions.email.verify_dns' => true]);
    $plan = SubscriptionPlan::where('code', 'commercial_caisse')->first();

    $this->post(route('offres.register', $plan), signupData(['email' => 'contact@grace.invalid']))
        ->assertSessionHasErrors(['email' => "Le domaine « grace.invalid » ne reçoit pas d'e-mails. Vérifiez l'adresse saisie."]);

    expect(Subscription::count())->toBe(0);
});

test('offers page lists the plans and enterprise plans are not yet subscribable', function () {
    $this->get(route('offres.index'))
        ->assertOk()
        ->assertSee('Commercial Caisse')
        ->assertSee('Business');

    $this->get(route('offres.signup', 'entreprise_business'))->assertRedirect(route('offres.index'));
});

test('signup creates a pending space with its administrator then redirects to payment', function () {
    $plan = SubscriptionPlan::where('code', 'commercial_caisse')->first();

    $this->post(route('offres.register', $plan), signupData())->assertRedirect();

    $subscription = Subscription::latest('id')->first();
    $admin = User::withoutGlobalScopes()->where('email', 'grace@grace.ci')->first();

    expect($subscription->status)->toBe('pending')
        ->and($subscription->amount)->toBe($plan->price_monthly)
        ->and($subscription->tenant->status)->toBe('pending')
        ->and($admin->tenant_id)->toBe($subscription->tenant_id)
        ->and($admin->isAdmin())->toBeTrue();
});

test('a successful payment activates the space, provisions it and emails the pdf', function () {
    $subscription = subscribeAndPay();
    $tenant = $subscription->tenant;

    expect($subscription->isPaid())->toBeTrue()
        ->and($subscription->pdf_path)->not->toBeNull()
        ->and($tenant->hasActiveSubscription())->toBeTrue()
        ->and($tenant->subscription_ends_at->isSameDay(now()->addMonth()))->toBeTrue()
        ->and(Warehouse::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count())->toBe(1);

    Mail::assertSent(SubscriptionConfirmed::class, fn (SubscriptionConfirmed $mail) => $mail->hasTo('grace@grace.ci') && $mail->hasTo('contact@grace.ci'));

    $this->get(route('offres.payment.pdf', $subscription))->assertOk()->assertHeader('Content-Type', 'application/pdf');
});

test('confirming an already paid subscription twice does not extend it again', function () {
    $subscription = subscribeAndPay();
    $endsAt = $subscription->ends_at;

    $this->get(route('offres.payment.return', $subscription))->assertOk();

    expect($subscription->refresh()->ends_at->equalTo($endsAt))->toBeTrue();
    Mail::assertSentCount(1);
});

test('a failed payment leaves the space inactive', function () {
    $subscription = subscribeAndPay('failed');

    expect($subscription->status)->toBe('failed')
        ->and($subscription->tenant->hasActiveSubscription())->toBeFalse();
    Mail::assertNothingSent();
});

test('the pdf of another subscriber cannot be downloaded', function () {
    $subscription = subscribeAndPay();
    session()->forget('subscription_reference');

    $this->get(route('offres.payment.pdf', $subscription))->assertForbidden();
});

test('a subscriber only sees the data and pages of their own space', function () {
    $ivosphereProduct = Product::create(['name' => 'Article Ivosphere', 'sku' => 'IVO-1', 'is_active' => true]);
    $subscription = subscribeAndPay();
    $admin = User::withoutGlobalScopes()->where('email', 'grace@grace.ci')->first();

    $this->actingAs($admin);

    $this->post(route('stock.products.store'), [
        'name' => 'Article Grâce',
        'sku' => 'IVO-1',
        'unit' => 'pièce',
        'purchase_price' => 100,
        'selling_price' => 150,
        'is_active' => 1,
    ]);

    expect(Product::pluck('name')->all())->toBe(['Article Grâce'])
        ->and(Product::withoutGlobalScopes()->find($ivosphereProduct->id)->name)->toBe('Article Ivosphere');

    $this->get(route('dashboard'))->assertRedirect(route('space.home'));
    $this->get(route('space.home'))->assertOk()->assertSee('Boutique Grâce');
    $this->get(route('commercial.pos.index'))->assertOk();
    $this->get(route('rh.employees.index'))->assertForbidden();
    $this->get(route('admin.subscriptions.index'))->assertForbidden();
});

test('an expired subscription only gives access to the subscription page', function () {
    $subscription = subscribeAndPay();
    $subscription->tenant->update(['subscription_ends_at' => now()->subDay()]);
    $admin = User::withoutGlobalScopes()->where('email', 'grace@grace.ci')->first();

    $this->actingAs($admin)
        ->get(route('commercial.pos.index'))
        ->assertRedirect(route('space.subscription'));

    $this->get(route('space.subscription'))->assertOk()->assertSee('Expiré');
});

test('a suspended space is logged out', function () {
    $subscription = subscribeAndPay();
    $subscription->tenant->update(['status' => 'suspended']);
    $admin = User::withoutGlobalScopes()->where('email', 'grace@grace.ci')->first();

    $this->actingAs($admin)->get(route('space.home'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('renewing before the end date extends from the current end date', function () {
    $subscription = subscribeAndPay();
    $firstEnd = $subscription->ends_at;
    $admin = User::withoutGlobalScopes()->where('email', 'grace@grace.ci')->first();

    $this->actingAs($admin)->post(route('space.subscription.renew'), [
        'plan_id' => $subscription->subscription_plan_id,
        'billing_cycle' => 'yearly',
    ])->assertRedirect();

    $renewal = Subscription::latest('id')->first();
    $this->post(route('offres.payment.simulate', $renewal), ['outcome' => 'paid']);
    $this->get(route('offres.payment.return', $renewal))->assertOk();

    expect($renewal->refresh()->ends_at->equalTo($firstEnd->copy()->addYear()))->toBeTrue();
});

test('ivosphere administrators manage subscribers and plan prices', function () {
    $subscription = subscribeAndPay();
    $this->actingAs(User::where('email', 'admin@ivosphere.com')->first());

    $this->get(route('admin.subscriptions.index'))->assertOk()->assertSee('Boutique Grâce');
    $this->get(route('admin.subscriptions.show', $subscription->tenant))->assertOk()->assertSee('grace@grace.ci');

    $plan = SubscriptionPlan::where('code', 'commercial_caisse')->first();
    $this->put(route('admin.subscriptions.plans.update', $plan), [
        'name' => 'Commercial Caisse',
        'price_monthly' => 12000,
        'price_yearly' => 120000,
        'max_users' => 3,
        'is_active' => 1,
        'is_available' => 1,
    ])->assertRedirect();

    expect($plan->refresh()->price_monthly)->toBe(12000);

    $this->patch(route('admin.subscriptions.toggle', $subscription->tenant))->assertRedirect();
    expect($subscription->tenant->refresh()->status)->toBe('suspended');
});
