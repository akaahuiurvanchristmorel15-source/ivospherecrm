<?php

use App\Models\Domain;
use App\Models\Role;
use App\Models\SmartAlert;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('authenticated user can access notification center and view active notifications', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $alert = SmartAlert::create([
        'type' => 'stock_low',
        'title' => 'Stock critique sur Papier A4',
        'description' => 'Le stock actuel est inférieur au seuil de sécurité.',
        'priority' => 'urgente',
        'status' => 'active',
        'action_url' => '/stock',
    ]);

    $response = $this->actingAs($user)->get(route('notifications.index'));

    $response->assertStatus(200);
    $response->assertSee('Centre de Notifications');
    $response->assertSee('Stock critique sur Papier A4');
    $response->assertSee('Stock & Logistique');
});

test('user can mark a single notification as read', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $alert = SmartAlert::create([
        'type' => 'invoice_overdue',
        'title' => 'Facture en retard FAC-2026-001',
        'priority' => 'haute',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('notifications.read', $alert));
    $response->assertRedirect();

    expect($alert->fresh()->status)->toBe('read');
});

test('user can mark all active notifications as read', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    SmartAlert::create([
        'type' => 'invoice_overdue',
        'title' => 'Facture 1',
        'priority' => 'haute',
        'status' => 'active',
    ]);

    SmartAlert::create([
        'type' => 'stock_low',
        'title' => 'Stock 1',
        'priority' => 'moyenne',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('notifications.mark-all-read'));
    $response->assertRedirect();

    expect(SmartAlert::where('status', 'active')->count())->toBe(0);
    expect(SmartAlert::where('status', 'read')->count())->toBeGreaterThanOrEqual(2);
});

test('user can resolve and dismiss notifications', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $alert1 = SmartAlert::create([
        'type' => 'custom',
        'title' => 'Alerte à résoudre',
        'priority' => 'moyenne',
        'status' => 'active',
    ]);

    $alert2 = SmartAlert::create([
        'type' => 'custom',
        'title' => 'Alerte à ignorer',
        'priority' => 'moyenne',
        'status' => 'active',
    ]);

    $this->actingAs($user)->patch(route('notifications.resolve', $alert1))->assertRedirect();
    expect($alert1->fresh()->status)->toBe('resolved');

    $this->actingAs($user)->patch(route('notifications.dismiss', $alert2))->assertRedirect();
    expect($alert2->fresh()->status)->toBe('dismissed');
});

test('user can trigger automated checks and get unread count via api', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($user)->post(route('notifications.run-checks'));
    $response->assertRedirect();

    $countResponse = $this->actingAs($user)->getJson(route('notifications.unread-count'));
    $countResponse->assertStatus(200);
    $countResponse->assertJsonStructure(['count']);
});

test('non-admin user only receives notifications for their assigned domains or global', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $printDomain = Domain::where('code', 'PRINT')->first();
    $techDomain = Domain::where('code', 'TECH')->first();

    // Create an agent user assigned only to PRINT domain
    $agentUser = User::factory()->create([
        'all_domains' => false,
    ]);

    $agentRole = Role::firstOrCreate(['slug' => 'agent'], ['name' => 'Agent Métier', 'domain' => 'global']);
    $agentUser->roles()->attach($agentRole);

    if ($printDomain) {
        $agentUser->domains()->attach($printDomain);
    }

    $printAlert = SmartAlert::create([
        'type' => 'domain_alert',
        'title' => 'Alerte Imprimerie Spécifique',
        'priority' => 'haute',
        'status' => 'active',
        'domain_id' => $printDomain ? $printDomain->id : null,
    ]);

    $techAlert = SmartAlert::create([
        'type' => 'domain_alert',
        'title' => 'Alerte Tech Serveur Spécifique',
        'priority' => 'haute',
        'status' => 'active',
        'domain_id' => $techDomain ? $techDomain->id : null,
    ]);

    $response = $this->actingAs($agentUser)->get(route('notifications.index'));
    $response->assertStatus(200);

    if ($printDomain && $techDomain) {
        $response->assertSee('Alerte Imprimerie Spécifique');
        $response->assertDontSee('Alerte Tech Serveur Spécifique');
    }
});
