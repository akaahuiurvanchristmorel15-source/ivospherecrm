<?php

use App\Models\Attendance;
use App\Models\DailyTaskSheet;
use App\Models\Employee;
use App\Models\SalesTarget;
use App\Models\User;
use App\Models\WorkSchedule;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->admin = User::where('email', 'admin@ivosphere.com')->first();

    $this->employee = Employee::create([
        'employee_code' => 'EMP-RENDER',
        'first_name' => 'Awa',
        'last_name' => 'Koné',
        'email' => 'awa.render@ivosphere.com',
        'phone' => '+225 07 00 00 00 99',
        'position' => 'Vendeuse',
        'department' => 'Commercial',
        'status' => 'actif',
    ]);
});

test('rh pages render with employee data', function () {
    $weekStart = now()->startOfWeek();

    Attendance::create([
        'employee_id' => $this->employee->id,
        'date' => today(),
        'check_in' => '08:05:00',
        'check_in_at' => today()->setTime(8, 5),
        'delay_minutes' => 5,
        'punctuality_score' => 0.33,
        'status' => 'present',
        'qr_type' => 'premises_monthly_poster',
    ]);

    // SQLite stocke les dates castées avec l'heure : on aligne sur le format DATE de MySQL
    DB::table('attendances')->update(['date' => today()->toDateString()]);

    $schedule = WorkSchedule::create([
        'employee_id' => $this->employee->id,
        'week_start_date' => $weekStart,
        'total_working_days' => 6,
    ]);
    foreach (range(1, 7) as $dayOfWeek) {
        $schedule->days()->create([
            'date' => $weekStart->copy()->addDays($dayOfWeek - 1),
            'day_of_week' => $dayOfWeek,
            'is_working_day' => $dayOfWeek < 7,
            'start_time' => '08:00',
            'end_time' => '20:00',
        ]);
    }

    DailyTaskSheet::create([
        'reference' => 'TCH-RENDER-1',
        'employee_id' => $this->employee->id,
        'date' => today(),
        'position' => 'Vendeuse',
        'tasks' => [['label' => 'Ranger la boutique', 'status' => 'termine'], ['label' => 'Appeler 5 clients', 'status' => 'assigne']],
        'status' => 'en_cours',
    ]);

    SalesTarget::create([
        'employee_id' => $this->employee->id,
        'title' => 'Objectif octobre',
        'target_amount' => 1000000,
        'start_date' => today()->startOfMonth(),
        'end_date' => today()->endOfMonth(),
        'period' => 'mensuel',
        'commission_rate' => 2,
    ]);

    $this->actingAs($this->admin);

    $this->get(route('rh.index'))->assertOk()->assertSee('Ressources humaines');
    $this->get(route('rh.attendance.index'))->assertOk()->assertSee('Awa Koné')->assertSee('08:05');
    $this->get(route('rh.employees.index'))->assertOk()->assertSee('Awa Koné')->assertSee('EMP-RENDER');
    $this->get(route('rh.schedules.index'))->assertOk()->assertSee('Awa Koné')->assertSee('Repos');
    $this->get(route('rh.daily-tasks.index'))->assertOk()->assertSee('TCH-RENDER-1')->assertSee('1/2');
    $this->get(route('rh.daily-tasks.create'))->assertOk()->assertSee('Nouvelle fiche de tâches');
    $this->get(route('rh.sales-targets.index'))->assertOk()->assertSee('Objectif octobre');
});

test('rh list pages render their empty states', function () {
    $this->actingAs($this->admin);

    $this->get(route('rh.attendance.index', ['date' => '2020-01-06']))->assertOk()->assertSee('Aucun pointage');
    $this->get(route('rh.daily-tasks.index'))->assertOk()->assertSee('Aucune fiche de tâches');
    $this->get(route('rh.sales-targets.index'))->assertOk()->assertSee('Aucun objectif commercial');
});
