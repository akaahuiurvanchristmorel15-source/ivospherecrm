<?php

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

    $this->actingAs(User::where('email', 'admin@ivosphere.com')->first());

    $this->plateau = Warehouse::create(['name' => "Dépôt l'Plateau", 'code' => 'WH-PLT', 'is_active' => true]);
    $this->cocody = Warehouse::create(['name' => 'Dépôt Cocody', 'code' => 'WH-COC', 'is_active' => true]);

    $this->ream = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'is_active' => true]);
    WarehouseStock::create(['warehouse_id' => $this->plateau->id, 'product_id' => $this->ream->id, 'physical_quantity' => 40, 'reserved_quantity' => 0]);
});

test('the warehouse list feeds the delete modal with every warehouse and its physical stock', function () {
    $this->get(route('stock.warehouses.index'))
        ->assertOk()
        ->assertSee('totalStock: 40', false)
        ->assertSee('"code":"WH-COC"', false)
        ->assertDontSee('"current_page"', false);
});

test('stock is transferred to the chosen warehouse before deletion', function () {
    $this->delete(route('stock.warehouses.destroy', $this->plateau), [
        'stock_resolution' => 'transfer',
        'transfer_to_warehouse_id' => $this->cocody->id,
        'force_delete' => '0',
    ])->assertRedirect(route('stock.warehouses.index'));

    expect(Warehouse::find($this->plateau->id))->toBeNull()
        ->and(WarehouseStock::where('warehouse_id', $this->cocody->id)->where('product_id', $this->ream->id)->value('physical_quantity'))->toBe(40);
});

test('forced deletion removes the warehouse and its stock without transferring it', function () {
    $this->delete(route('stock.warehouses.destroy', $this->plateau), [
        'stock_resolution' => 'force',
        'transfer_to_warehouse_id' => $this->cocody->id,
        'force_delete' => '1',
    ])->assertRedirect(route('stock.warehouses.index'));

    expect(Warehouse::find($this->plateau->id))->toBeNull()
        ->and(WarehouseStock::where('warehouse_id', $this->cocody->id)->exists())->toBeFalse();
});

test('a warehouse holding stock is kept when no resolution is confirmed', function () {
    $this->from(route('stock.warehouses.index'))
        ->delete(route('stock.warehouses.destroy', $this->plateau))
        ->assertRedirect(route('stock.warehouses.index'))
        ->assertSessionHas('error');

    expect(Warehouse::find($this->plateau->id))->not->toBeNull();
});

test('the last warehouse cannot be deleted', function () {
    $this->cocody->delete();

    $this->from(route('stock.warehouses.index'))
        ->delete(route('stock.warehouses.destroy', $this->plateau), ['stock_resolution' => 'force', 'force_delete' => '1'])
        ->assertSessionHas('error');

    expect(Warehouse::find($this->plateau->id))->not->toBeNull();
});
