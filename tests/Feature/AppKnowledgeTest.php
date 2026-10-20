<?php

use App\Models\Attendance;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->admin = User::where('email', 'admin@ivosphere.com')->firstOrFail();
    $this->actingAs($this->admin);
});

function askAssistant(string $query): array
{
    return test()->postJson(route('ai.query'), ['query' => $query])->assertOk()->json();
}

test('the assistant knows the company settings', function () {
    Setting::set('company_name', 'IVOSPHERE SARL');
    Setting::set('company_rccm', 'CI-ABJ-2024-B-12345');

    $answer = askAssistant("Quel est le RCCM de l'entreprise ?");

    expect($answer['intent'])->toBe('company')
        ->and($answer['table']['rows'])->toContain(['Raison sociale', 'IVOSPHERE SARL'], ['RCCM', 'CI-ABJ-2024-B-12345']);
});

test('the assistant knows who is present today', function () {
    $present = Employee::factory()->create(['first_name' => 'Awa', 'last_name' => 'Koné']);
    Employee::factory()->create(['first_name' => 'Yao', 'last_name' => 'Kouassi']);
    Attendance::create(['employee_id' => $present->id, 'date' => today(), 'check_in' => '08:05']);

    $answer = askAssistant("Qui est présent aujourd'hui ?");

    expect($answer['intent'])->toBe('attendance')
        ->and($answer['answer'])->toContain('**1 présent(s)** sur **2 employé(s) actif(s)**')
        ->and($answer['table']['rows'])->toContain(['Awa Koné', '08:05', '—'], ['Yao Kouassi', 'Absent', '—']);
});

test('the assistant lists pending quotations with a link to the page', function () {
    $customer = Customer::create(['code' => 'CLI-1', 'name' => 'Librairie Awa', 'type' => 'entreprise', 'status' => 'actif']);
    Quotation::create(['reference' => 'DEV-001', 'customer_id' => $customer->id, 'user_id' => $this->admin->id, 'date' => today(), 'valid_until' => today()->addDays(3), 'status' => 'envoye', 'total' => 45000]);

    $answer = askAssistant('Quels devis sont en attente ?');

    expect($answer['intent'])->toBe('quotations')
        ->and($answer['answer'])->toContain('**1 devis** en attente pour **45 000 FCFA**, dont **1** qui expirent')
        ->and($answer['links'][0]['url'])->toBe(route('commercial.quotations.index'));
});

test('the assistant explains the role of the user', function () {
    expect(askAssistant('Quel est mon rôle ?')['answer'])->toContain('**administrateur**');
});

test('knowledge topics follow the access rights of the user', function () {
    $technician = User::create(['name' => 'Koffi', 'email' => 'tech@ivosphere.test', 'password' => bcrypt('password'), 'is_active' => true]);
    $technician->roles()->attach(Role::where('slug', 'agent_technique')->firstOrFail());
    $this->actingAs($technician);

    expect(askAssistant("Qui est présent aujourd'hui ?")['intent'])->toBe('forbidden')
        ->and(askAssistant('Quel est le meilleur agent du cyber ?')['intent'])->toBe('forbidden')
        ->and(askAssistant('Quelle est la valeur du stock ?')['intent'])->toBe('stock_value');
});
