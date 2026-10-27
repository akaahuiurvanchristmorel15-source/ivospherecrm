<?php

use App\Models\Domain;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->actingAs(User::where('email', 'admin@ivosphere.com')->firstOrFail());

    $domain = Domain::where('code', 'PRINT')->firstOrFail();
    $this->plateau = Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT', 'is_active' => true]);
    $this->cocody = Warehouse::create(['name' => 'Dépôt Cocody', 'code' => 'WH-COC', 'is_active' => true]);

    $this->ream = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'domain_id' => $domain->id, 'selling_price' => 3500, 'is_active' => true]);
    $this->toner = Product::create(['name' => 'Toner noir', 'sku' => 'TON-01', 'domain_id' => $domain->id, 'selling_price' => 25000, 'is_active' => true]);
    $this->stapler = Product::create(['name' => 'Agrafeuse', 'sku' => 'AGR-01', 'domain_id' => $domain->id, 'selling_price' => 2000, 'is_active' => true]);

    WarehouseStock::create(['warehouse_id' => $this->plateau->id, 'product_id' => $this->ream->id, 'physical_quantity' => 10, 'reserved_quantity' => 0]);
    WarehouseStock::create(['warehouse_id' => $this->cocody->id, 'product_id' => $this->ream->id, 'physical_quantity' => 10, 'reserved_quantity' => 0]);
    WarehouseStock::create(['warehouse_id' => $this->cocody->id, 'product_id' => $this->toner->id, 'physical_quantity' => 4, 'reserved_quantity' => 0]);
    WarehouseStock::create(['warehouse_id' => $this->plateau->id, 'product_id' => $this->stapler->id, 'physical_quantity' => 0, 'reserved_quantity' => 0]);
});

test('the pos lists active warehouses instead of domains', function () {
    Warehouse::create(['name' => 'Dépôt fermé', 'code' => 'WH-OFF', 'is_active' => false]);

    $this->get(route('commercial.pos.index'))
        ->assertOk()
        ->assertViewMissing('domains')
        ->assertViewHas('warehouses', fn ($warehouses) => $warehouses->pluck('id')->sort()->values()->all() === collect([$this->plateau->id, $this->cocody->id])->sort()->values()->all())
        ->assertSee('Tous les entrepôts')
        ->assertSee('Dépôt Cocody')
        ->assertDontSee('Dépôt fermé');
});

test('the pos only shows products in stock in the chosen warehouse', function () {
    $this->get(route('commercial.pos.index', ['warehouse_id' => $this->plateau->id]))
        ->assertOk()
        ->assertViewHas('selectedWarehouseId', $this->plateau->id)
        ->assertViewHas('products', fn ($products) => $products->pluck('id')->all() === [$this->ream->id]);

    $this->get(route('commercial.pos.index', ['warehouse_id' => $this->cocody->id]))
        ->assertViewHas('products', fn ($products) => $products->pluck('id')->sort()->values()->all() === collect([$this->ream->id, $this->toner->id])->sort()->values()->all());
});

test('the pos shows every product when no warehouse is chosen', function () {
    $this->get(route('commercial.pos.index'))
        ->assertViewHas('selectedWarehouseId', null)
        ->assertViewHas('products', fn ($products) => $products->count() === 3);
});

test('a pos sale takes the stock from the chosen warehouse', function () {
    $this->post(route('commercial.pos.checkout'), [
        'warehouse_id' => $this->cocody->id,
        'payment_method' => 'especes',
        'amount_paid' => 10500,
        'items' => [['product_id' => $this->ream->id, 'quantity' => 3, 'unit_price' => 3500]],
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(WarehouseStock::where('warehouse_id', $this->cocody->id)->where('product_id', $this->ream->id)->value('physical_quantity'))->toBe(7)
        ->and(WarehouseStock::where('warehouse_id', $this->plateau->id)->where('product_id', $this->ream->id)->value('physical_quantity'))->toBe(10);
});

test('a pos sale cannot exceed the stock of the chosen warehouse', function () {
    $this->post(route('commercial.pos.checkout'), [
        'warehouse_id' => $this->plateau->id,
        'payment_method' => 'especes',
        'amount_paid' => 50000,
        'items' => [['product_id' => $this->toner->id, 'quantity' => 2, 'unit_price' => 25000]],
    ])->assertRedirect()->assertSessionHas('error');

    expect(WarehouseStock::where('warehouse_id', $this->cocody->id)->where('product_id', $this->toner->id)->value('physical_quantity'))->toBe(4);
});
