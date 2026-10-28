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

    $this->actingAs(User::where('email', 'admin@ivosphere.com')->firstOrFail());

    $this->source = Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT', 'is_active' => true]);
    $this->destination = Warehouse::create(['name' => 'Dépôt Cocody', 'code' => 'WH-COC', 'is_active' => true]);

    $this->ream = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'selling_price' => 3500, 'is_active' => true]);
    $this->pen = Product::create(['name' => 'Stylo bleu', 'sku' => 'STY-01', 'selling_price' => 150, 'is_active' => true]);

    WarehouseStock::create(['warehouse_id' => $this->source->id, 'product_id' => $this->ream->id, 'physical_quantity' => 20, 'reserved_quantity' => 0]);
    WarehouseStock::create(['warehouse_id' => $this->source->id, 'product_id' => $this->pen->id, 'physical_quantity' => 50, 'reserved_quantity' => 0]);
});

/**
 * @return array<int, array{products: int, items: int}>
 */
function warehouseIndicators(): array
{
    return test()->get(route('stock.warehouses.index'))
        ->assertOk()
        ->viewData('warehouses')
        ->getCollection()
        ->mapWithKeys(fn (Warehouse $warehouse) => [$warehouse->id => ['products' => $warehouse->product_count, 'items' => $warehouse->total_items]])
        ->all();
}

test('the warehouse list counts the real stock', function () {
    expect(warehouseIndicators())->toBe([
        $this->source->id => ['products' => 2, 'items' => 70],
        $this->destination->id => ['products' => 0, 'items' => 0],
    ]);
});

test('the warehouse list follows a transfer at once', function () {
    $this->post(route('stock.warehouses.transfer', $this->source), [
        'destination_warehouse_id' => $this->destination->id,
        'transfers_json' => json_encode([
            ['product_id' => $this->ream->id, 'quantity' => 20],
            ['product_id' => $this->pen->id, 'quantity' => 10],
        ]),
    ])->assertSessionHasNoErrors();

    expect(warehouseIndicators())->toBe([
        $this->source->id => ['products' => 1, 'items' => 40],
        $this->destination->id => ['products' => 2, 'items' => 30],
    ]);
});

test('the warehouse list follows a stock adjustment and a deletion', function () {
    $this->post(route('stock.warehouses.stock', $this->source), [
        'quantities_json' => json_encode([['id' => $this->pen->id, 'quantity' => 5]]),
    ])->assertSessionHasNoErrors();

    $this->post(route('stock.products.bulk-destroy'), ['product_ids' => [$this->ream->id], 'warehouse_id' => $this->source->id]);

    expect(warehouseIndicators()[$this->source->id])->toBe(['products' => 1, 'items' => 5]);
});
