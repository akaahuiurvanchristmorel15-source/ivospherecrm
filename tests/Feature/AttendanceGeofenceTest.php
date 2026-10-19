<?php

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\Employee;
use App\Models\User;
use App\Services\AttendanceService;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

/** Environ 1 m exprimé en degrés de latitude. */
const METER_IN_LAT_DEGREES = 1 / 111195;

beforeEach(function () {
    config([
        'attendance.geofence.default_radius_meters' => 100,
        'attendance.geofence.max_accuracy_meters' => 100,
    ]);

    $this->user = User::factory()->create(['is_active' => true]);
    $this->employee = Employee::create([
        'user_id' => $this->user->id,
        'employee_code' => 'EMP-GEO-001',
        'first_name' => 'Koffi',
        'last_name' => 'Yao',
        'email' => $this->user->email,
        'status' => 'actif',
    ]);

    $this->siege = AttendanceLocation::factory()->create([
        'name' => 'Siège Cocody',
        'latitude' => 5.3599520,
        'longitude' => -4.0082560,
    ]);

    $this->monthlyToken = app(AttendanceService::class)->generateMonthlyQrPayload()['token'];
});

/**
 * Position située à $meters mètres au nord d'un lieu.
 *
 * @return array{latitude: float, longitude: float, accuracy: float}
 */
function positionNorthOf(AttendanceLocation $location, float $meters, float $accuracy = 15): array
{
    return [
        'latitude' => (float) $location->latitude + $meters * METER_IN_LAT_DEGREES,
        'longitude' => (float) $location->longitude,
        'accuracy' => $accuracy,
    ];
}

function checkInAttendance(Employee $employee): ?Attendance
{
    return Attendance::where('employee_id', $employee->id)->first();
}

function checkInEmployee(Employee $employee): Attendance
{
    return Attendance::create([
        'employee_id' => $employee->id,
        'date' => today(),
        'check_in' => '08:00:00',
        'check_in_at' => today()->setTime(8, 0),
        'status' => 'present',
    ]);
}

test('check-in within the radius of a location is accepted and the location is recorded', function () {
    $response = $this->actingAs($this->user)->postJson(route('rh.attendance.check-in'), [
        'qr_payload' => $this->monthlyToken,
        ...positionNorthOf($this->siege, 60),
    ]);

    $response->assertOk()->assertJsonPath('success', true);

    $attendance = checkInAttendance($this->employee);
    expect($attendance->location_verified)->toBeTrue()
        ->and($attendance->check_in_location_id)->toBe($this->siege->id)
        ->and((float) $attendance->distance_meters)->toEqualWithDelta(60, 1)
        ->and((float) $attendance->accuracy_meters)->toBe(15.0);
});

