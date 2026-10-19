<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->admin = User::where('email', 'admin@ivosphere.com')->firstOrFail();
});

function technician(): User
{
    $user = User::create(['name' => 'Awa Koné', 'email' => 'tech@ivosphere.test', 'password' => bcrypt('password'), 'is_active' => true]);
    $user->roles()->attach(Role::where('slug', 'agent_technique')->firstOrFail());

    return $user->fresh();
}

test('the home page is a minimal assistant with a greeting and a prompt', function () {
    $this->actingAs($this->admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('id="home-assistant"', false)
        ->assertSee("Comment puis-je vous aider aujourd'hui ?")
        ->assertSee(route('dashboard.indicators'))
        ->assertDontSeeLivewire('dashboard-live');
});

test('filtered dashboard links still open the indicators', function () {
    $this->actingAs($this->admin)
        ->get(route('dashboard', ['domain' => 'PRINT']))
        ->assertOk()
        ->assertSeeLivewire('dashboard-live');
});

test('the assistant knows where each action lives in the menu', function () {
    $this->actingAs($this->admin)
        ->postJson(route('ai.query'), ['query' => 'Où créer un nouveau devis ?'])
        ->assertOk()
        ->assertJsonPath('intent', 'navigation')
        ->assertJsonPath('links.0.url', route('commercial.quotations.create'));

    $this->actingAs($this->admin)
        ->postJson(route('ai.query'), ['query' => 'Comment demander un congé ?'])
        ->assertJsonPath('links.0.url', route('rh.leaves.create'));
});

test('the assistant lists the pages of a module', function () {
    $response = $this->actingAs($this->admin)
        ->postJson(route('ai.query'), ['query' => 'Que contient le module Finance ?'])
        ->assertOk()
        ->assertJsonPath('intent', 'module');

    expect(collect($response->json('links'))->pluck('url'))->toContain(route('finance.expenses.index'));
});

test('the assistant still answers business questions', function () {
    $this->actingAs($this->admin)
        ->postJson(route('ai.query'), ['query' => 'Quels produits sont en rupture de stock ?'])
        ->assertOk()
        ->assertJsonPath('intent', 'stock_low');
});

test('the assistant only shows pages and figures the user may access', function () {
    $agent = technician();

    $links = $this->actingAs($agent)
        ->postJson(route('ai.query'), ['query' => 'Où sont les utilisateurs et les rôles ?'])
        ->json('links');

    expect(collect($links)->pluck('module'))->not->toContain('Administration');

    $this->actingAs($agent)
        ->postJson(route('ai.query'), ['query' => 'Quel est le bénéfice du mois dernier ?'])
        ->assertJsonPath('intent', 'forbidden');
});

test('a role without the assistant permission cannot use the ai', function () {
    $role = Role::create(['slug' => 'stagiaire', 'name' => 'Stagiaire']);
    $user = User::create(['name' => 'Yao Kouassi', 'email' => 'stagiaire@ivosphere.test', 'password' => bcrypt('password'), 'is_active' => true]);
    $user->roles()->attach($role);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee("L'assistant IA n'est pas activé pour votre rôle", false)
        ->assertDontSee("Comment puis-je vous aider aujourd'hui ?");

    $this->actingAs($user)->postJson(route('ai.query'), ['query' => 'Où créer un devis ?'])->assertForbidden();
    $this->actingAs($user)->get(route('ai.index'))->assertForbidden();
});

test('content generation needs the ai studio permission', function () {
    $this->actingAs(technician())
        ->postJson(route('ai.generate'), ['type' => 'post', 'topic' => 'Promo rentrée'])
        ->assertForbidden();
});

test('figures follow the ai permissions granted to the role', function () {
    $technician = technician();
    $technician->roles->first()->permissions()->detach(Permission::where('slug', 'ai.stock')->value('id'));

    $this->actingAs($technician->fresh())
        ->postJson(route('ai.query'), ['query' => 'Quels produits sont en rupture de stock ?'])
        ->assertJsonPath('intent', 'forbidden');
});
