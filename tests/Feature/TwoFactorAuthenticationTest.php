<?php

use App\Models\Role;
use App\Models\User;
use App\Services\TwoFactorAuthenticator;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->travelTo(now()->setTime(10, 0, 5));
    $this->authenticator = app(TwoFactorAuthenticator::class);
    $this->user = User::factory()->create(['is_active' => true, 'password' => 'MotDePasse42']);
});

/**
 * Active directement la double authentification du compte et renvoie sa clé secrète.
 */
function enableTwoFactorFor(User $user): string
{
    $secret = app(TwoFactorAuthenticator::class)->generateSecret();

    $user->forceFill([
        'two_factor_secret' => $secret,
        'two_factor_recovery_codes' => ['AAAAA-11111', 'BBBBB-22222'],
        'two_factor_confirmed_at' => now(),
    ])->save();

    return $secret;
}

function makeAdministrator(User $user): void
{
    test()->seed(RolePermissionSeeder::class);
    $user->roles()->attach(Role::where('slug', 'administrateur')->value('id'));
}

test('totp codes match the RFC 6238 reference values', function () {
    // Clé de test « 12345678901234567890 » de la RFC 6238 (SHA-1), tronquée à 6 chiffres.
    $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

    expect($this->authenticator->codeAt($secret, 59))->toBe('287082')
        ->and($this->authenticator->codeAt($secret, 1111111109))->toBe('081804')
        ->and($this->authenticator->codeAt($secret, 2000000000))->toBe('279037');
});

test('user enables two factor authentication from their profile', function () {
    $this->actingAs($this->user)
        ->post(route('two-factor.enable'), ['current_password' => 'MotDePasse42'])
        ->assertSessionHasNoErrors();

    $user = $this->user->fresh();
    expect($user->two_factor_secret)->not->toBeNull()
        ->and($user->hasTwoFactorEnabled())->toBeFalse();

    $this->actingAs($user)->get(route('password.edit'))
        ->assertOk()
        ->assertSee('Scannez ce QR code')
        ->assertSee('<svg', false);

    $this->actingAs($user)
        ->post(route('two-factor.confirm'), ['code' => $this->authenticator->codeAt($user->two_factor_secret)])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('two_factor_recovery_codes', $user->fresh()->two_factor_recovery_codes);

    expect($user->fresh()->hasTwoFactorEnabled())->toBeTrue()
        ->and($user->fresh()->two_factor_recovery_codes)->toHaveCount(8);
});

test('enabling two factor authentication requires the current password', function () {
    $this->actingAs($this->user)
        ->post(route('two-factor.enable'), ['current_password' => 'Mauvais99'])
        ->assertSessionHasErrorsIn('twoFactor', 'current_password');

    expect($this->user->fresh()->two_factor_secret)->toBeNull();
});

test('activation is not confirmed with an invalid code', function () {
    $this->actingAs($this->user)->post(route('two-factor.enable'), ['current_password' => 'MotDePasse42']);

    $this->actingAs($this->user)
        ->post(route('two-factor.confirm'), ['code' => '000000'])
        ->assertSessionHasErrorsIn('twoFactor', 'code');

    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});

