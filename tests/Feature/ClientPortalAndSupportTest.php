<?php

use App\Models\Customer;
use App\Models\SupportTicket;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('user can view dedicated client portal with financial and operational data', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();
    $customer = Customer::first();

    $response = $this->actingAs($user)->get('/client-portal?customer_id='.$customer->id);
    $response->assertStatus(200);

    $response->assertSee('Portail Extranet Client', false);
    $response->assertSee('Total Facturé', false);
    $response->assertSee('Règlements Effectués', false);
    $response->assertSee('Devis & Propositions', false);
});

test('support ticketing workflow: creation, reply and status update', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();
    $customer = Customer::first();

    // 1. Create Ticket
    $createResponse = $this->actingAs($user)->post('/support', [
        'subject' => 'Problème de tirage décalé',
        'description' => 'Le calibrage des couleurs ne correspond pas à la maquette validée.',
        'category' => 'technique',
        'priority' => 'urgente',
        'customer_id' => $customer->id,
    ]);

    $ticket = SupportTicket::where('subject', 'Problème de tirage décalé')->first();
    expect($ticket)->not->toBeNull();
    expect($ticket->status)->toBe('nouveau');
    expect($ticket->messages)->toHaveCount(1);

    // 2. View Ticket
    $showResponse = $this->actingAs($user)->get("/support/{$ticket->id}");
    $showResponse->assertStatus(200);
    $showResponse->assertSee($ticket->ticket_number);

    // 3. Post reply
    $replyResponse = $this->actingAs($user)->post("/support/{$ticket->id}/reply", [
        'message' => 'Nous relançons le tirage immédiatement avec le profil colorimétrique corrigé.',
        'is_internal_note' => 0,
    ]);
    $replyResponse->assertRedirect("/support/{$ticket->id}");
    expect($ticket->fresh()->messages)->toHaveCount(2);

    // 4. Update status to resolved
    $statusResponse = $this->actingAs($user)->patch("/support/{$ticket->id}/status", [
        'status' => 'resolu',
    ]);
    expect($ticket->fresh()->status)->toBe('resolu');
    expect($ticket->fresh()->closed_at)->not->toBeNull();
});
