<?php

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('user can view business calculators and simulators', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($user)->get('/simulators');
    $response->assertStatus(200);

    $response->assertSee('Simulateurs de Devis Métiers', false);
    $response->assertSee('Devis PRINT', false);
    $response->assertSee('Location MEDIA', false);
    $response->assertSee('Budget Event', false);
});

test('delivery lifecycle: schedule and mark delivered', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();
    $customer = Customer::first();

    $scheduleResponse = $this->actingAs($user)->post('/deliveries', [
        'customer_id' => $customer->id,
        'delivery_address' => 'Plateau, Immeuble Horizon, Abidjan',
        'city' => 'Abidjan',
        'driver_name' => 'Kouamé Test',
        'driver_phone' => '+225 07 12 34 56 78',
    ]);
    $scheduleResponse->assertRedirect('/deliveries');

    $delivery = Delivery::where('delivery_address', 'Plateau, Immeuble Horizon, Abidjan')->first();
    expect($delivery)->not->toBeNull();
    expect($delivery->status)->toBe('en_preparation');

    // Update status to livree
    $statusResponse = $this->actingAs($user)->patch("/deliveries/{$delivery->id}/status", [
        'status' => 'livree',
        'recipient_name' => 'M. Bamba',
    ]);
    $statusResponse->assertRedirect();
    expect($delivery->fresh()->status)->toBe('livree');
    expect($delivery->fresh()->delivered_at)->not->toBeNull();
});
