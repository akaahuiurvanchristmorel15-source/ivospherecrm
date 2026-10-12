<?php

use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\AiAssistantService;
use Carbon\Carbon;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    Carbon::setTestNow('2026-10-15 10:00:00');

    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->admin = User::where('email', 'admin@ivosphere.com')->first();
    $this->print = Domain::where('code', 'PRINT')->first();
    $this->tech = Domain::where('code', 'TECH')->first();
    $this->customer = Customer::create(['code' => 'CLI-KONE', 'name' => 'Librairie Koné', 'status' => 'actif']);
    $this->assistant = app(AiAssistantService::class);
});

afterEach(function () {
    Carbon::setTestNow();
});

function aiOrder(array $attributes): Order
{
    static $sequence = 0;
    $sequence++;

    return Order::create($attributes + [
        'reference' => 'CMD-AI-'.$sequence,
        'customer_id' => test()->customer->id,
        'user_id' => test()->admin->id,
        'status' => 'livree',
    ]);
}

test('revenue for a domain this month excludes cancelled sales and compares with last month', function () {
    aiOrder(['domain_id' => $this->print->id, 'date' => '2026-10-03', 'total' => 100000]);
    aiOrder(['domain_id' => $this->print->id, 'date' => '2026-10-10', 'total' => 50000]);
    aiOrder(['domain_id' => $this->print->id, 'date' => '2026-10-11', 'total' => 999999, 'status' => 'annulée']);
    aiOrder(['domain_id' => $this->tech->id, 'date' => '2026-10-05', 'total' => 70000]);
    aiOrder(['domain_id' => $this->print->id, 'date' => '2026-09-08', 'total' => 100000]);

    $result = $this->assistant->processQuery("Quel est le chiffre d'affaires de PRINT ce mois-ci ?");

    expect($result['intent'])->toBe('revenue')
        ->and($result['understood'])->toContain('Ce mois-ci')
        ->and($result['metrics'][0]['value'])->toBe('150 000 FCFA')
        ->and($result['metrics'][0]['trend'])->toBe(50.0)
        ->and($result['metrics'][1]['value'])->toBe('2');
});

test('the period « le mois dernier » and the domain breakdown are understood', function () {
    aiOrder(['domain_id' => $this->print->id, 'date' => '2026-09-08', 'total' => 30000]);
    aiOrder(['domain_id' => $this->tech->id, 'date' => '2026-09-20', 'total' => 90000]);
    aiOrder(['domain_id' => $this->tech->id, 'date' => '2026-10-02', 'total' => 5000]);

    $result = $this->assistant->processQuery('ventes du mois dernier');

    expect($result['intent'])->toBe('revenue')
        ->and($result['metrics'][0]['value'])->toBe('120 000 FCFA')
        ->and($result['table']['rows'][0][0])->toBe($this->tech->name);
});

test('« caisse » is a cash question, not a revenue question', function () {
    CashRegister::create(['name' => 'Caisse boutique', 'code' => 'CSE-01', 'balance' => 250000, 'is_active' => true]);

    $result = $this->assistant->processQuery('Combien il y a dans la caisse ?');

    expect($result['intent'])->toBe('cash')
        ->and($result['metrics'][0]['value'])->toBe('250 000 FCFA');
});

test('top selling products are ranked by quantity', function () {
    $ream = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'is_active' => true]);
    $pen = Product::create(['name' => 'Stylo bleu', 'sku' => 'STY-BL', 'is_active' => true]);

    $order = aiOrder(['domain_id' => $this->print->id, 'date' => '2026-10-12', 'total' => 30000]);
    $order->items()->create(['product_id' => $ream->id, 'description' => 'Rame', 'quantity' => 5, 'unit_price' => 3000, 'total' => 15000]);
    $order->items()->create(['product_id' => $pen->id, 'description' => 'Stylo', 'quantity' => 30, 'unit_price' => 500, 'total' => 15000]);

    $result = $this->assistant->processQuery('Top 5 des produits les plus vendus cette semaine');

    expect($result['intent'])->toBe('top_products')
        ->and($result['table']['rows'][0][1])->toBe('Stylo bleu')
        ->and($result['table']['rows'][1][1])->toBe('Rame papier A4');
});

test('stock of a named product is looked up', function () {
    Product::create(['name' => 'Cartouche encre noire', 'sku' => 'ENC-NR', 'is_active' => true]);

    $result = $this->assistant->processQuery('Combien de cartouche encre en stock ?');

    expect($result['intent'])->toBe('stock_lookup')
        ->and($result['answer'])->toContain('Cartouche encre noire');
});

test('unknown questions get help with examples', function () {
    $result = $this->assistant->processQuery('Quelle est la couleur du ciel ?');

    expect($result['intent'])->toBe('help')
        ->and($result['suggestions'])->not->toBeEmpty();
});

test('the assistant page renders and answers in JSON', function () {
    $this->actingAs($this->admin)->get(route('ai.index'))->assertOk()->assertSee('Assistant IVOSPHERE');

    $this->actingAs($this->admin)
        ->postJson(route('ai.query'), ['query' => 'Quelles factures sont impayées ?'])
        ->assertOk()
        ->assertJsonStructure(['intent', 'answer', 'understood', 'metrics', 'table', 'suggestions']);

    $this->actingAs($this->admin)
        ->postJson(route('ai.generate'), ['type' => 'slogans', 'topic' => 'Rentrée scolaire', 'domain' => 'IVOSPHERE PRINT'])
        ->assertOk()
        ->assertJsonPath('format', 'Accroches de campagne');
});
