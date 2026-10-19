<?php

use App\Models\Employee;
use App\Models\HrSetting;
use App\Models\ScheduleDay;
use App\Models\User;
use App\Models\WorkSchedule;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    // Mercredi 21 octobre 2026 : la semaine commence le lundi 19.
    $this->travelTo(Carbon::parse('2026-10-21 10:00:00'));

    $this->admin = User::where('email', 'admin@ivosphere.com')->first();
    HrSetting::current()->update(['max_work_days_per_week' => 6, 'work_start_time' => '08:00', 'work_end_time' => '18:00']);

    $this->employee = Employee::create([
        'employee_code' => 'EMP-PLAN-01',
        'first_name' => 'Mariam',
        'last_name' => 'Traoré',
        'email' => 'mariam.plan@ivosphere.com',
        'status' => 'actif',
    ]);
});

/**
 * Semaine du formulaire : lundi à vendredi travaillés de 09:00 à 17:00, samedi et dimanche en repos.
 *
 * @return array<int, array<string, string>>
 */
function weekDaysPayload(array $overrides = []): array
{
    $days = [];
    for ($i = 1; $i <= 7; $i++) {
        $days[$i] = $i <= 5
            ? ['is_working_day' => '1', 'start_time' => '09:00', 'end_time' => '17:00', 'notes' => '']
            : ['start_time' => '09:00', 'end_time' => '17:00', 'notes' => ''];
    }

    return array_replace($days, $overrides);
}

test('a weekly schedule with rest days can be saved', function () {
    $this->actingAs($this->admin)->post(route('rh.schedules.store'), [
        'employee_id' => $this->employee->id,
        'week_start_date' => '2026-10-19',
        'days' => weekDaysPayload(),
    ])->assertRedirect(route('rh.schedules.index', ['week' => '2026-10-19']))
        ->assertSessionHasNoErrors();

    $schedule = WorkSchedule::where('employee_id', $this->employee->id)->firstOrFail();
    $sunday = $schedule->days()->where('day_of_week', 7)->first();
    $monday = $schedule->days()->where('day_of_week', 1)->first();

    expect($schedule->total_working_days)->toBe(5)
        ->and($schedule->days()->count())->toBe(7)
        ->and($sunday->is_working_day)->toBeFalse()
        ->and($sunday->start_time)->toBeNull()
        ->and($monday->start_time)->toBe('09:00')
        ->and($monday->end_time)->toBe('17:00');
});

test('saving the same week again updates the existing schedule', function () {
    foreach (['09:00', '10:30'] as $startTime) {
        $this->actingAs($this->admin)->post(route('rh.schedules.store'), [
            'employee_id' => $this->employee->id,
            'week_start_date' => '2026-10-19',
            'days' => weekDaysPayload([1 => ['is_working_day' => '1', 'start_time' => $startTime, 'end_time' => '17:00']]),
        ])->assertSessionHasNoErrors()->assertRedirect();
    }

    $schedule = WorkSchedule::where('employee_id', $this->employee->id)->sole();

    expect($schedule->days()->count())->toBe(7)
        ->and($schedule->days()->where('day_of_week', 1)->value('start_time'))->toBe('10:30');
});

test('a schedule exceeding the maximum working days is refused', function () {
    HrSetting::current()->update(['max_work_days_per_week' => 5]);

    $this->actingAs($this->admin)->post(route('rh.schedules.store'), [
        'employee_id' => $this->employee->id,
        'week_start_date' => '2026-10-19',
        'days' => weekDaysPayload([6 => ['is_working_day' => '1', 'start_time' => '09:00', 'end_time' => '13:00']]),
    ])->assertSessionHas('error');

    expect(WorkSchedule::count())->toBe(0);
});

test('a working day must end after it starts and needs both times', function () {
    $this->actingAs($this->admin)->post(route('rh.schedules.store'), [
        'employee_id' => $this->employee->id,
        'week_start_date' => '2026-10-19',
        'days' => weekDaysPayload([2 => ['is_working_day' => '1', 'start_time' => '17:00', 'end_time' => '09:00']]),
    ])->assertSessionHasErrors(['days.2.end_time' => 'L\'heure de fin du mardi doit être postérieure à l\'heure de début.']);

    $this->actingAs($this->admin)->post(route('rh.schedules.store'), [
        'employee_id' => $this->employee->id,
        'week_start_date' => '2026-10-19',
        'days' => weekDaysPayload([3 => ['is_working_day' => '1', 'start_time' => '', 'end_time' => '17:00']]),
    ])->assertSessionHasErrors(['days.3.start_time' => 'Indiquez l\'heure de début de chaque jour travaillé.']);

    expect(WorkSchedule::count())->toBe(0);
});

test('the standard week follows the hr settings for working days and hours', function () {
    HrSetting::current()->update(['max_work_days_per_week' => 5]);

    $this->actingAs($this->admin)->post(route('rh.schedules.apply-standard'), ['week' => '2026-10-19'])
        ->assertRedirect(route('rh.schedules.index', ['week' => '2026-10-19']))
        ->assertSessionHas('success');

    $days = WorkSchedule::where('employee_id', $this->employee->id)->firstOrFail()->days;

    expect($days)->toHaveCount(7)
        ->and($days->where('is_working_day', true)->pluck('day_of_week')->all())->toBe([1, 2, 3, 4, 5])
        ->and($days->firstWhere('day_of_week', 1)->start_time)->toBe('08:00')
        ->and($days->firstWhere('day_of_week', 1)->end_time)->toBe('18:00')
        ->and($days->firstWhere('day_of_week', 6)->start_time)->toBeNull();
});

test('an invalid week parameter falls back to the current week', function (string $routeName) {
    $this->actingAs($this->admin)->get(route($routeName, ['week' => 'pas-une-date']))
        ->assertOk()
        ->assertSee('19/10');
})->with(['rh.schedules.index', 'rh.schedules.create']);

test('the edit form shows the saved schedule of the selected employee', function () {
    $this->actingAs($this->admin)->post(route('rh.schedules.store'), [
        'employee_id' => $this->employee->id,
        'week_start_date' => '2026-10-19',
        'days' => weekDaysPayload([1 => ['is_working_day' => '1', 'start_time' => '07:30', 'end_time' => '15:45']]),
    ]);

    $this->actingAs($this->admin)->get(route('rh.schedules.create', ['employee_id' => $this->employee->id, 'week' => '2026-10-19']))
        ->assertOk()
        ->assertSee('Modifier le Planning Hebdomadaire')
        ->assertSee('07:30')
        ->assertSee('15:45');
});

test('my space shows the schedule of the current week, not a later one', function () {
    $this->employee->update(['user_id' => $this->admin->id]);

    foreach (['2026-10-19' => '07:15', '2026-10-26' => '11:45'] as $weekStart => $startTime) {
        $schedule = WorkSchedule::create(['employee_id' => $this->employee->id, 'week_start_date' => $weekStart, 'status' => 'valide', 'total_working_days' => 1]);
        ScheduleDay::create(['work_schedule_id' => $schedule->id, 'date' => $weekStart, 'day_of_week' => 1, 'is_working_day' => true, 'start_time' => $startTime, 'end_time' => '18:00']);
    }

    $this->actingAs($this->admin)->get(route('rh.portal.index'))
        ->assertOk()
        ->assertSee('07:15')
        ->assertDontSee('11:45');
});
