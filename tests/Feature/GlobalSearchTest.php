<?php

use App\Models\Customer;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('spotlight search returns json results across multiple erp entities', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();
    $customer = Customer::first();

    $response = $this->actingAs($user)->getJson('/api/search?q='.substr($customer->first_name, 0, 3));
    $response->assertStatus(200);

    $response->assertJsonStructure([
        'query',
        'results',
        'count',
    ]);
});
