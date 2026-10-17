<?php

use App\Models\Domain;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserInvitation;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->admin = User::where('email', 'admin@ivosphere.com')->firstOrFail();
    $this->role = Role::where('slug', '!=', 'administrateur')->firstOrFail();
    $this->domain = Domain::firstOrFail();
});

function generateInvitationLink(array $overrides = []): string
{
    test()->actingAs(test()->admin)
        ->post(route('admin.users.invitations.store'), array_merge([
            'invite_role' => test()->role->id,
            'invite_domains' => [test()->domain->id],
            'invite_validity' => 7,
        ], $overrides))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('invitation_link');

    $link = session('invitation_link');
    auth()->logout();

    return $link;
}

function invitationSignup(array $overrides = []): array
{
    return array_merge([
        'name' => 'Koffi Yao',
        'email' => 'koffi.yao@gmail.com',
        'phone' => '0700000000',
        'password' => 'MotDePasse!2026',
        'password_confirmation' => 'MotDePasse!2026',
    ], $overrides);
}

test('administrator page offers to generate a signup link', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee("Générer un lien d'inscription", false);
});

test('a generated link creates the account with the chosen role and domains then stops working', function () {
    $link = generateInvitationLink();

    $this->get($link)->assertOk()->assertSee('Créez votre compte')->assertSee($this->role->name);

    $this->post($link, invitationSignup())
        ->assertRedirect(route('login'))
        ->assertSessionHas('success');

    $user = User::where('email', 'koffi.yao@gmail.com')->firstOrFail();

    expect($user->roles->pluck('id')->all())->toBe([$this->role->id])
        ->and($user->domains->pluck('id')->all())->toBe([$this->domain->id])
        ->and($user->is_active)->toBeTrue()
        ->and(UserInvitation::first()->used_at)->not->toBeNull()
        ->and(UserInvitation::first()->user_id)->toBe($user->id);

    $this->get($link)->assertStatus(410)->assertSee("Lien d'inscription invalide", false);
    $this->post($link, invitationSignup(['email' => 'autre@gmail.com']))->assertStatus(410);

    expect(User::where('email', 'autre@gmail.com')->exists())->toBeFalse();
});

test('the database only stores a hash of the link token', function () {
    $link = generateInvitationLink();
    $token = basename($link);

    expect(UserInvitation::first()->token_hash)->toBe(hash('sha256', $token))
        ->not->toBe($token);
});

test('a link reserved to an email address refuses another address', function () {
    $link = generateInvitationLink(['invite_email' => 'Awa@Gmail.com', 'invite_name' => 'Awa Koné']);

    $this->get($link)->assertOk()->assertSee('awa@gmail.com')->assertSee('Awa Koné');

    $this->post($link, invitationSignup(['email' => 'intrus@gmail.com']))->assertSessionHasErrors('email');
    expect(UserInvitation::first()->used_at)->toBeNull();

    $this->post($link, invitationSignup(['email' => 'awa@gmail.com']))->assertRedirect(route('login'));
    expect(User::where('email', 'awa@gmail.com')->value('email_verified_at'))->not->toBeNull();
});

test('expired or revoked links cannot be used', function () {
    $link = generateInvitationLink();
    $this->travel(8)->days();

    $this->get($link)->assertStatus(410);

    $this->travelBack();
    $secondLink = generateInvitationLink();
    $invitation = UserInvitation::latest('id')->first();

    $this->actingAs($this->admin)
        ->delete(route('admin.users.invitations.destroy', $invitation))
        ->assertRedirect();
    auth()->logout();

    $this->get($secondLink)->assertStatus(410);
});

test('only administrators can generate signup links', function () {
    $agent = User::factory()->create(['is_active' => true]);
    $agent->roles()->sync([$this->role->id]);

    $this->actingAs($agent)
        ->post(route('admin.users.invitations.store'), ['invite_role' => $this->role->id, 'invite_validity' => 7])
        ->assertForbidden();

    expect(UserInvitation::count())->toBe(0);
});

test('a link from a subscriber space creates the account in that space within its user limit', function () {
    $plan = SubscriptionPlan::where('code', 'commercial_caisse')->firstOrFail();
    $tenant = Tenant::create(['name' => 'Boutique Awa', 'slug' => 'boutique-awa', 'email' => 'contact@awa.ci', 'phone' => '07',
        'status' => 'active', 'subscription_plan_id' => $plan->id]);

    [, $token] = UserInvitation::generate(['tenant_id' => $tenant->id, 'role_ids' => [$this->role->id], 'expires_at' => now()->addDay()]);

    $this->get(route('invitations.show', $token))->assertOk()->assertSee('Boutique Awa');
    $this->post(route('invitations.register', $token), invitationSignup())->assertRedirect(route('login'));

    expect(User::withoutGlobalScopes()->where('email', 'koffi.yao@gmail.com')->value('tenant_id'))->toBe($tenant->id);

    User::factory()->count($plan->max_users)->create(['tenant_id' => $tenant->id]);
    [, $fullToken] = UserInvitation::generate(['tenant_id' => $tenant->id, 'role_ids' => [$this->role->id], 'expires_at' => now()->addDay()]);

    $this->post(route('invitations.register', $fullToken), invitationSignup(['email' => 'quatrieme@gmail.com']))
        ->assertSessionHas('error');

    expect(User::withoutGlobalScopes()->where('email', 'quatrieme@gmail.com')->exists())->toBeFalse()
        ->and(UserInvitation::withoutGlobalScopes()->where('token_hash', UserInvitation::hashToken($fullToken))->value('used_at'))->toBeNull();
});
