<?php

use App\Models\Domain;
use App\Models\User;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);
});

test('admin can view domains index with CRUD action buttons', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get(route('admin.domains.index'));

    $response->assertStatus(200);
    $response->assertSee('Gestion des Domaines');
    $response->assertSee('Ajouter Plusieurs Domaines Simultanément');
    $response->assertSee('Nouveau Domaine');
    $response->assertSee('Modifier');
    $response->assertSee('Supprimer');
    $response->assertSee('IVOSPHERE PRINT');
});

test('admin can view domains create page', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get(route('admin.domains.create'));

    $response->assertStatus(200);
    $response->assertSee('Ajout de Domaines');
    $response->assertSee('Ajout Multiple (Plusieurs domaines à la fois)');
    $response->assertSee('Ajout Simple (Un seul domaine)');
});

test('admin can view domain edit page', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $domain = Domain::where('code', 'PRINT')->first();

    $response = $this->actingAs($admin)->get(route('admin.domains.edit', $domain));

    $response->assertStatus(200);
    $response->assertSee('Modifier le Domaine');
    $response->assertSee($domain->name);
    $response->assertSee('Enregistrer les modifications');
});

test('admin can create a single domain', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->post(route('admin.domains.store'), [
        'name' => 'IVOSPHERE AGRO',
        'code' => 'AGRO',
        'description' => 'Agrobusiness et transformation',
        'color' => 'emerald',
        'icon' => 'globe-alt',
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.domains.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('domains', [
        'name' => 'IVOSPHERE AGRO',
        'code' => 'AGRO',
        'color' => 'emerald',
        'status' => 'actif',
    ]);
});

test('admin can add multiple domains in a single batch', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $payload = [
        'domains' => [
            [
                'name' => 'IVOSPHERE LOGISTIQUE',
                'code' => 'LOGISTIQUE',
                'description' => 'Transport et entreposage',
                'color' => 'teal',
                'icon' => 'truck',
                'is_active' => 1,
            ],
            [
                'name' => 'IVOSPHERE IMMOBILIER',
                'code' => 'IMMOBILIER',
                'description' => 'Gestion de patrimoine et location',
                'color' => 'amber',
                'icon' => 'building-office',
                'is_active' => 1,
            ],
            [
                'name' => 'IVOSPHERE FORMATION',
                'code' => 'FORMATION',
                'description' => 'Académie et séminaires',
                'color' => 'rose',
                'icon' => 'academic-cap',
                'is_active' => 1,
            ],
        ],
    ];

    $response = $this->actingAs($admin)->post(route('admin.domains.batch'), $payload);

    $response->assertRedirect(route('admin.domains.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('domains', [
        'name' => 'IVOSPHERE LOGISTIQUE',
        'code' => 'LOGISTIQUE',
        'color' => 'teal',
        'status' => 'actif',
    ]);

    $this->assertDatabaseHas('domains', [
        'name' => 'IVOSPHERE IMMOBILIER',
        'code' => 'IMMOBILIER',
        'color' => 'amber',
    ]);

    $this->assertDatabaseHas('domains', [
        'name' => 'IVOSPHERE FORMATION',
        'code' => 'FORMATION',
        'color' => 'rose',
    ]);
});

test('batch store rejects duplicate domain codes', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $payload = [
        'domains' => [
            [
                'name' => 'IVOSPHERE PRINT BIS',
                'code' => 'PRINT', // Already exists in seed
                'color' => 'indigo',
            ],
        ],
    ];

    $response = $this->actingAs($admin)->post(route('admin.domains.batch'), $payload);

    $response->assertSessionHasErrors(['domains.0.code']);
});

test('admin can toggle domain status', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $domain = Domain::where('code', 'SPORT')->first();
    $initialState = $domain->is_active;

    $response = $this->actingAs($admin)->patch(route('admin.domains.toggle', $domain));

    $response->assertRedirect();
    expect($domain->fresh()->is_active)->toBe(! $initialState);
});

test('admin can update a domain', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $domain = Domain::create([
        'name' => 'Domaine Temporaire',
        'code' => 'TEMP-DOM',
        'description' => 'Description test',
        'color' => 'indigo',
        'icon' => 'briefcase',
        'status' => 'actif',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->put(route('admin.domains.update', $domain), [
        'name' => 'Domaine Renommé',
        'code' => 'TEMP-RENOMME',
        'description' => 'Nouvelle description',
        'color' => 'emerald',
        'icon' => 'truck',
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.domains.index'));
    expect($domain->fresh()->name)->toBe('Domaine Renommé');
    expect($domain->fresh()->code)->toBe('TEMP-RENOMME');
    expect($domain->fresh()->color)->toBe('emerald');
});

test('admin can delete a domain and its user associations are safely detached', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $domain = Domain::create([
        'name' => 'Domaine A Supprimer',
        'code' => 'DOM-DEL',
        'color' => 'slate',
        'is_active' => true,
    ]);

    // Attach admin to this domain
    $domain->users()->sync([$admin->id]);
    expect($domain->users()->count())->toBe(1);

    $response = $this->actingAs($admin)->delete(route('admin.domains.destroy', $domain));

    $response->assertRedirect(route('admin.domains.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('domains', ['id' => $domain->id]);
    $this->assertDatabaseMissing('domain_user', ['domain_id' => $domain->id]);
});
