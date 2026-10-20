<?php

use App\Models\Employee;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use App\Services\CyberAgentPerformanceService;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->admin = User::where('email', 'admin@ivosphere.com')->firstOrFail();

    $this->cashier = User::factory()->create(['is_active' => true]);
    $this->cashier->roles()->attach(Role::where('slug', 'agent_cyber')->firstOrFail());
    $this->cashierEmployee = Employee::factory()->create(['user_id' => $this->cashier->id, 'first_name' => 'Julie', 'last_name' => 'Koffi', 'position' => 'Caissière']);

    $this->armed = Employee::factory()->create(['first_name' => 'Armed', 'last_name' => 'Gnahoré', 'position' => 'Agent Cyber']);
    $this->adia = Employee::factory()->create(['first_name' => 'Adia', 'last_name' => 'Koné', 'position' => 'Agent Cyber']);
});

function cyberSale(User $cashier, ?Employee $agent, float $amount): Order
{
    test()->actingAs($cashier)->post(route('commercial.cyber.checkout'), array_filter([
        'performed_by_employee_id' => $agent?->id,
        'payment_method' => 'especes',
        'amount_paid' => $amount,
        'items' => [['description' => 'Impression', 'quantity' => 1, 'unit_price' => $amount]],
    ]))->assertRedirect();

    return Order::latest('id')->firstOrFail();
}

test('a cyber sale records the agent who performed it, distinct from the cashier', function () {
    $order = cyberSale($this->cashier, $this->armed, 1500);

    expect($order->performed_by_employee_id)->toBe($this->armed->id)
        ->and($order->user_id)->toBe($this->cashier->id);

    $this->actingAs($this->cashier)->get(route('commercial.cyber.index'))
        ->assertOk()
        ->assertSee('Réalisé par')
        ->assertSee('Armed Gnahoré');
});

test('without a chosen agent the sale is credited to the cashier employee', function () {
    $order = cyberSale($this->cashier, null, 500);

    expect($order->performed_by_employee_id)->toBe($this->cashierEmployee->id);
});

test('agents are ranked against the average with an automatic status', function () {
    $this->travelTo(now()->startOfMonth()->addDays(2));
    cyberSale($this->cashier, $this->armed, 1000);
    cyberSale($this->cashier, $this->armed, 1000);
    $this->travelTo(now()->addDay());
    cyberSale($this->cashier, $this->armed, 1000);
    cyberSale($this->cashier, $this->adia, 500);
    $cancelled = cyberSale($this->cashier, $this->adia, 9000);
    $cancelled->update(['status' => 'annulee']);

    $report = app(CyberAgentPerformanceService::class)->forMonth(now());
    $rows = collect($report['agents'])->keyBy('employee_id');

    expect($report['totals'])->toMatchArray(['revenue' => 3500.0, 'clients' => 4, 'threshold' => 1750.0])
        ->and($rows[$this->armed->id])->toMatchArray(['clients' => 3, 'days' => 2, 'revenue' => 3000.0, 'rank' => 1, 'status' => 'Performant'])
        ->and($rows[$this->adia->id])->toMatchArray(['clients' => 1, 'revenue' => 500.0, 'rank' => 2, 'status' => 'À améliorer'])
        ->and($rows[$this->cashierEmployee->id])->toMatchArray(['revenue' => 0.0, 'collected' => 3500.0, 'status' => 'Caisse'])
        ->and($report['daily']['counts'][now()->day - 1][$this->armed->id])->toBe(2);
});

test('the performance page is reserved to managers', function () {
    $this->actingAs($this->cashier)->get(route('commercial.cyber.performance'))->assertForbidden();

    cyberSale($this->cashier, $this->armed, 1200);

    $this->actingAs($this->admin)
        ->get(route('commercial.cyber.performance'))
        ->assertOk()
        ->assertSee('Classement du mois')
        ->assertSee('Armed Gnahoré')
        ->assertSee('Performant');
});
