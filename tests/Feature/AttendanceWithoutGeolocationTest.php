<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\HrSetting;
use App\Models\User;
use App\Services\AttendanceService;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->admin = User::where('email', 'admin@ivosphere.com')->first();
});

test('qr check-in succeeds without any gps coordinates even if geofencing was previously enabled', function () {
    HrSetting::current()->update(['geofence_enabled' => true]);

    $employee = Employee::create([
        'user_id' => $this->admin->id,
        'employee_code' => 'EMP-NOGEO',
        'first_name' => 'Awa',
        'last_name' => 'Koné',
        'email' => 'awa.nogeo@ivosphere.com',
        'status' => 'actif',
    ]);

    $token = app(AttendanceService::class)->generateMonthlyQrPayload()['token'];

    $response = $this->actingAs($this->admin)->postJson(route('rh.attendance.check-in'), [
        'qr_payload' => $token,
    ]);

    $attendance = Attendance::where('employee_id', $employee->id)->first();

    if ($response->json('error') === 'day_off') {
        $this->markTestSkipped('Le planning du jour est un jour de repos.');
    }

    $response->assertOk()->assertJsonPath('success', true);
    expect($attendance)->not->toBeNull()
        ->and($attendance->latitude)->toBeNull()
        ->and($attendance->longitude)->toBeNull()
        ->and($attendance->location_verified)->toBeFalse();
});

test('rh settings can be saved without geolocation fields and geofencing stays disabled', function () {
    HrSetting::current()->update(['geofence_enabled' => true]);
    $settings = HrSetting::current();

    $this->actingAs($this->admin)->put(route('rh.settings.update'), [
        'work_start_time' => '08:00',
        'work_end_time' => '17:00',
        'auto_checkout_enabled' => '1',
        'auto_checkout_time' => '20:00',
        'max_work_days_per_week' => 5,
        'allow_custom_schedules' => '1',
        'evaluation_day_of_month' => 5,
        'weight_punctuality' => $settings->weight_punctuality,
        'weight_tasks' => $settings->weight_tasks,
        'weight_teamwork' => $settings->weight_teamwork,
        'max_evaluation_score' => 30,
        'punctuality_grace_minutes' => 5,
        'late_threshold_minutes' => 15,
    ])->assertRedirect(route('rh.settings.index'))
        ->assertSessionHasNoErrors();

    expect(HrSetting::current()->fresh()->geofence_enabled)->toBeFalse();
});

test('rh settings page no longer exposes geolocation options', function () {
    $this->actingAs($this->admin)->get(route('rh.settings.index'))
        ->assertOk()
        ->assertSee('Pointage par QR code')
        ->assertDontSee('office_latitude')
        ->assertDontSee('geofence_radius_meters');
});
