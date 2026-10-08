<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('user can view analytics and BI forecasts', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($user)->get('/analytics');
    $response->assertStatus(200);

    $response->assertSee('Prévisions Commerciales & BI', false);
    $response->assertSee('Projection du Chiffre d\'Affaires à 3 Mois', false);
    $response->assertSee('Anticipation des Ruptures de Stock', false);
    $response->assertSee('marge d\'incertitude', false);
});
