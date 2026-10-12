<?php

use App\Models\CyberService;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->admin = User::where('email', 'admin@ivosphere.com')->first();

    $this->cyberAgent = User::factory()->create(['is_active' => true]);
    $this->cyberAgent->roles()->attach(Role::where('slug', 'agent_cyber')->first());
});

test('cyber agent sees the cyber page with default services', function () {
    $this->actingAs($this->cyberAgent)
        ->get(route('commercial.cyber.index'))
        ->assertOk()
        ->assertSee('Impression A4 couleur')
        ->assertSee('Photo minute (identité, 4 photos)')
        ->assertDontSee('Catalogue et tarifs du cyber');
});

test('checkout generates an invoice with catalog prices and free entries', function () {
    $colorPrint = CyberService::factory()->create(['name' => 'Impression couleur test', 'price' => 200]);

    $response = $this->actingAs($this->cyberAgent)->post(route('commercial.cyber.checkout'), [
        'payment_method' => 'especes',
        'amount_paid' => 5000,
        'items' => [
            // Le prix envoyé pour une prestation du catalogue est ignoré
            ['cyber_service_id' => $colorPrint->id, 'quantity' => 10, 'unit_price' => 1],
            ['description' => 'Mise en page CV', 'quantity' => 1, 'unit_price' => 1500],
        ],
    ]);

    $invoice = Invoice::where('reference', 'like', 'FAC-CYB-%')->firstOrFail();
    $response->assertRedirect(route('commercial.invoices.print', $invoice));

    expect((float) $invoice->total)->toBe(3500.0)
        ->and((float) $invoice->paid_amount)->toBe(3500.0)
        ->and($invoice->status)->toBe('payee')
        ->and($invoice->items()->pluck('description')->all())->toBe(['Impression couleur test', 'Mise en page CV'])
        ->and((float) Payment::where('invoice_id', $invoice->id)->sum('amount'))->toBe(3500.0);
});

test('credit checkout creates an unpaid invoice without payment', function () {
    $this->actingAs($this->cyberAgent)->post(route('commercial.cyber.checkout'), [
        'payment_method' => 'credit',
        'amount_paid' => 1000,
        'items' => [['description' => 'Scan', 'quantity' => 2, 'unit_price' => 200]],
    ]);

    $invoice = Invoice::where('reference', 'like', 'FAC-CYB-%')->firstOrFail();

    expect($invoice->status)->toBe('non_payee')
        ->and(Payment::where('invoice_id', $invoice->id)->exists())->toBeFalse();
});

test('free entry requires a description and a price', function () {
    $this->actingAs($this->cyberAgent)
        ->post(route('commercial.cyber.checkout'), [
            'payment_method' => 'especes',
            'amount_paid' => 0,
            'items' => [['quantity' => 1]],
        ])
        ->assertSessionHasErrors(['items.0.description', 'items.0.unit_price']);

    expect(Invoice::where('reference', 'like', 'FAC-CYB-%')->exists())->toBeFalse();
});

test('managers can change service prices', function () {
    $service = CyberService::factory()->create(['price' => 50]);

    $this->actingAs($this->admin)
        ->put(route('commercial.cyber.services.update', $service), [
            'name' => $service->name,
            'category' => 'impression',
            'unit' => 'page',
            'price' => 75,
            'is_active' => 1,
        ])
        ->assertRedirect();

    expect((float) $service->fresh()->price)->toBe(75.0);
});

test('cyber agents cannot change the catalog', function () {
    $service = CyberService::factory()->create(['price' => 50]);

    $this->actingAs($this->cyberAgent)
        ->put(route('commercial.cyber.services.update', $service), [
            'name' => $service->name,
            'category' => 'impression',
            'unit' => 'page',
            'price' => 1,
        ])
        ->assertForbidden();

    $this->actingAs($this->cyberAgent)
        ->post(route('commercial.cyber.services.store'), ['name' => 'X', 'category' => 'autre', 'unit' => 'page', 'price' => 10])
        ->assertForbidden();

    expect((float) $service->fresh()->price)->toBe(50.0);
});
