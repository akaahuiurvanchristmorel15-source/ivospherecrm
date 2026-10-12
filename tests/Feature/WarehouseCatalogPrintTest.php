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

    $this->plateau = Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT', 'is_active' => true]);
    $this->cocody = Warehouse::create(['name' => 'Dépôt Cocody', 'code' => 'WH-COC', 'is_active' => true]);

    $this->ream = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'is_active' => true]);
    $this->ink = Product::create(['name' => 'Cartouche encre noire', 'sku' => 'ENC-NR', 'is_active' => true]);

    WarehouseStock::create(['warehouse_id' => $this->plateau->id, 'product_id' => $this->ream->id, 'physical_quantity' => 40, 'reserved_quantity' => 0]);
    WarehouseStock::create(['warehouse_id' => $this->cocody->id, 'product_id' => $this->ream->id, 'physical_quantity' => 7, 'reserved_quantity' => 0]);
    WarehouseStock::create(['warehouse_id' => $this->cocody->id, 'product_id' => $this->ink->id, 'physical_quantity' => 12, 'reserved_quantity' => 0]);
});

test('catalog for one warehouse shows its name and its own quantities', function () {
    $response = $this->get(route('stock.products.print-catalog', ['warehouse_id' => $this->plateau->id]));

    $response->assertOk()
        ->assertSee('Dépôt Plateau')
        ->assertSee('Rame papier A4')
        ->assertDontSee('Cartouche encre noire')
        ->assertViewHas('sections', fn ($sections) => $sections->count() === 1
            && $sections->first()['rows']->first()['quantity'] === 40.0);
});

test('catalog per warehouse prints one section per stocked warehouse', function () {
    $response = $this->get(route('stock.products.print-catalog', ['per_warehouse' => 1]));

    $response->assertOk()
        ->assertSee('Dépôt Plateau')
        ->assertSee('Dépôt Cocody')
        ->assertViewHas('sections', function ($sections) {
            $quantitiesByWarehouse = $sections->mapWithKeys(fn ($section) => [
                $section['warehouse']->code => $section['rows']->pluck('quantity', 'product.sku')->all(),
            ]);

            return $quantitiesByWarehouse->all() === [
                'WH-COC' => ['ENC-NR' => 12.0, 'PAP-A4' => 7.0],
                'WH-PLT' => ['PAP-A4' => 40.0],
            ];
        });
});

test('warehouse pages link to their printable catalog', function () {
    $this->get(route('stock.warehouses.index'))
        ->assertOk()
        ->assertSee(route('stock.products.print-catalog', ['per_warehouse' => 1]), false)
        ->assertSee(route('stock.products.print-catalog', ['warehouse_id' => $this->plateau->id]), false);

    $this->get(route('stock.warehouses.show', $this->plateau))
        ->assertOk()
        ->assertSee('Imprimer le catalogue');
});
