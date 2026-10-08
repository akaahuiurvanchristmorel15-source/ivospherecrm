<?php

use App\Models\User;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);
});

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSee('GROUPE IVOSPHERE');
    $response->assertSee('admin@ivosphere.com');
});

test('users can authenticate using the login screen', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));
});

test('users cannot authenticate with invalid password', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('deactivated users cannot authenticate', function () {
    $user = User::where('email', 'commercial@ivosphere.com')->first();
    $user->update(['is_active' => false]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('users can log out', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});
