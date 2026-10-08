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
});

test('admin can access user management', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertStatus(200);
    $response->assertSee('Utilisateurs de la Plateforme');
});

test('non-admin cannot access user management', function () {
    $commercial = User::where('email', 'commercial@ivosphere.com')->first();

    $response = $this->actingAs($commercial)->get('/admin/users');

    $response->assertStatus(403);
});

test('rh manager can access rh pole but commercial cannot', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();
    $commercial = User::where('email', 'commercial@ivosphere.com')->first();

    $this->actingAs($rh)->get('/rh')->assertStatus(200);
    $this->actingAs($commercial)->get('/rh')->assertStatus(403);
});

test('commercial manager can access commercial pole', function () {
    $commercial = User::where('email', 'commercial@ivosphere.com')->first();

    $response = $this->actingAs($commercial)->get('/commercial');

    $response->assertStatus(200);
    $response->assertSee('Tableau de bord Commercial');
});

test('admin can access role and permission management matrix', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get('/admin/roles');

    $response->assertStatus(200);
    $response->assertSee('Matrice des Rôles & Permissions', false);
    $response->assertSee('Administrateur');
    $response->assertSee('Droits Disponibles');
});

test('non-admin cannot access role management', function () {
    $commercial = User::where('email', 'commercial@ivosphere.com')->first();

    $response = $this->actingAs($commercial)->get('/admin/roles');

    $response->assertStatus(403);
});

test('admin can update role permissions', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $role = Role::where('slug', 'responsable_commercial')->first();
    $permissions = Permission::take(3)->pluck('id')->toArray();

    $response = $this->actingAs($admin)->put("/admin/roles/{$role->id}/permissions", [
        'permissions' => $permissions,
    ]);

    $response->assertRedirect('/admin/roles?role='.$role->id);
    $response->assertSessionHas('success');
    expect($role->fresh()->permissions->pluck('id')->toArray())->toEqualCanonicalizing($permissions);
});

test('admin can view user creation form with all requested roles', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get(route('admin.users.create'));

    $response->assertStatus(200);
    $response->assertSee('Administrateur');
    $response->assertSee('Responsable');
    $response->assertSee('Agent commercial terrain');
    $response->assertSee('Agent caissier et vendeur');
    $response->assertSee('Agent technique');
    $response->assertSee('Agent monétique', false);
    $response->assertSee('Agent cyber');
});

test('admin can create user with responsable role and verify specific permissions', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $role = Role::where('slug', 'responsable')->first();

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Koffi Responsable',
        'email' => 'koffi.resp@ivosphere.com',
        'phone' => '+225 01 02 03 04 05',
        'password' => 'Password123!',
        'roles' => [$role->id],
        'all_domains' => 1,
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.users.index'));
    $newUser = User::where('email', 'koffi.resp@ivosphere.com')->first();
    expect($newUser)->not->toBeNull();
    expect($newUser->hasRole('responsable'))->toBeTrue();
    expect($newUser->hasPermission('quotations.view'))->toBeTrue();
    expect($newUser->hasPermission('orders.manage'))->toBeTrue();
    expect($newUser->hasPermission('leaves.manage'))->toBeTrue();
    expect($newUser->hasPermission('finance.cash'))->toBeTrue();
});

test('admin can create user with agent commercial terrain role and verify permissions', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $role = Role::where('slug', 'agent_commercial_terrain')->first();

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Soro Commercial',
        'email' => 'soro.comm@ivosphere.com',
        'phone' => '+225 01 02 03 04 06',
        'password' => 'Password123!',
        'roles' => [$role->id],
        'all_domains' => 1,
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.users.index'));
    $user = User::where('email', 'soro.comm@ivosphere.com')->first();
    expect($user->hasRole('agent_commercial_terrain'))->toBeTrue();
    expect($user->hasPermission('customers.create'))->toBeTrue();
    expect($user->hasPermission('prospects.manage'))->toBeTrue();
    expect($user->hasPermission('quotations.create'))->toBeTrue();
    // Non-commercial permissions should not be granted
    expect($user->hasPermission('users.create'))->toBeFalse();
    expect($user->hasPermission('settings.manage'))->toBeFalse();
});

test('admin can create user with agent caissier vendeur role and verify permissions', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $role = Role::where('slug', 'agent_caissier_vendeur')->first();

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Goli Caisse',
        'email' => 'goli.caisse@ivosphere.com',
        'phone' => '+225 01 02 03 04 07',
        'password' => 'Password123!',
        'roles' => [$role->id],
        'all_domains' => 1,
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.users.index'));
    $user = User::where('email', 'goli.caisse@ivosphere.com')->first();
    expect($user->hasRole('agent_caissier_vendeur'))->toBeTrue();
    expect($user->hasPermission('finance.cash'))->toBeTrue();
    expect($user->hasPermission('pos.sales'))->toBeTrue();
    expect($user->hasPermission('payments.create'))->toBeTrue();
    expect($user->hasPermission('invoices.create'))->toBeTrue();
    expect($user->hasPermission('employees.delete'))->toBeFalse();
});

test('admin can create user with agent technique role and verify permissions', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $role = Role::where('slug', 'agent_technique')->first();

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Boli Tech',
        'email' => 'boli.tech@ivosphere.com',
        'phone' => '+225 01 02 03 04 08',
        'password' => 'Password123!',
        'roles' => [$role->id],
        'all_domains' => 1,
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.users.index'));
    $user = User::where('email', 'boli.tech@ivosphere.com')->first();
    expect($user->hasRole('agent_technique'))->toBeTrue();
    expect($user->hasPermission('tech.manage'))->toBeTrue();
    expect($user->hasPermission('print.manage'))->toBeTrue();
    expect($user->hasPermission('stocks.movements'))->toBeTrue();
});

test('admin can create user with agent monetique role and verify permissions', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $role = Role::where('slug', 'agent_monetique')->first();

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'N\'goran Monetique',
        'email' => 'ngoran.monetique@ivosphere.com',
        'phone' => '+225 01 02 03 04 09',
        'password' => 'Password123!',
        'roles' => [$role->id],
        'all_domains' => 1,
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.users.index'));
    $user = User::where('email', 'ngoran.monetique@ivosphere.com')->first();
    expect($user->hasRole('agent_monetique'))->toBeTrue();
    expect($user->hasPermission('finance.cash'))->toBeTrue();
    expect($user->hasPermission('finance.bank'))->toBeTrue();
    expect($user->hasPermission('monetique.transactions'))->toBeTrue();
});

test('admin can create user with agent cyber role and verify permissions', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $role = Role::where('slug', 'agent_cyber')->first();

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Kone Cyber',
        'email' => 'kone.cyber@ivosphere.com',
        'phone' => '+225 01 02 03 04 10',
        'password' => 'Password123!',
        'roles' => [$role->id],
        'all_domains' => 1,
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.users.index'));
    $user = User::where('email', 'kone.cyber@ivosphere.com')->first();
    expect($user->hasRole('agent_cyber'))->toBeTrue();
    expect($user->hasPermission('cyber.services'))->toBeTrue();
    expect($user->hasPermission('print.manage'))->toBeTrue();
    expect($user->hasPermission('pos.sales'))->toBeTrue();
    expect($user->hasPermission('finance.cash'))->toBeTrue();
});
