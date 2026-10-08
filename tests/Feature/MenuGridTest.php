<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('unauthenticated user cannot access menu grid', function () {
    $response = $this->get('/menu');
    $response->assertRedirect('/login');
});

test('admin can view all menu sections including administration and roles', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get('/menu');
    $response->assertStatus(200);

    $response->assertSee('Menu Général', false);

    // Admin sees all sections
    $response->assertSee('Direction', false);
    $response->assertSee('IVOSPHERE AI', false);
    $response->assertSee('Commercial & CRM', false);
    $response->assertSee('Finance & Trésorerie', false);
    $response->assertSee('Stocks & Logistique', false);
    $response->assertSee('Ressources Humaines', false);
    $response->assertSee('PRINT', false);
    $response->assertSee('SPORT', false);
    $response->assertSee('TECH', false);
    $response->assertSee('MEDIA', false);
    $response->assertSee('ASSURANCE', false);

    // Administration & Rôles
    $response->assertSee('Rôles & Permissions', false);
    $response->assertSee('Paramètres Généraux', false);
    $response->assertSee(route('admin.roles.index'));
    $response->assertSee(route('admin.settings.index'));
});

test('commercial user sees permitted items and not admin-only items', function () {
    $commercial = User::where('email', 'commercial@ivosphere.com')->first();

    $response = $this->actingAs($commercial)->get('/menu');
    $response->assertStatus(200);

    // Should see commercial and domain sections
    $response->assertSee('Commercial & CRM', false);
    $response->assertSee('PRINT', false);

    // Should NOT see admin-only items (Direction, AI, Alertes, Rôles, Paramètres)
    $response->assertDontSee('Cockpit exécutif', false);
    $response->assertDontSee('Assistant IA, studio créatif', false);
    $response->assertDontSee('Rôles & Permissions', false);
    $response->assertDontSee('Paramètres Généraux', false);
});

test('topbar features menu button', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get('/dashboard');
    $response->assertStatus(200);

    $response->assertSee('Menu des 8 Pôles', false);
    $response->assertSee(route('menu.index'));
});

test('menu sections contain responsive mobile horizontal scroll classes', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get('/menu');
    $response->assertStatus(200);

    // Verify horizontal scroll classes and snap
    $response->assertSee('overflow-x-auto', false);
    $response->assertSee('snap-x', false);
    $response->assertSee('snap-mandatory', false);
    $response->assertSee('w-[76vw]', false);
});
