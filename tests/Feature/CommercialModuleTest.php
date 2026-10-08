<?php

use App\Models\CommercialAppointment;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Models\StockMovement;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->admin = User::where('email', 'admin@ivosphere.com')->first();
    $this->actingAs($this->admin);
});

test('can view customers list and client 360 profile', function () {
    $customer = Customer::first();
    expect($customer)->not->toBeNull();

    $response = $this->get(route('commercial.customers.index'));
    $response->assertOk();
    $response->assertSee($customer->code);

    $showResponse = $this->get(route('commercial.customers.show', $customer));
    $showResponse->assertOk();
    $showResponse->assertSee($customer->name);
    $showResponse->assertSee('Factures');
    $showResponse->assertSee('Devis');
    $showResponse->assertSee('Rendez-vous');
});

test('accumulates loyalty points when quick payment is recorded', function () {
    $customer = Customer::create([
        'code' => 'CLI-TEST-LOYALTY',
        'name' => 'Client Test Fidélité',
        'type' => 'particulier',
        'status' => 'actif',
        'user_id' => $this->admin->id,
        'loyalty_points' => 0,
        'loyalty_level' => 'BRONZE',
    ]);

    $invoice = Invoice::create([
        'reference' => 'FAC-TEST-001',
        'customer_id' => $customer->id,
        'user_id' => $this->admin->id,
        'type' => 'standard',
        'date' => now(),
        'status' => 'non_payee',
        'subtotal' => 500000,
        'tax_amount' => 0,
        'discount' => 0,
        'total' => 500000,
        'paid_amount' => 0,
    ]);

    // Record payment of 500 000 FCFA
    $response = $this->post(route('commercial.invoices.payment', $invoice), [
        'amount' => 500000,
        'method' => 'especes',
        'date' => now()->format('Y-m-d'),
        'notes' => 'Règlement total',
    ]);

    $response->assertRedirect();
    $invoice->refresh();
    $customer->refresh();

    expect($invoice->status)->toBe('payee')
        ->and($customer->loyalty_points)->toBe(500)
        ->and($customer->loyalty_level)->toBe('SILVER');
});

test('can view pipeline kanban and transition prospect stages', function () {
    $prospect = Prospect::first();
    expect($prospect)->not->toBeNull();

    $response = $this->get(route('commercial.prospects.index'));
    $response->assertOk();
    $response->assertSee('Pipeline Commercial');

    // Update stage to negociation
    $patchResponse = $this->patch(route('commercial.prospects.stage', $prospect), [
        'stage' => 'negociation',
        'probability' => 85,
        'notes' => 'Offre discutée avec la direction',
    ]);

    $patchResponse->assertRedirect();
    $prospect->refresh();

    expect($prospect->stage)->toBe('negociation')
        ->and($prospect->probability)->toBe(85);
});

test('can convert prospect to customer', function () {
    $prospect = Prospect::create([
        'name' => 'Prospect Conversion Test',
        'company' => 'Société Test SARL',
        'email' => 'prospect.convert@test.ci',
        'phone' => '+225 0707070707',
        'stage' => 'negociation',
        'probability' => 85,
        'estimated_value' => 2000000,
        'assigned_to' => $this->admin->id,
    ]);

    $response = $this->post(route('commercial.prospects.convert', $prospect));
    $response->assertRedirect();

    $prospect->refresh();
    expect($prospect->stage)->toBe('gagne')
        ->and($prospect->status)->toBe('gagné');

    $newCustomer = Customer::where('name', 'Prospect Conversion Test')->first();
    expect($newCustomer)->not->toBeNull()
        ->and($newCustomer->company)->toBe('Société Test SARL')
        ->and($newCustomer->phone)->toBe('+225 0707070707');
});

test('can render POS terminal with barcode scanner features', function () {
    $product = Product::first();
    expect($product)->not->toBeNull();

    $response = $this->get(route('commercial.pos.index'));
    $response->assertOk();
    $response->assertSee('posBarcodeScanner');
    $response->assertSee('x-ref="searchInput"', false);
    $response->assertSee('x-ref="scannerVideo"', false);
    $response->assertSee('Scanner');
    $response->assertSee('Scanner un code-barres');
});

