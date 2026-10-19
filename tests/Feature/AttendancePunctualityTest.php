<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\HrSetting;
use App\Models\ScheduleDay;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Services\AttendanceService;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    HrSetting::current()->update([
        'work_start_time' => '08:00',
        'weight_punctuality' => 0.33,
        'punctuality_grace_minutes' => 5,
        'late_threshold_minutes' => 15,
    ]);

    $this->user = User::factory()->create(['is_active' => true]);
    $this->employee = Employee::create([
        'user_id' => $this->user->id,
        'employee_code' => 'EMP-PONCT-01',
        'first_name' => 'Ibrahim',
        'last_name' => 'Coulibaly',
        'email' => $this->user->email,
        'status' => 'actif',
    ]);
});

/**
 * Planning de la semaine du lundi 19 octobre 2026 avec le mercredi 21 programmé selon $wednesday.
 *
 * @param  array{is_working_day: bool, start_time: string|null}  $wednesday
 */
function planWednesday(Employee $employee, array $wednesday): void
{
    $schedule = WorkSchedule::create([
        'employee_id' => $employee->id,
        'week_start_date' => '2026-10-19',
        'status' => 'valide',
        'total_working_days' => 6,
    ]);

    ScheduleDay::create([
        'work_schedule_id' => $schedule->id,
        'date' => '2026-10-21',
        'day_of_week' => 3,
        'end_time' => $wednesday['is_working_day'] ? '17:00' : null,
        ...$wednesday,
    ]);
}

function checkInAt(string $time, User $user): TestResponse
{
    test()->travelTo(Carbon::parse("2026-10-21 {$time}:00"));

    return test()->actingAs($user)->postJson(route('rh.attendance.check-in'), [
        'qr_payload' => app(AttendanceService::class)->generateMonthlyQrPayload()['token'],
    ]);
}

test('the punctuality score is based on the start time of the employee planning', function (string $plannedStart, string $arrival, string $status, float $score, int $delay) {
    planWednesday($this->employee, ['is_working_day' => true, 'start_time' => $plannedStart]);

    checkInAt($arrival, $this->user)->assertOk()
        ->assertJsonPath('status', $status)
        ->assertJsonPath('delay_minutes', $delay);

    $attendance = Attendance::where('employee_id', $this->employee->id)->sole();
    expect((float) $attendance->punctuality_score)->toBe($score)
        ->and($attendance->notes)->toContain("prise de poste {$plannedStart}")
        ->and($attendance->is_late)->toBe($status === 'retard');
})->with([
    'à 8h20 pour une prise de poste à 8h30 : à l\'heure' => ['08:30', '08:20', 'present', 0.33, 0],
    'à 8h20 pour une prise de poste à 8h00 : retard important' => ['08:00', '08:20', 'retard', 0.0, 20],
    'à 8h33 pour 8h30 : dans la tolérance, pas un retard' => ['08:30', '08:33', 'present', 0.33, 3],
    'à 8h40 pour 8h30 : retard léger' => ['08:30', '08:40', 'retard', 0.17, 10],
    'à 8h40 pour 8h00 : retard important' => ['08:00', '08:40', 'retard', 0.0, 40],
]);

test('without a planning the standard start time of the hr settings is used', function () {
    checkInAt('08:10', $this->user)->assertOk()
        ->assertJsonPath('status', 'retard')
        ->assertJsonPath('delay_minutes', 10);

    expect(Attendance::where('employee_id', $this->employee->id)->sole()->notes)->toContain('prise de poste 08:00');
});

test('check-in is refused on a rest day of the planning', function () {
    planWednesday($this->employee, ['is_working_day' => false, 'start_time' => null]);

    checkInAt('08:00', $this->user)->assertUnprocessable()->assertJsonPath('error', 'day_off');

    expect(Attendance::count())->toBe(0);
});

test('the attendance screen shows the expected start time of the day', function () {
    planWednesday($this->employee, ['is_working_day' => true, 'start_time' => '08:30']);
    $this->travelTo(Carbon::parse('2026-10-21 07:45:00'));

    $this->actingAs($this->user)->get(route('rh.attendance.scanner'))
        ->assertOk()
        ->assertSeeInOrder(['Prise de poste', '08:30', 'Selon votre planning']);
});
