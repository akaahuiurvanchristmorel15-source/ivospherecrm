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
    $other = Warehouse::create(['name' => 'Dépôt Cocody', 'code' => 'WH-COC', 'is_active' => true]);

    $this->ream = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'selling_price' => 3500, 'is_active' => true]);
    $this->pen = Product::create(['name' => 'Stylo bleu', 'sku' => 'STY-01', 'selling_price' => 150, 'is_active' => true]);
    $this->kept = Product::create(['name' => 'Agrafeuse', 'sku' => 'AGR-01', 'selling_price' => 2000, 'is_active' => true]);

    foreach ([$this->ream, $this->pen, $this->kept] as $product) {
        WarehouseStock::create(['warehouse_id' => $this->warehouse->id, 'product_id' => $product->id, 'physical_quantity' => 10, 'reserved_quantity' => 0]);
    }
    WarehouseStock::create(['warehouse_id' => $other->id, 'product_id' => $this->ream->id, 'physical_quantity' => 4, 'reserved_quantity' => 0]);
});

test('several products are permanently deleted from the warehouse page', function () {
    $this->actingAs($this->admin)
        ->post(route('stock.products.bulk-destroy'), [
            'product_ids' => [$this->ream->id, $this->pen->id],
            'warehouse_id' => $this->warehouse->id,
        ])
        ->assertRedirect(route('stock.warehouses.show', $this->warehouse))
        ->assertSessionHas('success', '2 article(s) supprimé(s) des stocks avec succès.');

    expect(Product::whereKey([$this->ream->id, $this->pen->id])->exists())->toBeFalse()
        ->and(WarehouseStock::whereIn('product_id', [$this->ream->id, $this->pen->id])->exists())->toBeFalse()
        ->and(Product::whereKey($this->kept->id)->exists())->toBeTrue();

    $this->get(route('stock.warehouses.show', $this->warehouse))
        ->assertViewHas('stockLevels', fn ($levels) => $levels->pluck('product_id')->all() === [$this->kept->id]);
});

test('the bulk deletion still returns to the stock page without a warehouse', function () {
    $this->actingAs($this->admin)
        ->post(route('stock.products.bulk-destroy'), ['product_ids' => [$this->pen->id]])
        ->assertRedirect(route('stock.index', ['tab' => 'disponibilite']));
});

test('deleting products from a warehouse requires the delete permission', function () {
    $seller = User::factory()->create(['is_active' => true]);
    $seller->roles()->attach(Role::where('slug', 'agent_caissier_vendeur')->firstOrFail());

    $this->actingAs($seller)->get(route('stock.warehouses.show', $this->warehouse))
        ->assertOk()
        ->assertDontSee('Supprimer des articles');

    $this->post(route('stock.products.bulk-destroy'), ['product_ids' => [$this->pen->id], 'warehouse_id' => $this->warehouse->id])
        ->assertForbidden();

    expect(Product::whereKey($this->pen->id)->exists())->toBeTrue();

    $this->actingAs($this->admin)->get(route('stock.warehouses.show', $this->warehouse))->assertSee('Supprimer des articles');
});
