<?php

use App\Models\InternalChannel;
use App\Models\InternalMessage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('user can view collaborative calendar aggregating events and appointments', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($user)->get('/calendar');
    $response->assertStatus(200);

    $response->assertSee('Calendrier Collaboratif IVOSPHERE', false);
    $response->assertSee('Commercial & Devis', false);
    $response->assertSee('Shootings Média & Events', false);
});

test('internal messaging allows channels and direct messages between team members', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();
    $otherUser = User::where('email', '!=', 'admin@ivosphere.com')->first();

    // 1. View messaging
    $response = $this->actingAs($user)->get('/messages');
    $response->assertStatus(200);
    $response->assertSee('Communication interne', false);
    $response->assertSee('Salons', false);

    // 2. Post channel message
    $channel = InternalChannel::first();
    $postResponse = $this->actingAs($user)->post('/messages', [
        'channel_id' => $channel->id,
        'content' => 'Point d\'équipe à 14h dans la salle principale.',
    ]);
    $postResponse->assertRedirect();

    $msg = InternalMessage::where('content', 'Point d\'équipe à 14h dans la salle principale.')->first();
    expect($msg)->not->toBeNull();
    expect($msg->channel_id)->toBe($channel->id);

    // 3. Post direct message
    $dmResponse = $this->actingAs($user)->post('/messages', [
        'recipient_id' => $otherUser->id,
        'content' => 'As-tu validé le bon à tirer du client ?',
    ]);
    $dmResponse->assertRedirect();

    $dm = InternalMessage::where('recipient_id', $otherUser->id)->first();
    expect($dm)->not->toBeNull();
});
