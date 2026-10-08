<?php

use App\Models\Domain;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('creating an agent user in admin automatically provisions an employee record', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $agentRole = Role::where('slug', 'agent_commercial_terrain')->first();
    $domain = Domain::first();

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Agent Dupont',
        'email' => 'agent.dupont@ivosphere.com',
        'phone' => '+225 07 11 22 33 44',
        'password' => 'Password123!',
        'roles' => [$agentRole->id],
        'domains' => [$domain->id],
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'agent.dupont@ivosphere.com')->first();
    expect($user)->not->toBeNull();

    $employee = $user->employee;
    expect($employee)->not->toBeNull();
    expect($employee->email)->toBe('agent.dupont@ivosphere.com');
    expect($employee->first_name)->toBe('Agent');
    expect($employee->last_name)->toBe('Dupont');
    expect($employee->position)->toBe('Agent Commercial Terrain');
    expect($employee->department)->toBe('Pôle Commercial');
    expect($employee->status)->toBe('actif');
    expect($employee->employee_code)->toStartWith('EMP-COM-');
});

test('creating a responsable user automatically provisions an employee record with manager position', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $managerRole = Role::where('slug', 'responsable_commercial')->first();
    $domain = Domain::first();

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Kone Mamadou',
        'email' => 'kone.mamadou@ivosphere.com',
        'phone' => '+225 07 99 88 77 66',
        'password' => 'Password123!',
        'roles' => [$managerRole->id],
        'domains' => [$domain->id],
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'kone.mamadou@ivosphere.com')->first();
    expect($user)->not->toBeNull();

    $employee = $user->employee;
    expect($employee)->not->toBeNull();
    expect($employee->position)->toBe('Responsable Commercial');
    expect($employee->department)->toBe('Pôle Commercial');
    expect($employee->status)->toBe('actif');
});

test('updating user profile synchronizes the linked employee record', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $agentRole = Role::where('slug', 'agent_caissier_vendeur')->first();
    $domain = Domain::first();

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Paul Caisse',
        'email' => 'paul.caisse@ivosphere.com',
        'phone' => '+225 01 02 03 04 05',
        'password' => 'Password123!',
        'roles' => [$agentRole->id],
        'domains' => [$domain->id],
        'is_active' => 1,
    ]);

    $user = User::where('email', 'paul.caisse@ivosphere.com')->first();

    $this->actingAs($admin)->put(route('admin.users.update', $user), [
        'name' => 'Paul Caisse Modifie',
        'email' => 'paul.modifie@ivosphere.com',
        'phone' => '+225 05 05 05 05 05',
        'roles' => [$agentRole->id],
        'domains' => [$domain->id],
        'is_active' => 1,
    ]);

    $employee = $user->fresh()->employee;
    expect($employee->first_name)->toBe('Paul');
    expect($employee->last_name)->toBe('Caisse Modifie');
    expect($employee->email)->toBe('paul.modifie@ivosphere.com');
    expect($employee->phone)->toBe('+225 05 05 05 05 05');
});

test('creating employee with erp account option creates both employee and user', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();
    $domain = Domain::first();
    $agentRole = Role::where('slug', 'agent_technique')->first();

    $response = $this->actingAs($rh)->post(route('rh.employees.store'), [
        'domain_id' => $domain->id,
        'employee_code' => 'EMP-TEST-9999',
        'first_name' => 'Marc',
        'last_name' => 'Technicien',
        'email' => 'marc.tech@ivosphere.com',
        'phone' => '+225 07 44 55 66 77',
        'position' => 'Technicien PAO',
        'department' => 'Atelier Print',
        'status' => 'actif',
        'create_user_account' => 1,
        'role_id' => $agentRole->id,
        'user_password' => 'SecurePass2026@',
    ]);

    $response->assertRedirect(route('rh.employees.index'));

    $employee = Employee::where('email', 'marc.tech@ivosphere.com')->first();
    expect($employee)->not->toBeNull();
    expect($employee->user_id)->not->toBeNull();

    $user = $employee->user;
    expect($user)->not->toBeNull();
    expect($user->email)->toBe('marc.tech@ivosphere.com');
    expect($user->hasRole('agent_technique'))->toBeTrue();
});

test('toggling user status synchronizes employee status', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $agentRole = Role::where('slug', 'agent_monetique')->first();
    $domain = Domain::first();

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Saliou Monetique',
        'email' => 'saliou.monetique@ivosphere.com',
        'password' => 'Password123!',
        'roles' => [$agentRole->id],
        'domains' => [$domain->id],
        'is_active' => 1,
    ]);

    $user = User::where('email', 'saliou.monetique@ivosphere.com')->first();
    expect($user->employee->status)->toBe('actif');

    $this->actingAs($admin)->patch(route('admin.users.toggle', $user));

    expect($user->fresh()->is_active)->toBeFalse();
    expect($user->fresh()->employee->status)->toBe('inactif');
});

test('employee creation form automatically generates and prefills employee code as non modifiable', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();

    $response = $this->actingAs($rh)->get(route('rh.employees.create'));

    $response->assertStatus(200);
    $response->assertSee('Non modifiable');
    $response->assertSee('EMP-');
});

test('submitting employee form without code automatically generates unique employee code', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();
    $domain = Domain::first();

    $response = $this->actingAs($rh)->post(route('rh.employees.store'), [
        'domain_id' => $domain->id,
        'employee_code' => '', // laisse vide pour auto-generation
        'first_name' => 'Fanta',
        'last_name' => 'Koffi',
        'email' => 'fanta.koffi@ivosphere.com',
        'phone' => '+225 07 10 20 30 40',
        'position' => 'Charge d\'accueil',
        'department' => 'Accueil',
        'status' => 'actif',
    ]);

    $response->assertRedirect(route('rh.employees.index'));

    $employee = Employee::where('email', 'fanta.koffi@ivosphere.com')->first();
    expect($employee)->not->toBeNull();
    expect($employee->employee_code)->not->toBeEmpty();
    expect($employee->employee_code)->toStartWith('EMP-');
});

test('employee code cannot be modified during employee update', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();
    $employee = Employee::first();
    $originalCode = $employee->employee_code;

    $response = $this->actingAs($rh)->put(route('rh.employees.update', $employee), [
        'domain_id' => $employee->domain_id,
        'employee_code' => 'EMP-TAMPERED-CODE',
        'first_name' => 'Jean',
        'last_name' => 'Dupont',
        'email' => $employee->email,
        'status' => 'actif',
    ]);

    $response->assertRedirect(route('rh.employees.index'));
    expect($employee->fresh()->employee_code)->toBe($originalCode);
    expect($employee->fresh()->employee_code)->not->toBe('EMP-TAMPERED-CODE');
});
