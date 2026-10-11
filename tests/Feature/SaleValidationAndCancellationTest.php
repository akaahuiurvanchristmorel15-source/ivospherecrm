<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->admin = User::where('email', 'admin@ivosphere.com')->first();
    $this->actingAs($this->admin);

    $this->customer = Customer::first();
    $this->product = Product::first();
});

test('authorized manager can validate a pending sale and deduct stock', function () {
    $initialStock = (int) $this->product->current_stock;
    expect($initialStock)->toBeGreaterThanOrEqual(5);

    $order = Order::create([
        'reference' => 'CMD-TEST-VAL-01',
        'customer_id' => $this->customer->id,
        'user_id' => $this->admin->id,
        'date' => now(),
        'status' => 'en_attente',
        'subtotal' => 20000,
        'tax_amount' => 0,
        'discount' => 0,
        'total' => 20000,
    ]);

    $order->items()->create([
        'product_id' => $this->product->id,
        'description' => $this->product->name,
        'quantity' => 2,
        'unit_price' => 10000,
        'total' => 20000,
    ]);

    $response = $this->post(route('commercial.orders.validate', $order));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $order->refresh();
    expect($order->status)->toBe('livrée')
        ->and($order->isValidated())->toBeTrue()
        ->and($order->validated_at)->not->toBeNull()
        ->and($order->validated_by)->toBe($this->admin->id);

    $this->product->refresh();
    expect((int) $this->product->current_stock)->toBe($initialStock - 2);

    $movement = StockMovement::where('order_id', $order->id)
        ->where('type', 'sortie')
        ->first();
    expect($movement)->not->toBeNull()
        ->and((int) $movement->quantity)->toBe(2);
});

test('a validated sale cannot be validated again and cannot be edited or deleted', function () {
    $order = Order::create([
        'reference' => 'CMD-TEST-VAL-02',
        'customer_id' => $this->customer->id,
        'user_id' => $this->admin->id,
        'date' => now(),
        'status' => 'en_attente',
        'subtotal' => 10000,
        'tax_amount' => 0,
        'discount' => 0,
        'total' => 10000,
    ]);

    $order->items()->create([
        'product_id' => $this->product->id,
        'description' => $this->product->name,
        'quantity' => 1,
        'unit_price' => 10000,
        'total' => 10000,
    ]);

    $this->post(route('commercial.orders.validate', $order))->assertRedirect();
    $order->refresh();

    // Trying to validate again should fail gracefully
    $revalidateResponse = $this->post(route('commercial.orders.validate', $order));
    $revalidateResponse->assertSessionHas('error');

    // Trying to edit should redirect with error
    $editResponse = $this->get(route('commercial.orders.edit', $order));
    $editResponse->assertRedirect(route('commercial.orders.show', $order));
    $editResponse->assertSessionHas('error');

    // Trying to delete should redirect with error
    $deleteResponse = $this->delete(route('commercial.orders.destroy', $order));
    $deleteResponse->assertSessionHas('error');
    $this->assertDatabaseHas('orders', ['id' => $order->id]);
});

