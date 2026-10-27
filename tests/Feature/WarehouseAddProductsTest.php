<?php

use App\Models\Product;
use App\Models\StockMovement;
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

    $this->warehouse = Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT', 'is_active' => true]);

    $this->ream = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'selling_price' => 3500, 'is_active' => true]);
    $this->pen = Product::create(['name' => 'Stylo bleu', 'sku' => 'STY-01', 'selling_price' => 150, 'is_active' => true]);

    WarehouseStock::create(['warehouse_id' => $this->warehouse->id, 'product_id' => $this->ream->id, 'physical_quantity' => 5, 'reserved_quantity' => 0]);
});

function addWarehouseProducts(array $items)
{
    return test()->post(route('stock.warehouses.products.add', test()->warehouse), ['items_json' => json_encode($items)]);
}

test('the warehouse page offers to add catalog products', function () {
    $this->get(route('stock.warehouses.show', $this->warehouse))
        ->assertOk()
        ->assertSee('Ajouter des produits')
        ->assertViewHas('catalogProducts', fn ($products) => $products->pluck('id')->contains($this->pen->id));
});

test('several products are added to the warehouse at once', function () {
    addWarehouseProducts([
        ['product_id' => $this->ream->id, 'quantity' => 10],
        ['product_id' => $this->pen->id, 'quantity' => 30],
    ])->assertRedirect(route('stock.warehouses.show', $this->warehouse))
        ->assertSessionHas('success', "2 produit(s) ajouté(s) à l'entrepôt (40 unité(s)).");

    $stocks = WarehouseStock::where('warehouse_id', $this->warehouse->id)->pluck('physical_quantity', 'product_id');

    expect((int) $stocks[$this->ream->id])->toBe(15)
        ->and((int) $stocks[$this->pen->id])->toBe(30)
        ->and(StockMovement::where('warehouse_id', $this->warehouse->id)->where('type', 'entree')->count())->toBe(2);
});

test('quantities must be positive and products listed once', function () {
    addWarehouseProducts([['product_id' => $this->pen->id, 'quantity' => 0]])
        ->assertSessionHasErrors('items.0.quantity');

    addWarehouseProducts([
        ['product_id' => $this->pen->id, 'quantity' => 1],
        ['product_id' => $this->pen->id, 'quantity' => 2],
    ])->assertSessionHasErrors('items.0.product_id');

    addWarehouseProducts([])->assertSessionHasErrors('items');

    expect(WarehouseStock::where('product_id', $this->pen->id)->exists())->toBeFalse();
});

test('an unknown product is rejected without touching the stock', function () {
    addWarehouseProducts([
        ['product_id' => $this->ream->id, 'quantity' => 10],
        ['product_id' => 999999, 'quantity' => 1],
    ])->assertSessionHasErrors('items');

    expect((int) WarehouseStock::where('product_id', $this->ream->id)->value('physical_quantity'))->toBe(5);
});
