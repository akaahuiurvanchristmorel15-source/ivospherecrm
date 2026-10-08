<?php

use App\Models\AutomationRule;
use App\Models\CustomWorkflow;
use App\Models\SmartAlert;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('user can view smart alerts and resolve an alert', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $alert = SmartAlert::create([
        'type' => 'unpaid_invoice',
        'title' => 'Test Alerte Facture Impayée',
        'priority' => 'haute',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get('/alerts');
    $response->assertStatus(200);
    $response->assertSee('Test Alerte Facture Impayée');

    // Resolve alert
    $resolveResponse = $this->actingAs($user)->patch("/alerts/{$alert->id}/resolve");
    $resolveResponse->assertRedirect();

    expect($alert->fresh()->status)->toBe('resolved');
});

test('user can trigger automated checks and create custom rules', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $checkResponse = $this->actingAs($user)->post('/alerts/run-checks');
    $checkResponse->assertRedirect();

    // Create automation rule
    $ruleResponse = $this->actingAs($user)->post('/automations/rules', [
        'name' => 'Relance Facture 15 jours',
        'trigger_event' => 'invoice_overdue_7d',
        'description' => 'Alerter le pôle comptabilité',
    ]);
    $ruleResponse->assertRedirect('/automations');

    $rule = AutomationRule::where('name', 'Relance Facture 15 jours')->first();
    expect($rule)->not->toBeNull();

    // Toggle rule
    $this->actingAs($user)->patch("/automations/rules/{$rule->id}/toggle");
    expect($rule->fresh()->is_active)->toBeFalse();
});

test('user can create a custom multi-step workflow', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($user)->post('/automations/workflows', [
        'name' => 'Validation Dépense Exceptionnelle',
        'module' => 'expenses',
        'description' => 'Circuit à 2 niveaux',
        'steps' => [
            ['name' => 'Vérification Comptable', 'time_limit_hours' => 24],
            ['name' => 'Signature Direction', 'time_limit_hours' => 48],
        ],
    ]);

    $response->assertRedirect('/automations');
    $workflow = CustomWorkflow::where('name', 'Validation Dépense Exceptionnelle')->first();
    expect($workflow)->not->toBeNull();
    expect($workflow->steps)->toHaveCount(2);
});
