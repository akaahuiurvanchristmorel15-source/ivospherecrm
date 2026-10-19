<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\CommercialAgentBriefing;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->actingAs(User::where('email', 'admin@ivosphere.com')->firstOrFail());

    $warehouse = Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT', 'is_active' => true]);
    $this->ream = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'selling_price' => 3500, 'min_stock' => 10, 'is_active' => true]);
    WarehouseStock::create(['warehouse_id' => $warehouse->id, 'product_id' => $this->ream->id, 'physical_quantity' => 12, 'reserved_quantity' => 0]);
});

test('the agent briefs on today sales, the star product and low stock', function () {
    $this->post(route('commercial.pos.checkout'), [
        'payment_method' => 'especes',
        'amount_paid' => 7000,
        'items' => [['product_id' => $this->ream->id, 'quantity' => 2, 'unit_price' => 3500]],
    ])->assertRedirect();

    $briefing = app(CommercialAgentBriefing::class)->build('Awa');
    $texts = collect($briefing['messages'])->pluck('text')->implode(' ');

    expect($briefing['greeting'])->toContain('Awa')
        ->and($texts)->toContain("Aujourd'hui, vous avez encaissé **7 000 FCFA**")
        ->and($texts)->toContain('Produit star du mois : **Rame papier A4**')
        ->and($texts)->toContain('**1 produit(s)** sous le seuil minimum')
        ->and(collect($briefing['week'])->last())->toMatchArray(['amount' => 7000.0, 'is_today' => true]);
});

test('the commercial dashboard shows the animated agent with its briefing', function () {
    $this->get(route('commercial.index'))
        ->assertOk()
        ->assertSee('id="commercial-agent"', false)
        ->assertSee('Agent commercial intelligent')
        ->assertSee('commercialAgent(', false)
        ->assertSee('Quels sont les produits les plus vendus ce mois-ci ?');
});

test('the agent answers commerce questions as json', function () {
    $this->postJson(route('ai.query'), ['query' => 'Quels produits sont en rupture de stock ?'])
        ->assertOk()
        ->assertJsonPath('intent', 'stock_low');
});
