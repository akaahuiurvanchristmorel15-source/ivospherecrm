<?php

use App\Models\Product;
use App\Models\StockAlert;
use App\Models\StockMovement;
use App\Models\StockTransfer;
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

    $this->ream = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'selling_price' => 3500, 'min_stock' => 2, 'is_active' => true]);
    $this->pen = Product::create(['name' => 'Stylo bleu', 'sku' => 'STY-01', 'selling_price' => 150, 'min_stock' => 2, 'is_active' => true]);

    WarehouseStock::create(['warehouse_id' => $this->source->id, 'product_id' => $this->ream->id, 'physical_quantity' => 20, 'reserved_quantity' => 0]);
    WarehouseStock::create(['warehouse_id' => $this->source->id, 'product_id' => $this->pen->id, 'physical_quantity' => 50, 'reserved_quantity' => 0]);
    WarehouseStock::create(['warehouse_id' => $this->destination->id, 'product_id' => $this->pen->id, 'physical_quantity' => 5, 'reserved_quantity' => 0]);
});

function transferWarehouseProducts(array $items, ?int $destinationId = null)
{
    return test()->post(route('stock.warehouses.transfer', test()->source), [
        'destination_warehouse_id' => $destinationId ?? test()->destination->id,
        'transfers_json' => json_encode($items),
    ]);
}

function stockIn(Warehouse $warehouse, Product $product): ?int
{
    $quantity = WarehouseStock::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->value('physical_quantity');

    return $quantity === null ? null : (int) $quantity;
}

test('several products are transferred to another warehouse at once', function () {
    transferWarehouseProducts([
        ['product_id' => $this->ream->id, 'quantity' => 20],
        ['product_id' => $this->pen->id, 'quantity' => 30],
    ])->assertRedirect(route('stock.warehouses.show', $this->source))
        ->assertSessionHas('success', '2 article(s) transféré(s) vers Dépôt Cocody (50 unité(s)).');

    expect(stockIn($this->destination, $this->ream))->toBe(20)
        ->and(stockIn($this->destination, $this->pen))->toBe(35)
        ->and(stockIn($this->source, $this->pen))->toBe(20)
        ->and(StockTransfer::where('status', 'receptionne')->count())->toBe(2);
});

test('a fully transferred product no longer exists in the source warehouse', function () {
    StockAlert::create(['product_id' => $this->ream->id, 'warehouse_id' => $this->source->id, 'status' => 'active', 'min_quantity' => 2, 'current_quantity' => 1]);

    transferWarehouseProducts([['product_id' => $this->ream->id, 'quantity' => 20]])->assertSessionHasNoErrors();

    expect(stockIn($this->source, $this->ream))->toBeNull()
        ->and(StockAlert::where('product_id', $this->ream->id)->where('warehouse_id', $this->source->id)->where('status', 'active')->exists())->toBeFalse();

    $this->get(route('stock.warehouses.show', $this->source))
        ->assertOk()
        ->assertViewHas('stockLevels', fn ($levels) => $levels->pluck('product_id')->all() === [$this->pen->id]);

    $this->get(route('stock.warehouses.show', $this->destination))
        ->assertViewHas('stockLevels', fn ($levels) => $levels->pluck('product_id')->contains($this->ream->id));
});

test('a transfer cannot exceed the source stock and moves nothing', function () {
    transferWarehouseProducts([
        ['product_id' => $this->ream->id, 'quantity' => 5],
        ['product_id' => $this->pen->id, 'quantity' => 51],
    ])->assertSessionHasErrors('transfers');

    expect(stockIn($this->source, $this->ream))->toBe(20)
        ->and(stockIn($this->source, $this->pen))->toBe(50)
        ->and(StockMovement::count())->toBe(0);
});

test('the destination must be another existing warehouse', function () {
    transferWarehouseProducts([['product_id' => $this->ream->id, 'quantity' => 1]], $this->source->id)
        ->assertSessionHasErrors('destination_warehouse_id');

    transferWarehouseProducts([['product_id' => $this->ream->id, 'quantity' => 1]], 999999)
        ->assertSessionHasErrors('destination_warehouse_id');

    transferWarehouseProducts([['product_id' => $this->ream->id, 'quantity' => 0]])
        ->assertSessionHasErrors('transfers.0.quantity');

    expect(stockIn($this->source, $this->ream))->toBe(20);
});
