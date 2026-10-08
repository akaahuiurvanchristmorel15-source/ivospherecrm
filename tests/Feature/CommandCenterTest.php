<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('unauthenticated user cannot access command center', function () {
    $response = $this->get('/command-center');
    $response->assertRedirect('/login');
});

test('non-admin user is forbidden from accessing command center', function () {
    $user = User::where('email', 'commercial@ivosphere.com')->first();

    $response = $this->actingAs($user)->get('/command-center');
    $response->assertStatus(403);
});

test('admin can access command center with consolidated KPIs and domain breakdown', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get('/command-center');
    $response->assertStatus(200);

    $response->assertSee('Command Center IVOSPHERE');
    $response->assertSee("Chiffre d'Affaires", false);
    $response->assertSee('Bénéfice Net Estimé', false);
    $response->assertSee('Trésorerie Disponible', false);
    $response->assertSee('Créances Clients Échues', false);
    $response->assertSee('Contribution par Domaine d\'Activité', false);
    $response->assertSee('IVOSPHERE PRINT');
    $response->assertSee('IVOSPHERE TECH');
});

test('command center can filter by period and domain', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get('/command-center?period=today&domain=TECH');
    $response->assertStatus(200);
    $response->assertSee('Command Center IVOSPHERE');
});
