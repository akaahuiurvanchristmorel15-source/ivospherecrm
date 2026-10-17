<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->admin = User::where('email', 'admin@ivosphere.com')->firstOrFail();
    $this->agentRole = Role::where('slug', 'agent_caissier_vendeur')->firstOrFail();
});

function employeeWithAccount(string $name, string $email): Employee
{
    $user = User::create(['name' => $name, 'email' => $email, 'password' => 'MotDePasse!2026', 'is_active' => true]);
    $user->roles()->sync([test()->agentRole->id]);

    return $user->ensureEmployeeProfile();
}

test('selected employees are deleted with their history and login account', function () {
    $awa = employeeWithAccount('Awa Koné', 'awa@ivosphere.ci');
    $koffi = employeeWithAccount('Koffi Yao', 'koffi@ivosphere.ci');
    $kept = employeeWithAccount('Marie Diallo', 'marie@ivosphere.ci');

    LeaveRequest::create(['employee_id' => $awa->id, 'type' => 'congé_annuel', 'start_date' => now()->addDay(), 'end_date' => now()->addDays(3),
        'days' => 2, 'reason' => 'Repos', 'status' => 'en_attente']);

    $this->actingAs($this->admin)
        ->get(route('rh.employees.index'))
        ->assertOk()
        ->assertSee('Tout sélectionner sur cette page');

    $this->actingAs($this->admin)
        ->delete(route('rh.employees.bulk-destroy'), ['employee_ids' => [$awa->id, $koffi->id]])
        ->assertRedirect(route('rh.employees.index'))
        ->assertSessionHas('success', '2 employé(s) supprimé(s) définitivement.');

    expect(Employee::whereIn('id', [$awa->id, $koffi->id])->exists())->toBeFalse()
        ->and(User::whereIn('email', ['awa@ivosphere.ci', 'koffi@ivosphere.ci'])->exists())->toBeFalse()
        ->and(LeaveRequest::where('employee_id', $awa->id)->exists())->toBeFalse()
        ->and(Employee::find($kept->id))->not->toBeNull();
});

test('an account holding business records is kept deactivated to protect the accounting', function () {
    $cashier = employeeWithAccount('Awa Koné', 'awa@ivosphere.ci');
    $expense = ['reference' => 'DEP-0001', 'user_id' => $cashier->user_id, 'category' => 'Fournitures', 'description' => 'Papier', 'date' => now()->toDateString(), 'created_at' => now(), 'updated_at' => now()];
    if (Schema::hasColumn('expenses', 'amount')) {
        $expense['amount'] = 5000;
    }
    DB::table('expenses')->insert($expense);

    $this->actingAs($this->admin)
        ->delete(route('rh.employees.bulk-destroy'), ['employee_ids' => [$cashier->id]])
        ->assertSessionHas('success');

    $user = User::where('email', 'awa@ivosphere.ci')->first();

    expect(Employee::find($cashier->id))->toBeNull()
        ->and($user)->not->toBeNull()
        ->and($user->is_active)->toBeFalse()
        ->and(DB::table('expenses')->where('reference', 'DEP-0001')->exists())->toBeTrue();
});

test('the current user can never delete their own employee file', function () {
    $ownEmployee = $this->admin->ensureEmployeeProfile();

    $this->actingAs($this->admin)
        ->delete(route('rh.employees.bulk-destroy'), ['employee_ids' => [$ownEmployee->id]])
        ->assertSessionHas('error')
        ->assertSessionHas('warning');

    expect(Employee::find($ownEmployee->id))->not->toBeNull()
        ->and($this->admin->fresh())->not->toBeNull();
});

test('only rh managers can bulk delete employees', function () {
    $target = employeeWithAccount('Awa Koné', 'awa@ivosphere.ci');
    $agent = User::where('email', 'koffi@ivosphere.ci')->first() ?? employeeWithAccount('Koffi Yao', 'koffi@ivosphere.ci')->user;

    $this->actingAs($agent)
        ->delete(route('rh.employees.bulk-destroy'), ['employee_ids' => [$target->id]])
        ->assertForbidden();

    expect(Employee::find($target->id))->not->toBeNull();
});