test('user without two factor authentication logs in with the password only', function () {
    $this->post('/login', ['email' => $this->user->email, 'password' => 'MotDePasse42'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($this->user);
});

test('password alone does not log in a user with two factor authentication', function () {
    enableTwoFactorFor($this->user);

    $this->post('/login', ['email' => $this->user->email, 'password' => 'MotDePasse42'])
        ->assertRedirect(route('two-factor.challenge'));

    $this->assertGuest();
    $this->get(route('two-factor.challenge'))->assertOk()->assertSee('Code de vérification');
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('user logs in with a valid authenticator code', function () {
    $secret = enableTwoFactorFor($this->user);
    $this->post('/login', ['email' => $this->user->email, 'password' => 'MotDePasse42']);

    $this->post(route('two-factor.challenge.store'), ['code' => $this->authenticator->codeAt($secret)])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($this->user);
});

test('a code from the previous period is still accepted', function () {
    $secret = enableTwoFactorFor($this->user);
    $this->post('/login', ['email' => $this->user->email, 'password' => 'MotDePasse42']);

    $this->post(route('two-factor.challenge.store'), ['code' => $this->authenticator->codeAt($secret, now()->getTimestamp() - 30)]);

    $this->assertAuthenticatedAs($this->user);
});

test('an invalid code is rejected', function () {
    $secret = enableTwoFactorFor($this->user);
    $this->post('/login', ['email' => $this->user->email, 'password' => 'MotDePasse42']);

    $expiredCode = $this->authenticator->codeAt($secret, now()->getTimestamp() - 120);

    $this->post(route('two-factor.challenge.store'), ['code' => $expiredCode])
        ->assertSessionHasErrors('code');

    $this->assertGuest();
});

test('an authenticator code cannot be used twice', function () {
    $secret = enableTwoFactorFor($this->user);
    $code = $this->authenticator->codeAt($secret);

    $this->post('/login', ['email' => $this->user->email, 'password' => 'MotDePasse42']);
    $this->post(route('two-factor.challenge.store'), ['code' => $code]);
    $this->post('/logout');

    $this->post('/login', ['email' => $this->user->email, 'password' => 'MotDePasse42']);
    $this->post(route('two-factor.challenge.store'), ['code' => $code])
        ->assertSessionHasErrors('code');

    $this->assertGuest();
});

test('code attempts are limited', function () {
    enableTwoFactorFor($this->user);
    $this->post('/login', ['email' => $this->user->email, 'password' => 'MotDePasse42']);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('two-factor.challenge.store'), ['code' => '000000']);
    }

    $this->post(route('two-factor.challenge.store'), ['code' => '000000'])
        ->assertSessionHasErrors(['code' => 'Trop de tentatives. Réessayez dans 60 secondes.']);
});

test('a recovery code logs the user in once', function () {
    enableTwoFactorFor($this->user);

    $this->post('/login', ['email' => $this->user->email, 'password' => 'MotDePasse42']);
    $this->post(route('two-factor.challenge.store'), ['recovery_code' => 'aaaaa-11111'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($this->user);
    expect($this->user->fresh()->two_factor_recovery_codes)->toBe(['BBBBB-22222']);

    $this->post('/logout');
    $this->post('/login', ['email' => $this->user->email, 'password' => 'MotDePasse42']);
    $this->post(route('two-factor.challenge.store'), ['recovery_code' => 'AAAAA-11111'])
        ->assertSessionHasErrors('recovery_code');

    $this->assertGuest();
});

test('the challenge expires ten minutes after the password step', function () {
    $secret = enableTwoFactorFor($this->user);
    $this->post('/login', ['email' => $this->user->email, 'password' => 'MotDePasse42']);

    $this->travel(11)->minutes();

    $this->post(route('two-factor.challenge.store'), ['code' => $this->authenticator->codeAt($secret)])
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('the challenge page requires a verified password first', function () {
    $this->get(route('two-factor.challenge'))->assertRedirect(route('login'));
});

test('user regenerates their recovery codes', function () {
    enableTwoFactorFor($this->user);

    $this->actingAs($this->user)
        ->post(route('two-factor.recovery-codes'), ['current_password' => 'MotDePasse42'])
        ->assertSessionHas('two_factor_recovery_codes');

    expect($this->user->fresh()->two_factor_recovery_codes)->toHaveCount(8)->not->toContain('AAAAA-11111');
});

test('user disables two factor authentication with their password', function () {
    enableTwoFactorFor($this->user);

    $this->actingAs($this->user)
        ->delete(route('two-factor.disable'), ['current_password' => 'Mauvais99'])
        ->assertSessionHasErrorsIn('twoFactor', 'current_password');
    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeTrue();

    $this->actingAs($this->user)
        ->delete(route('two-factor.disable'), ['current_password' => 'MotDePasse42'])
        ->assertSessionHasNoErrors();

    $user = $this->user->fresh();
    expect($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_recovery_codes)->toBeNull();

    $this->post('/logout');
    $this->post('/login', ['email' => $user->email, 'password' => 'MotDePasse42']);
    $this->assertAuthenticatedAs($user);
});

test('an administrator cannot disable two factor authentication', function () {
    makeAdministrator($this->user);
    enableTwoFactorFor($this->user);

    $this->actingAs($this->user)
        ->delete(route('two-factor.disable'), ['current_password' => 'MotDePasse42'])
        ->assertSessionHas('error');

    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeTrue();
});

test('an administrator without two factor authentication must enable it after logging in', function () {
    makeAdministrator($this->user);

    $this->post('/login', ['email' => $this->user->email, 'password' => 'MotDePasse42']);

    $this->get(route('dashboard'))->assertRedirect(route('password.edit'));
    $this->get(route('admin.users.index'))->assertRedirect(route('password.edit'));
    $this->get(route('password.edit'))->assertOk()->assertSee('Obligatoire pour votre rôle');

    $this->post(route('two-factor.enable'), ['current_password' => 'MotDePasse42']);
    $this->post(route('two-factor.confirm'), ['code' => $this->authenticator->codeAt($this->user->fresh()->two_factor_secret)]);

    $this->get(route('dashboard'))->assertOk();
});

test('an administrator resets the two factor authentication of another user', function () {
    $administrator = User::factory()->create(['is_active' => true]);
    makeAdministrator($administrator);
    enableTwoFactorFor($this->user);

    $this->actingAs($administrator)
        ->delete(route('admin.users.two-factor.reset', $this->user))
        ->assertSessionHas('success');

    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});

test('a non administrator cannot reset two factor authentication', function () {
    $otherUser = User::factory()->create(['is_active' => true]);
    enableTwoFactorFor($otherUser);

    $this->actingAs($this->user)
        ->delete(route('admin.users.two-factor.reset', $otherUser))
        ->assertForbidden();

    expect($otherUser->fresh()->hasTwoFactorEnabled())->toBeTrue();
});