test('check-in outside every location is refused with the distance to the nearest one', function () {
    $response = $this->actingAs($this->user)->postJson(route('rh.attendance.check-in'), [
        'qr_payload' => $this->monthlyToken,
        ...positionNorthOf($this->siege, 1500),
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('error', 'out_of_zone')
        ->assertJsonPath('message', 'Pointage refusé : vous êtes hors de la zone autorisée. Le lieu de pointage le plus proche (« Siège Cocody ») est à 1,5 km de vous, le rayon autorisé est de 100 m.');

    expect(checkInAttendance($this->employee))->toBeNull();
});

test('check-in is accepted near any of several locations and refusals name the nearest one', function () {
    $agence = AttendanceLocation::factory()->create([
        'name' => 'Agence Plateau',
        'latitude' => 5.3200000,
        'longitude' => -4.0200000,
    ]);

    $this->actingAs($this->user)->postJson(route('rh.attendance.check-in'), [
        'qr_payload' => $this->monthlyToken,
        ...positionNorthOf($agence, 250),
    ])->assertUnprocessable()
        ->assertJsonPath('message', 'Pointage refusé : vous êtes hors de la zone autorisée. Le lieu de pointage le plus proche (« Agence Plateau ») est à 250 m de vous, le rayon autorisé est de 100 m.');

    $this->actingAs($this->user)->postJson(route('rh.attendance.check-in'), [
        'qr_payload' => $this->monthlyToken,
        ...positionNorthOf($agence, 40),
    ])->assertOk();

    expect(checkInAttendance($this->employee)->check_in_location_id)->toBe($agence->id);
});

test('a location radius overrides the configured default radius', function () {
    config(['attendance.geofence.default_radius_meters' => 50]);
    $entrepot = AttendanceLocation::factory()->create(['name' => 'Entrepôt', 'latitude' => 5.40, 'longitude' => -3.95, 'radius_meters' => 300]);

    $this->actingAs($this->user)->postJson(route('rh.attendance.check-in'), [
        'qr_payload' => $this->monthlyToken,
        ...positionNorthOf($this->siege, 80),
    ])->assertUnprocessable()->assertJsonPath('error', 'out_of_zone');

    $this->actingAs($this->user)->postJson(route('rh.attendance.check-in'), [
        'qr_payload' => $this->monthlyToken,
        ...positionNorthOf($entrepot, 250),
    ])->assertOk();
});

test('check-in without a gps position is refused when locations are defined', function () {
    $this->actingAs($this->user)->postJson(route('rh.attendance.check-in'), [
        'qr_payload' => $this->monthlyToken,
    ])->assertUnprocessable()
        ->assertJsonPath('error', 'location_missing');

    expect(checkInAttendance($this->employee))->toBeNull();
});

test('check-in with an insufficient gps accuracy is refused even at the location', function () {
    $this->actingAs($this->user)->postJson(route('rh.attendance.check-in'), [
        'qr_payload' => $this->monthlyToken,
        ...positionNorthOf($this->siege, 0, accuracy: 250),
    ])->assertUnprocessable()
        ->assertJsonPath('error', 'location_inaccurate')
        ->assertJsonPath('message', 'Pointage refusé : la précision de votre position GPS (± 250 m) est insuffisante (± 100 m maximum). Activez la localisation précise, rapprochez-vous d\'une fenêtre puis réessayez.');

    expect(checkInAttendance($this->employee))->toBeNull();
});

test('typing the employee code does not allow checking in outside the zone', function (string $payload) {
    $this->actingAs($this->user)->postJson(route('rh.attendance.check-in'), [
        'qr_payload' => $payload,
        ...positionNorthOf($this->siege, 5000),
    ])->assertUnprocessable()->assertJsonPath('error', 'out_of_zone');

    $this->actingAs($this->user)->postJson(route('rh.attendance.check-in'), [
        'qr_payload' => $payload,
    ])->assertUnprocessable()->assertJsonPath('error', 'location_missing');

    expect(checkInAttendance($this->employee))->toBeNull();
})->with(['EMP-GEO-001', 'IVO-EMP:EMP-GEO-001']);

test('check-out outside the zone or without a position is refused', function () {
    $attendance = checkInEmployee($this->employee);

    $this->actingAs($this->user)->postJson(route('rh.attendance.check-out'))
        ->assertUnprocessable()->assertJsonPath('error', 'location_missing');

    $this->actingAs($this->user)->postJson(route('rh.attendance.check-out'), positionNorthOf($this->siege, 800))
        ->assertUnprocessable()->assertJsonPath('error', 'out_of_zone');

    expect($attendance->fresh()->isCheckedOut())->toBeFalse();
});

test('check-out within the zone is accepted and its position is recorded', function () {
    $attendance = checkInEmployee($this->employee);

    $this->actingAs($this->user)->postJson(route('rh.attendance.check-out'), positionNorthOf($this->siege, 30))
        ->assertOk()->assertJsonPath('success', true);

    $attendance->refresh();
    expect($attendance->isCheckedOut())->toBeTrue()
        ->and($attendance->check_out_location_id)->toBe($this->siege->id)
        ->and((float) $attendance->check_out_distance_meters)->toEqualWithDelta(30, 1);
});

test('without any active location the geographic restriction is not applied', function () {
    $this->siege->update(['is_active' => false]);

    $this->actingAs($this->user)->postJson(route('rh.attendance.check-in'), [
        'qr_payload' => $this->monthlyToken,
    ])->assertOk();

    $this->actingAs($this->user)->postJson(route('rh.attendance.check-out'))->assertOk();

    expect(checkInAttendance($this->employee)->location_verified)->toBeFalse();
});

describe('administration des lieux', function () {
    beforeEach(function () {
        $this->seed(DomainSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(UserSeeder::class);

        $this->admin = User::where('email', 'admin@ivosphere.com')->first();
    });

    test('hr admin can list, add, edit and delete the locations of the qr code', function () {
        $this->actingAs($this->admin)->get(route('rh.attendance.locations.index'))
            ->assertOk()
            ->assertSee('Siège Cocody')
            ->assertSee('Utiliser ma position actuelle');

        $this->actingAs($this->admin)->post(route('rh.attendance.locations.store'), [
            'name' => 'Agence Yopougon',
            'latitude' => '5.3364000',
            'longitude' => '-4.0890000',
            'radius_meters' => 150,
            'is_active' => '1',
        ])->assertRedirect(route('rh.attendance.locations.index'))->assertSessionHasNoErrors();

        $agence = AttendanceLocation::where('name', 'Agence Yopougon')->firstOrFail();
        expect($agence->radius_meters)->toBe(150)->and($agence->is_active)->toBeTrue();

        $this->actingAs($this->admin)->put(route('rh.attendance.locations.update', $agence), [
            'name' => 'Agence Yopougon Siporex',
            'latitude' => '5.3370000',
            'longitude' => '-4.0880000',
            'radius_meters' => '',
        ])->assertRedirect(route('rh.attendance.locations.index'));

        $agence->refresh();
        expect($agence->name)->toBe('Agence Yopougon Siporex')
            ->and($agence->radius_meters)->toBeNull()
            ->and($agence->effectiveRadius())->toBe(100)
            ->and($agence->is_active)->toBeFalse();

        $this->actingAs($this->admin)->delete(route('rh.attendance.locations.destroy', $agence))
            ->assertRedirect(route('rh.attendance.locations.index'));

        expect(AttendanceLocation::find($agence->id))->toBeNull();
    });

    test('a location requires a name and valid coordinates', function () {
        $this->actingAs($this->admin)->post(route('rh.attendance.locations.store'), [
            'name' => '',
            'latitude' => '95',
            'longitude' => 'abc',
        ])->assertSessionHasErrors(['name', 'latitude', 'longitude']);

        expect(AttendanceLocation::count())->toBe(1);
    });

    test('an employee without hr access cannot manage locations', function () {
        $this->actingAs($this->user)->post(route('rh.attendance.locations.store'), [
            'name' => 'Chez moi',
            'latitude' => '5.30',
            'longitude' => '-4.00',
        ])->assertForbidden();

        expect(AttendanceLocation::where('name', 'Chez moi')->exists())->toBeFalse();
    });
});
