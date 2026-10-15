<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StockSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->actingAs(User::where('email', 'admin@ivosphere.com')->first());
});

test('new warehouses get sequential label numbers and distinct default colors', function () {
    $first = Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT']);
    $second = Warehouse::create(['name' => 'Dépôt Cocody', 'code' => 'WH-COC']);

    expect($first->label_number)->toBe(1)
        ->and($second->label_number)->toBe(2)
        ->and($first->label_color)->toBe(Warehouse::LABEL_COLORS[0])
        ->and($second->label_color)->toBe(Warehouse::LABEL_COLORS[1]);
});

test('a freed label number is reused for the next warehouse', function () {
    Warehouse::create(['name' => 'A', 'code' => 'WH-A']);
    $second = Warehouse::create(['name' => 'B', 'code' => 'WH-B']);
    Warehouse::create(['name' => 'C', 'code' => 'WH-C']);

    $second->delete();

    expect(Warehouse::create(['name' => 'D', 'code' => 'WH-D'])->label_number)->toBe(2);
});

test('label number and color can be set when updating a warehouse', function () {
    $warehouse = Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT']);

    $this->put(route('stock.warehouses.update', $warehouse), [
        'name' => 'Dépôt Plateau',
        'code' => 'WH-PLT',
        'label_number' => 7,
        'label_color' => '#FACC15',
    ])->assertRedirect(route('stock.warehouses.index'));

    $warehouse->refresh();

    expect($warehouse->label_number)->toBe(7)
        ->and($warehouse->label_color)->toBe('#FACC15')
        ->and($warehouse->label_text_color)->toBe('#0B0F14');
});

test('label number must be unique and color must be a hex value', function () {
    Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT']);

    $this->post(route('stock.warehouses.store'), [
        'name' => 'Dépôt Cocody',
        'code' => 'WH-COC',
        'label_number' => 1,
        'label_color' => 'red',
    ])->assertSessionHasErrors(['label_number', 'label_color']);
});

test('pos exposes warehouse labels of each product', function () {
    $warehouse = Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT', 'label_color' => '#DC2626']);
    $product = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'is_active' => true, 'selling_price' => 3000]);
    WarehouseStock::create(['warehouse_id' => $warehouse->id, 'product_id' => $product->id, 'physical_quantity' => 10, 'reserved_quantity' => 0]);

    $this->get(route('commercial.pos.index'))
        ->assertOk()
        ->assertViewHas('products', function ($products) {
            $warehouseData = $products->first()->toArray()['warehouse_stocks'][0]['warehouse'];

            return $warehouseData['label_number'] === 1
                && $warehouseData['label_color'] === '#DC2626'
                && $warehouseData['label_text_color'] === '#FFFFFF';
        });
});

test('seeded warehouses receive a label even though model events are disabled', function () {
    Warehouse::withoutEvents(fn () => $this->seed(StockSeeder::class));

    expect(Warehouse::whereNull('label_number')->count())->toBe(0)
        ->and(Warehouse::orderBy('label_number')->pluck('label_number')->all())->toBe(range(1, Warehouse::count()));
});
