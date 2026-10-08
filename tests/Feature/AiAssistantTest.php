<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('authenticated user can view AI space', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($user)->get('/ai');
    $response->assertStatus(200);
    $response->assertSee('Espace IVOSPHERE AI');
    $response->assertSee('Assistant de Gestion');
    $response->assertSee('Studio de Génération');
});

test('ai assistant processes natural language queries on live ERP data', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    // Query 1: Revenue by domain
    $response = $this->actingAs($user)->postJson('/ai/query', [
        'query' => 'Donne-moi le chiffre d\'affaires de TECH ce mois-ci',
    ]);
    $response->assertStatus(200);
    $response->assertJsonStructure(['intent', 'answer', 'data', 'suggestions']);
    expect($response->json('intent'))->toBe('revenue_inquiry');

    // Query 2: Stock alerts
    $response2 = $this->actingAs($user)->postJson('/ai/query', [
        'query' => 'Quels produits sont bientôt en rupture ?',
    ]);
    $response2->assertStatus(200);
    expect($response2->json('intent'))->toBe('stock_alerts');

    // Query 3: Overdue invoices
    $response3 = $this->actingAs($user)->postJson('/ai/query', [
        'query' => 'Quelles factures sont en retard ?',
    ]);
    $response3->assertStatus(200);
    expect($response3->json('intent'))->toBe('overdue_invoices');
});

test('ai studio generates professional business content', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($user)->postJson('/ai/generate', [
        'type' => 'commercial_pitch',
        'topic' => 'Solution Impression Haute Définition',
        'domain' => 'IVOSPHERE PRINT',
        'target' => 'Directions Marketing B2B',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['title', 'content', 'format', 'tags']);
    expect($response->json('content'))->toContain('IVOSPHERE PRINT');
});
