<?php

use App\Models\Customer;
use App\Models\Domain;
use App\Models\Employee;
use App\Models\User;
use App\Services\PhoneNumberFormatter;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->admin = User::where('email', 'admin@ivosphere.com')->first();
});

function customerPayload(array $overrides = []): array
{
    return array_merge([
        'code' => 'CLI-'.fake()->unique()->numerify('####'),
        'type' => 'particulier',
        'name' => 'Client Test',
        'status' => 'actif',
    ], $overrides);
}

function employeePayload(array $overrides = []): array
{
    return array_merge([
        'domain_id' => Domain::first()->id,
        'employee_code' => 'EMP-'.fake()->unique()->numerify('####'),
        'first_name' => 'Awa',
        'last_name' => 'Koné',
        'email' => fake()->unique()->safeEmail(),
        'status' => 'actif',
    ], $overrides);
}

test('ivorian numbers without prefix are accepted and stored in international format', function () {
    $this->actingAs($this->admin)
        ->post(route('commercial.customers.store'), customerPayload([
            'code' => 'CLI-CI01',
            'phone' => '0708091011',
            'whatsapp' => '07 08 09 10 11',
        ]))
        ->assertSessionHasNoErrors();

    $customer = Customer::where('code', 'CLI-CI01')->first();

    expect($customer->phone)->toBe('+225 07 08 09 10 11')
        ->and($customer->whatsapp)->toBe('+225 07 08 09 10 11');
});

test('foreign numbers with their country code are accepted', function () {
    $this->actingAs($this->admin)
        ->post(route('commercial.customers.store'), customerPayload([
            'code' => 'CLI-FR01',
            'phone' => '+33 6 12 34 56 78',
        ]))
        ->assertSessionHasNoErrors();

    expect(Customer::where('code', 'CLI-FR01')->value('phone'))->toBe('+33 6 12 34 56 78');
});

test('invalid customer phone numbers are rejected', function (string $phone) {
    $this->actingAs($this->admin)
        ->post(route('commercial.customers.store'), customerPayload(['phone' => $phone]))
        ->assertSessionHasErrors('phone');

    expect(Customer::count())->toBe(0);
})->with(['123', 'téléphone', '+225 99 99 99 99 99', '+2250708']);

test('invalid employee phone numbers are rejected and valid ones are formatted', function () {
    $this->actingAs($this->admin)
        ->post(route('rh.employees.store'), employeePayload(['phone' => '12345']))
        ->assertSessionHasErrors('phone');

    $this->actingAs($this->admin)
        ->post(route('rh.employees.store'), employeePayload(['employee_code' => 'EMP-OK01', 'phone' => '2250708091011']))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('rh.employees.index'));

    expect(Employee::where('employee_code', 'EMP-OK01')->value('phone'))->toBe('+225 07 08 09 10 11');
});

test('phone formatter returns whatsapp-ready digits', function () {
    expect(PhoneNumberFormatter::digits('07 08 09 10 11'))->toBe('2250708091011')
        ->and(PhoneNumberFormatter::digits('+33 6 12 34 56 78'))->toBe('33612345678')
        ->and(PhoneNumberFormatter::format(null))->toBeNull();
});

describe('without the phone library', function () {
    beforeEach(fn () => PhoneNumberFormatter::useLibrary(false));
    afterEach(fn () => PhoneNumberFormatter::useLibrary(null));

    test('employee is created and phone formatted', function () {
        $this->actingAs($this->admin)
            ->post(route('rh.employees.store'), employeePayload(['employee_code' => 'EMP-FB01', 'phone' => '07 08 09 10 11']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('rh.employees.index'));

        expect(Employee::where('employee_code', 'EMP-FB01')->value('phone'))->toBe('+225 07 08 09 10 11')
            ->and(PhoneNumberFormatter::digits('+33 6 12 34 56 78'))->toBe('33612345678')
            ->and(PhoneNumberFormatter::format('2250708091011'))->toBe('+225 07 08 09 10 11');
    });

    test('invalid phone numbers are still rejected', function (string $phone) {
        $this->actingAs($this->admin)
            ->post(route('commercial.customers.store'), customerPayload(['phone' => $phone]))
            ->assertSessionHasErrors('phone');

        expect(Customer::count())->toBe(0);
    })->with(['123', 'téléphone', '+225 99 99 99 99 99', '+2250708']);
});
