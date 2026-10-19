<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\BusinessForecastService;
use App\Services\CommercialAgentBriefing;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Symfony\Component\Process\ExecutableFinder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->admin = User::where('email', 'admin@ivosphere.com')->firstOrFail();
    $this->actingAs($this->admin);

    $warehouse = Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT', 'is_active' => true]);
    $this->ream = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'selling_price' => 3500, 'min_stock' => 5, 'is_active' => true]);
    WarehouseStock::create(['warehouse_id' => $warehouse->id, 'product_id' => $this->ream->id, 'physical_quantity' => 12, 'reserved_quantity' => 0]);

    // 40 jours de ventes : 3 rames par jour (10 500 FCFA)
    $customer = Customer::create(['code' => 'CLI-001', 'name' => 'Librairie Awa', 'type' => 'entreprise', 'status' => 'actif']);
    foreach (range(1, 40) as $daysAgo) {
        $order = Order::create([
            'reference' => 'CMD-'.$daysAgo, 'customer_id' => $customer->id, 'user_id' => $this->admin->id,
            'date' => now()->subDays($daysAgo)->toDateString(), 'status' => 'livree', 'total' => 10500,
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $this->ream->id, 'description' => 'Rame papier A4', 'quantity' => 3, 'unit_price' => 3500, 'total' => 10500]);
    }
});

test('without python the forecast falls back to recent averages', function () {
    $forecast = app(BusinessForecastService::class)->refresh();

    expect($forecast['engine'])->toBe('php')
        ->and($forecast['sales']['next_7_days'])->toEqual(round(10500 * 27 / 28 * 7))
        ->and($forecast['stockouts'][0])->toMatchArray(['name' => 'Rame papier A4', 'stock' => 12.0, 'days_left' => 4.9])
        ->and(Setting::get(BusinessForecastService::SETTING_KEY)['engine'])->toBe('php');
});

test('the python script computes the forecast when python is available', function () {
    $python = (new ExecutableFinder)->find('python3');
    if (! $python || ! str_contains((string) shell_exec($python.' -c "import pandas, numpy; print(1)" 2>/dev/null'), '1')) {
        $this->markTestSkipped('python3 avec pandas indisponible.');
    }

    config(['services.python.binary' => $python]);
    $forecast = app(BusinessForecastService::class)->refresh();

    expect($forecast['engine'])->toBe('python')
        ->and($forecast['sales']['method'])->toBe('regression')
        ->and($forecast['sales']['next_7_days'])->toBeGreaterThan(60000)->toBeLessThan(85000)
        ->and($forecast['stockouts'][0]['name'])->toBe('Rame papier A4')
        ->and($forecast['stockouts'][0]['days_left'])->toBeLessThan(7);
});

test('a broken python setup does not break the forecast', function () {
    config(['services.python.binary' => '/nonexistent/python']);

    expect(app(BusinessForecastService::class)->refresh()['engine'])->toBe('php');
});

test('the nightly command refreshes the forecast of each space', function () {
    $this->artisan('ai:forecast')->assertSuccessful();

    expect(Setting::get(BusinessForecastService::SETTING_KEY))->toHaveKeys(['engine', 'generated_at', 'sales', 'stockouts']);
});

test('the assistant answers forecast questions', function () {
    $this->postJson(route('ai.query'), ['query' => 'Quels produits seront bientôt en rupture ?'])
        ->assertOk()
        ->assertJsonPath('intent', 'forecast_stockouts')
        ->assertJsonPath('table.rows.0.0', 'Rame papier A4');

    $this->postJson(route('ai.query'), ['query' => 'Quand vais-je être en rupture de rame papier A4 ?'])
        ->assertJsonPath('intent', 'forecast_stockouts')
        ->assertJsonPath('table', null);

    $this->postJson(route('ai.query'), ['query' => 'Quelle est la prévision des ventes des 30 prochains jours ?'])
        ->assertJsonPath('intent', 'forecast_sales');
});

test('sales forecasts follow the ai sales permission', function () {
    $technician = User::create(['name' => 'Koffi', 'email' => 'tech@ivosphere.test', 'password' => bcrypt('password'), 'is_active' => true]);
    $technician->roles()->attach(Role::where('slug', 'agent_technique')->firstOrFail());

    $this->actingAs($technician)
        ->postJson(route('ai.query'), ['query' => 'Quelle est la prévision des ventes des 30 prochains jours ?'])
        ->assertJsonPath('intent', 'forbidden');
});

test('ivo announces the forecast in the daily briefing', function () {
    $texts = collect(app(CommercialAgentBriefing::class)->build('Awa')['messages'])->pluck('text')->implode(' ');

    expect($texts)->toContain('Prévision : environ')
        ->and($texts)->toContain('risquent la rupture d\'ici 7 jours, dont **Rame papier A4**');
});
