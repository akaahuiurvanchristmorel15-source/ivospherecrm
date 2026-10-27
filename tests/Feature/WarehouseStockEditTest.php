<?php

use App\Models\Product;
use App\Models\Role;
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

    $this->admin = User::where('email', 'admin@ivosphere.com')->firstOrFail();
    $this->warehouse = Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT', 'is_active' => true]);

    $this->ream = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'purchase_price' => 2500, 'selling_price' => 3500, 'is_active' => true]);
    $this->pen = Product::create(['name' => 'Stylo bleu', 'sku' => 'STY-01', 'purchase_price' => 100, 'selling_price' => 150, 'is_active' => true]);
    $this->elsewhere = Product::create(['name' => 'Ballon', 'sku' => 'BAL-01', 'purchase_price' => 5000, 'selling_price' => 8000, 'is_active' => true]);

    foreach ([$this->ream, $this->pen] as $product) {
        WarehouseStock::create(['warehouse_id' => $this->warehouse->id, 'product_id' => $product->id, 'physical_quantity' => 20, 'reserved_quantity' => 0]);
    }
});

function updateWarehouseStock(array $rows)
{
    return test()->post(route('stock.warehouses.stock', test()->warehouse), ['quantities_json' => json_encode($rows), 'reason' => 'Inventaire']);
}

function warehouseQuantityOf(Product $product): int
{
    return (int) WarehouseStock::where('warehouse_id', test()->warehouse->id)->where('product_id', $product->id)->value('physical_quantity');
}

test('several stock quantities are set from the warehouse page', function () {
    $this->actingAs($this->admin);

    updateWarehouseStock([
        ['id' => $this->ream->id, 'quantity' => 35],
        ['id' => $this->pen->id, 'quantity' => 0],
    ])->assertRedirect(route('stock.warehouses.show', $this->warehouse))
        ->assertSessionHas('success', 'Stock mis à jour pour 2 produit(s).');

    expect(warehouseQuantityOf($this->ream))->toBe(35)
        ->and(warehouseQuantityOf($this->pen))->toBe(0)
        ->and(StockMovement::where('type', 'ajustement')->where('reason_motif', 'Inventaire')->count())->toBe(2);
});

test('unchanged quantities and products outside the warehouse are skipped', function () {
    $this->actingAs($this->admin);

    updateWarehouseStock([
        ['id' => $this->ream->id, 'quantity' => 20],
        ['id' => $this->elsewhere->id, 'quantity' => 50],
    ])->assertSessionHas('success', 'Aucune quantité modifiée.');

    expect(StockMovement::count())->toBe(0)
        ->and(WarehouseStock::where('product_id', $this->elsewhere->id)->exists())->toBeFalse();
});

test('a stock quantity must be a positive integer', function () {
    $this->actingAs($this->admin);

    updateWarehouseStock([['id' => $this->ream->id, 'quantity' => -3]])->assertSessionHasErrors('quantities.0.quantity');
    updateWarehouseStock([['id' => $this->ream->id, 'quantity' => 2.5]])->assertSessionHasErrors('quantities.0.quantity');

    expect(warehouseQuantityOf($this->ream))->toBe(20);
});

test('editing stock requires the stock adjust permission', function () {
    $seller = User::factory()->create(['is_active' => true]);
    $seller->roles()->attach(Role::where('slug', 'agent_caissier_vendeur')->firstOrFail());
    $this->actingAs($seller);

    updateWarehouseStock([['id' => $this->ream->id, 'quantity' => 1]])->assertForbidden();
    $this->get(route('stock.warehouses.show', $this->warehouse))->assertOk()->assertDontSee('Modifier le stock');

    expect(warehouseQuantityOf($this->ream))->toBe(20);

    $this->actingAs($this->admin)->get(route('stock.warehouses.show', $this->warehouse))->assertSee('Modifier le stock');
});