test('can process POS counter sale checkout', function () {
    $product = Product::first();
    expect($product)->not->toBeNull();

    $customer = Customer::first();
    $totalExpected = (2 * 7500) * (1 + (($product->tax_rate ?? 0) / 100));

    $response = $this->post(route('commercial.pos.checkout'), [
        'customer_id' => $customer->id,
        'payment_method' => 'especes',
        'amount_paid' => $totalExpected,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 7500,
                'discount' => 0,
            ],
        ],
    ]);

    $response->assertRedirect();

    $order = Order::where('customer_id', $customer->id)->latest('id')->first();
    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('livree')
        ->and((float) $order->subtotal)->toBe(15000.0);

    $invoice = Invoice::where('order_id', $order->id)->first();
    expect($invoice)->not->toBeNull()
        ->and($invoice->status)->toBe('payee');
});

test('can process POS checkout with mobile money operator', function () {
    $product = Product::first();
    $customer = Customer::first();
    $totalExpected = 7500 * (1 + (($product->tax_rate ?? 0) / 100));

    $response = $this->post(route('commercial.pos.checkout'), [
        'customer_id' => $customer->id,
        'payment_method' => 'mobile_money',
        'mobile_money_provider' => 'wave',
        'amount_paid' => $totalExpected,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => 7500,
                'discount' => 0,
            ],
        ],
    ]);

    $response->assertRedirect();

    $payment = Payment::where('customer_id', $customer->id)->latest('id')->first();
    expect($payment)->not->toBeNull()
        ->and($payment->method)->toBe('mobile_money')
        ->and($payment->notes)->toContain('Wave');
});

test('can render printable quotation and invoice views', function () {
    $quotation = Quotation::first();
    expect($quotation)->not->toBeNull();

    $quoteResponse = $this->get(route('commercial.quotations.print', $quotation));
    $quoteResponse->assertOk();
    $quoteResponse->assertSee($quotation->reference);
    $quoteResponse->assertSee('DEVIS PROFORMA');

    $invoice = Invoice::first();
    expect($invoice)->not->toBeNull();

    $invResponse = $this->get(route('commercial.invoices.print', $invoice));
    $invResponse->assertOk();
    $invResponse->assertSee($invoice->reference);
    $invResponse->assertSee('FACTURE CLIENT');
});

test('can schedule and manage commercial appointments', function () {
    $customer = Customer::first();

    $response = $this->post(route('commercial.appointments.store'), [
        'title' => 'Entretien Commercial Clôture',
        'customer_id' => $customer->id,
        'date' => now()->addDays(2)->format('Y-m-d'),
        'time' => '14:30',
        'location' => 'Plateau Tour Postel 2001',
        'status' => 'planifié',
        'notes' => 'Signature du bon de commande',
    ]);

    $response->assertRedirect();

    $appointment = CommercialAppointment::where('title', 'Entretien Commercial Clôture')->first();
    expect($appointment)->not->toBeNull()
        ->and($appointment->location)->toBe('Plateau Tour Postel 2001');

    // Update outcome
    $updateResponse = $this->put(route('commercial.appointments.update', $appointment), [
        'title' => 'Entretien Commercial Clôture',
        'date' => $appointment->date->format('Y-m-d'),
        'status' => 'effectué',
        'notes' => 'Client a signé le bon de commande en séance.',
    ]);

    $updateResponse->assertRedirect();
    $appointment->refresh();
    expect($appointment->status)->toBe('effectué');
});

test('can create and toggle promotions', function () {
    $code = 'TESTPROMO'.rand(100, 999);

    $response = $this->post(route('commercial.promotions.store'), [
        'code' => $code,
        'name' => 'Campagne Test Promo',
        'type' => 'percent',
        'value' => 15,
        'min_amount' => 20000,
        'is_active' => true,
    ]);

    $response->assertRedirect();

    $promo = Promotion::where('code', $code)->first();
    expect($promo)->not->toBeNull()
        ->and((float) $promo->value)->toBe(15.0)
        ->and($promo->is_active)->toBeTrue();

    // Toggle
    $toggleResponse = $this->patch(route('commercial.promotions.toggle', $promo));
    $toggleResponse->assertRedirect();
    $promo->refresh();
    expect($promo->is_active)->toBeFalse();
});

