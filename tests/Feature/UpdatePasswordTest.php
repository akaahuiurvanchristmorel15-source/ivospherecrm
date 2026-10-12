<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->user = User::factory()->create([
        'is_active' => true,
        'password' => 'AncienMotDePasse1',
    ]);
});

test('user sees the password page', function () {
    $this->actingAs($this->user)
        ->get(route('password.edit'))
        ->assertOk()
        ->assertSee('Mot de passe actuel');
});

test('user changes their password with the current one', function () {
    $this->actingAs($this->user)
        ->put(route('password.update'), [
            'current_password' => 'AncienMotDePasse1',
            'password' => 'NouveauSecret42',
            'password_confirmation' => 'NouveauSecret42',
        ])
        ->assertRedirect(route('password.edit'))
        ->assertSessionHasNoErrors();

    expect(Hash::check('NouveauSecret42', $this->user->fresh()->password))->toBeTrue();
});

test('wrong current password is rejected', function () {
    $this->actingAs($this->user)
        ->put(route('password.update'), [
            'current_password' => 'MauvaisMotDePasse9',
            'password' => 'NouveauSecret42',
            'password_confirmation' => 'NouveauSecret42',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('AncienMotDePasse1', $this->user->fresh()->password))->toBeTrue();
});

test('new password must be confirmed, strong and different', function (array $input, string $errorField) {
    $this->actingAs($this->user)
        ->put(route('password.update'), ['current_password' => 'AncienMotDePasse1'] + $input)
        ->assertSessionHasErrors($errorField);
})->with([
    'non confirmé' => [['password' => 'NouveauSecret42', 'password_confirmation' => 'Autre12345'], 'password'],
    'trop court' => [['password' => 'abc1', 'password_confirmation' => 'abc1'], 'password'],
    'sans chiffre' => [['password' => 'SansChiffreIci', 'password_confirmation' => 'SansChiffreIci'], 'password'],
    'identique' => [['password' => 'AncienMotDePasse1', 'password_confirmation' => 'AncienMotDePasse1'], 'password'],
]);

test('guests cannot change a password', function () {
    $this->put(route('password.update'), [])->assertRedirect(route('login'));
});
