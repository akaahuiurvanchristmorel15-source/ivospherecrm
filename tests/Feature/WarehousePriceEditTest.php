<?php

use App\Models\Product;
use App\Models\Role;
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

function updateWarehousePrices(array $rows)
{
    return test()->post(route('stock.warehouses.prices', test()->warehouse), ['prices_json' => json_encode($rows)]);
}

test('several product prices are updated from the warehouse page', function () {
    $this->actingAs($this->admin);

    updateWarehousePrices([
        ['id' => $this->ream->id, 'purchase_price' => 2600, 'selling_price' => 3800],
        ['id' => $this->pen->id, 'purchase_price' => 100, 'selling_price' => 200],
    ])->assertRedirect(route('stock.warehouses.show', $this->warehouse))
        ->assertSessionHas('success', 'Prix mis à jour pour 2 produit(s).');

    expect((float) $this->ream->fresh()->purchase_price)->toBe(2600.0)
        ->and((float) $this->ream->fresh()->selling_price)->toBe(3800.0)
        ->and((float) $this->pen->fresh()->selling_price)->toBe(200.0);
});

test('products outside the warehouse are not touched', function () {
    $this->actingAs($this->admin);

    updateWarehousePrices([['id' => $this->elsewhere->id, 'purchase_price' => 1, 'selling_price' => 1]])
        ->assertSessionHas('success', 'Aucun prix modifié.');

    expect((float) $this->elsewhere->fresh()->selling_price)->toBe(8000.0);
});

test('a selling price is required and cannot be negative', function () {
    $this->actingAs($this->admin);

    updateWarehousePrices([['id' => $this->ream->id, 'purchase_price' => 2500, 'selling_price' => -5]])
        ->assertSessionHasErrors('prices.0.selling_price');
});

test('editing prices requires the catalogue edit permission', function () {
    $seller = User::factory()->create(['is_active' => true]);
    $seller->roles()->attach(Role::where('slug', 'agent_caissier_vendeur')->firstOrFail());
    $this->actingAs($seller);

    updateWarehousePrices([['id' => $this->ream->id, 'purchase_price' => 1, 'selling_price' => 1]])->assertForbidden();

    $this->get(route('stock.warehouses.show', $this->warehouse))->assertOk()->assertDontSee('Modifier les prix');

    $this->actingAs($this->admin)->get(route('stock.warehouses.show', $this->warehouse))
        ->assertOk()
        ->assertSee('Modifier les prix')
        ->assertSee('Rame papier A4');
});