test('commercial product routes redirect to stock logistics catalog and pos has no product addition buttons', function () {
    $response = $this->get(route('commercial.products.index'));
    $response->assertRedirect(route('stock.index', ['tab' => 'disponibilite']));

    $createResponse = $this->get(route('commercial.products.create'));
    $createResponse->assertRedirect(route('stock.products.create'));

    $posResponse = $this->get(route('commercial.pos.index'));
    $posResponse->assertOk();
    $posResponse->assertDontSee('+ Ajouter Produit');
});

test('cannot validate pos checkout if requested quantity exceeds available stock', function () {
    $product = Product::first();
    $availableStock = $product->available_stock;

    $excessiveQty = $availableStock + 10;

    $response = $this->post(route('commercial.pos.checkout'), [
        'payment_method' => 'especes',
        'amount_paid' => $excessiveQty * $product->selling_price,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => $excessiveQty,
                'unit_price' => $product->selling_price,
                'discount' => 0,
            ],
        ],
    ]);

    $response->assertSessionHas('error');
    $response->assertRedirect();
});

test('can validate pos checkout when quantity is within available stock', function () {
    $product = Product::first();
    expect($product->available_stock)->toBeGreaterThan(0);

    $validQty = 1;

    $response = $this->post(route('commercial.pos.checkout'), [
        'payment_method' => 'especes',
        'amount_paid' => $validQty * $product->selling_price,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => $validQty,
                'unit_price' => $product->selling_price,
                'discount' => 0,
            ],
        ],
    ]);

    $response->assertSessionHas('success');
    $response->assertRedirect();
});

test('automatically decrements product stock and records stock movement on POS checkout', function () {
    $product = Product::first();
    $initialStock = (int) $product->current_stock;
    expect($initialStock)->toBeGreaterThan(1);

    $qtyToBuy = 2;

    $response = $this->post(route('commercial.pos.checkout'), [
        'payment_method' => 'especes',
        'amount_paid' => $qtyToBuy * $product->selling_price,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => $qtyToBuy,
                'unit_price' => $product->selling_price,
                'discount' => 0,
            ],
        ],
    ]);

    $response->assertSessionHas('success');

    $product->refresh();
    expect((int) $product->current_stock)->toBe($initialStock - $qtyToBuy);

    $movement = StockMovement::where('product_id', $product->id)
        ->where('type', 'sortie')
        ->where('reason_motif', 'vente')
        ->latest('id')
        ->first();

    expect($movement)->not->toBeNull()
        ->and($movement->quantity)->toBe($qtyToBuy);
});

test('can render invoice creation form and store new invoice', function () {
    $customer = Customer::first();
    $product = Product::first();

    // 1. Render create form
    $createResponse = $this->get(route('commercial.invoices.create'));
    $createResponse->assertOk();
    $createResponse->assertSee('Nouvelle Facture de Vente');
    $createResponse->assertSee($customer->name);

    // 2. Store new invoice
    $storeResponse = $this->post(route('commercial.invoices.store'), [
        'customer_id' => $customer->id,
        'date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(30)->format('Y-m-d'),
        'status' => 'non_payee',
        'notes' => 'Facture test pour validation',
        'items' => [
            [
                'product_id' => $product->id,
                'description' => $product->name,
                'quantity' => 2,
                'unit_price' => 50000,
                'tax_rate' => 18,
                'discount' => 5000,
            ],
        ],
    ]);

    $storeResponse->assertRedirect(route('commercial.invoices.index'));
    $storeResponse->assertSessionHas('success');

    $this->assertDatabaseHas('invoices', [
        'customer_id' => $customer->id,
        'status' => 'non_payee',
    ]);
});
