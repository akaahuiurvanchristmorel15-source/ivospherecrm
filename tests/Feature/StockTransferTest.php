<?php

use App\Models\Product;
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

    $this->actingAs(User::where('email', 'admin@ivosphere.com')->first());

    $this->source = Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT', 'is_active' => true]);
    $this->destination = Warehouse::create(['name' => 'Dépôt Cocody', 'code' => 'WH-COC', 'is_active' => true]);
    $this->product = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'is_active' => true]);

    WarehouseStock::create(['warehouse_id' => $this->source->id, 'product_id' => $this->product->id, 'physical_quantity' => 1, 'reserved_quantity' => 0]);
});

test('transfer above the available stock is refused with a message and records nothing', function () {
    $this->from(route('stock.index'))
        ->post(route('stock.transfers.store'), [
            'source_warehouse_id' => $this->source->id,
            'destination_warehouse_id' => $this->destination->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'execute_immediately' => 1,
        ])
        ->assertRedirect(route('stock.index'))
        ->assertSessionHas('error', fn (string $message) => str_contains($message, 'seulement 1 unité(s)')
            && str_contains($message, 'Dépôt Plateau'));

    expect(StockTransfer::count())->toBe(0)
        ->and((int) WarehouseStock::where('warehouse_id', $this->source->id)->value('physical_quantity'))->toBe(1);
});

test('transfer within the available stock moves the quantity', function () {
    $this->post(route('stock.transfers.store'), [
        'source_warehouse_id' => $this->source->id,
        'destination_warehouse_id' => $this->destination->id,
        'product_id' => $this->product->id,
        'quantity' => 1,
        'execute_immediately' => 1,
    ])->assertSessionHas('success');

    expect(StockTransfer::where('status', 'receptionne')->count())->toBe(1)
        ->and((int) WarehouseStock::where('warehouse_id', $this->source->id)->value('physical_quantity'))->toBe(0)
        ->and((int) WarehouseStock::where('warehouse_id', $this->destination->id)->value('physical_quantity'))->toBe(1);
});
