<?php

use App\Models\DailyTaskSheet;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->admin = User::where('email', 'admin@ivosphere.com')->first();
});

function dailySheetFor(string $code, ?string $phone): DailyTaskSheet
{
    $employee = Employee::create([
        'employee_code' => $code,
        'first_name' => 'Agent',
        'last_name' => $code,
        'email' => strtolower($code).'@ivosphere.com',
        'phone' => $phone,
        'position' => 'Vendeur',
        'department' => 'Commercial',
        'status' => 'actif',
    ]);

    return DailyTaskSheet::create([
        'reference' => 'TCH-'.$code,
        'employee_id' => $employee->id,
        'date' => today(),
        'position' => 'Vendeur',
        'tasks' => [['title' => 'Ranger la boutique', 'status' => 'assigne']],
        'status' => 'assigne',
    ]);
}

test('daily tasks page lists today sheets with a phone in the WhatsApp batch', function () {
    dailySheetFor('EMP-WA1', '+225 07 00 00 00 01');
    dailySheetFor('EMP-NOPHONE', null);

    $response = $this->actingAs($this->admin)->get(route('rh.daily-tasks.index'));

    $response->assertOk()
        ->assertSee('Envoyer à tous')
        ->assertViewHas('sheetsWithoutPhoneCount', 1)
        ->assertViewHas('whatsappQueue', fn ($queue) => $queue->count() === 1
            && str_starts_with($queue->first()['url'], 'https://wa.me/2250700000001?text=')
            && $queue->first()['sent'] === false);
});

test('opening a WhatsApp link from the batch marks the sheet as sent', function () {
    $sheet = dailySheetFor('EMP-WA2', '+225 07 00 00 00 02');

    $this->actingAs($this->admin)
        ->postJson(route('rh.daily-tasks.whatsapp-sent', $sheet))
        ->assertOk()
        ->assertJson(['sent' => true]);

    expect($sheet->fresh()->whatsapp_sent_at)->not->toBeNull()
        ->and($sheet->fresh()->whatsapp_status)->toBe('envoye');
});

test('printable daily task sheet shows the employee, tasks and signatures', function () {
    $sheet = dailySheetFor('EMP-PRINT', '+225 07 00 00 00 03');

    $this->actingAs($this->admin)
        ->get(route('rh.daily-tasks.print', $sheet))
        ->assertOk()
        ->assertSee('Fiche de tâches du jour')
        ->assertSee('TCH-EMP-PRINT')
        ->assertSee('Agent EMP-PRINT')
        ->assertSee('Ranger la boutique')
        ->assertSee('Le superviseur');
});
