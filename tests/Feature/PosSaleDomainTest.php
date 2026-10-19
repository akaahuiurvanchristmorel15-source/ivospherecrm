<?php

use App\Http\Controllers\Commercial\PosController;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
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

    $this->print = Domain::where('code', 'PRINT')->firstOrFail();
    $this->assurance = Domain::where('code', 'ASSURANCE')->firstOrFail();
    $warehouse = Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT', 'is_active' => true]);

    $this->ream = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'domain_id' => $this->print->id, 'selling_price' => 3500, 'is_active' => true]);
    $this->policy = Product::create(['name' => 'Carnet assurance', 'sku' => 'ASS-01', 'domain_id' => $this->assurance->id, 'selling_price' => 500, 'is_active' => true]);
    foreach ([$this->ream, $this->policy] as $product) {
        WarehouseStock::create(['warehouse_id' => $warehouse->id, 'product_id' => $product->id, 'physical_quantity' => 50, 'reserved_quantity' => 0]);
    }

    // Client de passage créé autrefois avec le domaine Assurance : il ne doit plus décider du domaine de la vente
    Customer::create(['code' => 'CLI-PASSAGER', 'name' => 'Client Comptoir / Passager', 'type' => 'particulier', 'status' => 'actif', 'domain_id' => $this->assurance->id]);
});

test('a pos sale of print products counts in the print domain', function () {
    $this->post(route('commercial.pos.checkout'), [
        'payment_method' => 'especes',
        'amount_paid' => 7500,
        'items' => [
            ['product_id' => $this->ream->id, 'quantity' => 2, 'unit_price' => 3500],
            ['product_id' => $this->policy->id, 'quantity' => 1, 'unit_price' => 500],
        ],
    ])->assertRedirect();

    $order = Order::latest('id')->firstOrFail();
    $invoice = Invoice::where('order_id', $order->id)->firstOrFail();

    expect($order->domain_id)->toBe($this->print->id)
        ->and($invoice->domain_id)->toBe($this->print->id)
        ->and(Payment::where('invoice_id', $invoice->id)->value('domain_id'))->toBe($this->print->id);
});

test('the dominant domain is the one weighing most in the ticket', function () {
    expect(PosController::dominantProductDomain([
        ['product_id' => $this->ream->id, 'quantity' => 1, 'unit_price' => 3500],
        ['product_id' => $this->policy->id, 'quantity' => 10, 'unit_price' => 500],
    ]))->toBe($this->assurance->id)
        ->and(PosController::dominantProductDomain([
            ['product_id' => $this->ream->id, 'quantity' => 1, 'unit_price' => 3500],
        ]))->toBe($this->print->id);
});
