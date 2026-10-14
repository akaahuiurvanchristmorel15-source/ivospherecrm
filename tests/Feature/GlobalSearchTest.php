<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
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

test('spotlight product results show the available stock', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();
    $warehouse = Warehouse::create(['name' => 'Dépôt Spotlight', 'code' => 'WH-SPOT']);
    $product = Product::create(['name' => 'Zzyzx agrafeuse', 'sku' => 'ZZ-AGR', 'is_active' => true, 'selling_price' => 1500]);
    WarehouseStock::create(['warehouse_id' => $warehouse->id, 'product_id' => $product->id, 'physical_quantity' => 37, 'reserved_quantity' => 0]);

    $this->actingAs($user)->getJson('/api/search?q=Zzyzx')
        ->assertOk()
        ->assertJsonPath('results.Produits.0.subtitle', 'SKU: ZZ-AGR — Stock: 37 — 1 500 FCFA');
});