test('a validated sale can be cancelled within 3 days by authorized manager and restores stock', function () {
    $stockBefore = (int) $this->product->current_stock;

    $order = Order::create([
        'reference' => 'CMD-TEST-VAL-CANCEL',
        'customer_id' => $this->customer->id,
        'user_id' => $this->admin->id,
        'date' => now()->subDays(1), // 1 day ago: within 3-day window
        'status' => 'en_attente',
        'subtotal' => 10000,
        'tax_amount' => 0,
        'discount' => 0,
        'total' => 10000,
    ]);

    $order->items()->create([
        'product_id' => $this->product->id,
        'description' => $this->product->name,
        'quantity' => 1,
        'unit_price' => 10000,
        'total' => 10000,
    ]);

    // 1. Validate the sale (stock is deducted)
    $this->post(route('commercial.orders.validate', $order))->assertRedirect();
    $order->refresh();
    $this->product->refresh();
    expect($order->status)->toBe('livrée')
        ->and((int) $this->product->current_stock)->toBe($stockBefore - 1);

    // 2. Manager cancels the validated sale within 3 days
    $response = $this->post(route('commercial.orders.cancel', $order), [
        'cancellation_reason' => 'Vente validée annulée suite à rétractation client acceptée.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $order->refresh();
    $this->product->refresh();
    expect($order->status)->toBe('annulée')
        ->and($order->isCancelled())->toBeTrue()
        ->and($order->cancelled_at)->not->toBeNull()
        ->and($order->cancelled_by)->toBe($this->admin->id)
        ->and($order->cancellation_reason)->toContain('rétractation client acceptée')
        ->and((int) $this->product->current_stock)->toBe($stockBefore);
});

test('a validated sale CANNOT be cancelled after the 3-day window has expired', function () {
    $order = Order::create([
        'reference' => 'CMD-TEST-VAL-EXPIRED',
        'customer_id' => $this->customer->id,
        'user_id' => $this->admin->id,
        'date' => now()->subDays(4), // 4 days ago: past 3-day window
        'status' => 'livrée',
        'subtotal' => 10000,
        'tax_amount' => 0,
        'discount' => 0,
        'total' => 10000,
    ]);

    $response = $this->post(route('commercial.orders.cancel', $order), [
        'cancellation_reason' => 'Tentative annulation tardive sur vente validée',
    ]);

    $response->assertSessionHas('error');
    $order->refresh();
    expect($order->status)->toBe('livrée');
});

test('can cancel a pending sale within 3 days with mandatory reason, restoring stock and cancelling invoices', function () {
    $warehouse = Warehouse::first();
    $stockBefore = (int) $this->product->current_stock;

    $order = Order::create([
        'reference' => 'CMD-TEST-CANCEL-01',
        'customer_id' => $this->customer->id,
        'user_id' => $this->admin->id,
        'date' => now()->subDays(2), // 2 days ago: within 3-day window
        'status' => 'confirmée',
        'subtotal' => 15000,
        'tax_amount' => 0,
        'discount' => 0,
        'total' => 15000,
    ]);

    $order->items()->create([
        'product_id' => $this->product->id,
        'description' => $this->product->name,
        'quantity' => 1,
        'unit_price' => 15000,
        'total' => 15000,
    ]);

    // Simulate an existing invoice and payment
    $invoice = Invoice::create([
        'reference' => 'FAC-TEST-CANCEL-01',
        'order_id' => $order->id,
        'customer_id' => $this->customer->id,
        'user_id' => $this->admin->id,
        'date' => now()->subDays(2),
        'status' => 'payee',
        'subtotal' => 15000,
        'tax_amount' => 0,
        'discount' => 0,
        'total' => 15000,
        'paid_amount' => 15000,
    ]);

    $payment = Payment::create([
        'reference' => 'PAY-TEST-CANCEL-01',
        'invoice_id' => $invoice->id,
        'customer_id' => $this->customer->id,
        'user_id' => $this->admin->id,
        'date' => now()->subDays(2),
        'amount' => 15000,
        'method' => 'especes',
        'status' => 'valide',
    ]);

    // Also simulate an existing stock deduction to test stock restoration
    StockMovement::create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $this->product->id,
        'user_id' => $this->admin->id,
        'type' => 'sortie',
        'reason_motif' => 'vente',
        'quantity' => 1,
        'stock_before' => $stockBefore,
        'stock_after' => $stockBefore - 1,
        'order_id' => $order->id,
        'date' => now()->subDays(2),
    ]);
    WarehouseStock::where('warehouse_id', $warehouse->id)
        ->where('product_id', $this->product->id)
        ->decrement('physical_quantity', 1);

    // Cancel the sale
    $response = $this->post(route('commercial.orders.cancel', $order), [
        'cancellation_reason' => 'Client a exercé son droit de rétractation dans les délais.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $order->refresh();
    expect($order->status)->toBe('annulée')
        ->and($order->isCancelled())->toBeTrue()
        ->and($order->cancelled_at)->not->toBeNull()
        ->and($order->cancelled_by)->toBe($this->admin->id)
        ->and($order->cancellation_reason)->toContain('droit de rétractation');

    // Invoice and payment cancelled
    $invoice->refresh();
    $payment->refresh();
    expect($invoice->status)->toBe('annulee')
        ->and($payment->status)->toBe('annule');

    // Stock movement for restoration
    $restoreMovement = StockMovement::where('order_id', $order->id)
        ->where('type', 'retour')
        ->where('reason_motif', 'annulation_vente')
        ->first();
    expect($restoreMovement)->not->toBeNull()
        ->and((int) $restoreMovement->quantity)->toBe(1);

    $this->product->refresh();
    expect((int) $this->product->current_stock)->toBe($stockBefore);
});

test('cannot cancel a sale after the 3-day deadline has passed', function () {
    $order = Order::create([
        'reference' => 'CMD-TEST-EXPIRED',
        'customer_id' => $this->customer->id,
        'user_id' => $this->admin->id,
        'date' => now()->subDays(4), // 4 days ago: past the 3-day limit
        'status' => 'en_attente',
        'subtotal' => 10000,
        'tax_amount' => 0,
        'discount' => 0,
        'total' => 10000,
    ]);

    $response = $this->post(route('commercial.orders.cancel', $order), [
        'cancellation_reason' => 'Tentative d\'annulation tardive',
    ]);

    $response->assertSessionHas('error');
    $order->refresh();
    expect($order->status)->toBe('en_attente');
});

test('unauthorized users without manager role cannot validate or cancel sales', function () {
    $cashierRole = Role::where('slug', 'agent_caissier_vendeur')->first();
    $cashierUser = User::create([
        'name' => 'Caissier Test',
        'email' => 'caissier.test@ivosphere.ci',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);
    if ($cashierRole) {
        $cashierUser->roles()->attach($cashierRole->id);
    }

    $order = Order::create([
        'reference' => 'CMD-TEST-FORBIDDEN',
        'customer_id' => $this->customer->id,
        'user_id' => $this->admin->id,
        'date' => now(),
        'status' => 'en_attente',
        'subtotal' => 10000,
        'tax_amount' => 0,
        'discount' => 0,
        'total' => 10000,
    ]);

    $this->actingAs($cashierUser);

    // Validate forbidden (403)
    $valResponse = $this->post(route('commercial.orders.validate', $order));
    $valResponse->assertForbidden();

    // Cancel forbidden (403)
    $cancelResponse = $this->post(route('commercial.orders.cancel', $order), [
        'cancellation_reason' => 'Non autorisé',
    ]);
    $cancelResponse->assertForbidden();
});
