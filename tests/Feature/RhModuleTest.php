<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\SalesTarget;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('rh manager can access rh dashboard and see metrics', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();

    $response = $this->actingAs($rh)->get('/rh');

    $response->assertStatus(200);
    $response->assertSee('Ressources Humaines');
    $response->assertSee('Total Employés');
    $response->assertSee('Objectifs des Ventes & Commissions', false);
});

test('rh manager can view employees list', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();

    $response = $this->actingAs($rh)->get('/rh/employees');

    $response->assertStatus(200);
    $response->assertSee('EMP0001');
});

test('rh manager can approve a pending leave request', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();
    $employee = Employee::first();

    $leave = LeaveRequest::create([
        'employee_id' => $employee->id,
        'type' => 'congé_annuel',
        'start_date' => now()->addDays(5)->format('Y-m-d'),
        'end_date' => now()->addDays(10)->format('Y-m-d'),
        'days' => 5,
        'reason' => 'Vacances annuelles',
        'status' => 'en_attente',
    ]);

    $response = $this->actingAs($rh)->patch("/rh/leaves/{$leave->id}/approve");

    $response->assertRedirect();
    expect($leave->fresh()->status)->toBe('approuvé');
    expect($leave->fresh()->approved_by)->toBe($rh->id);
});

test('rh manager can view sales targets listing and metrics', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();

    $response = $this->actingAs($rh)->get('/rh/sales-targets');

    $response->assertStatus(200);
    $response->assertSee('Objectifs des Ventes & Performance Commerciale', false);
    $response->assertSee('Objectifs Totaux Assignés', false);
    $response->assertSee('Chiffre d\'Affaires Réalisé', false);
    $response->assertSee('Taux Moyen d\'Atteinte', false);
});

test('rh manager can assign a new sales target to an employee', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();
    $employee = Employee::where('status', 'actif')->first();

    // 1. View create form
    $responseCreate = $this->actingAs($rh)->get('/rh/sales-targets/create');
    $responseCreate->assertStatus(200);
    $responseCreate->assertSee('Assigner un Nouvel Objectif Commercial', false);

    // 2. Store new sales target
    $responseStore = $this->actingAs($rh)->post('/rh/sales-targets', [
        'employee_id' => $employee->id,
        'title' => 'Objectif Print & Goodies Q4',
        'target_amount' => 4500000,
        'achieved_amount' => 2250000,
        'target_sales_count' => 15,
        'period' => 'trimestriel',
        'start_date' => now()->startOfQuarter()->format('Y-m-d'),
        'end_date' => now()->endOfQuarter()->format('Y-m-d'),
        'commission_rate' => 4.0,
        'bonus_amount' => 75000,
        'status' => 'en_cours',
        'notes' => 'Quota commercial spécial 4ème trimestre.',
    ]);

    $responseStore->assertRedirect('/rh/sales-targets');
    $this->assertDatabaseHas('sales_targets', [
        'employee_id' => $employee->id,
        'title' => 'Objectif Print & Goodies Q4',
        'target_amount' => 4500000,
        'achieved_amount' => 2250000,
    ]);

    $target = SalesTarget::where('title', 'Objectif Print & Goodies Q4')->first();

    // 3. View detail page
    $responseShow = $this->actingAs($rh)->get("/rh/sales-targets/{$target->id}");
    $responseShow->assertStatus(200);
    $responseShow->assertSee('Objectif Print & Goodies Q4');
    $responseShow->assertSee('4 500 000 FCFA', false);
    $responseShow->assertSee('50%', false);

    // 4. Update sales target
    $responseUpdate = $this->actingAs($rh)->put("/rh/sales-targets/{$target->id}", [
        'employee_id' => $employee->id,
        'title' => 'Objectif Print & Goodies Q4 (Révisé)',
        'target_amount' => 4500000,
        'achieved_amount' => 4500000,
        'period' => 'trimestriel',
        'start_date' => now()->startOfQuarter()->format('Y-m-d'),
        'end_date' => now()->endOfQuarter()->format('Y-m-d'),
        'commission_rate' => 4.0,
        'bonus_amount' => 75000,
        'status' => 'atteint',
    ]);

    $responseUpdate->assertRedirect('/rh/sales-targets');
    $this->assertDatabaseHas('sales_targets', [
        'id' => $target->id,
        'title' => 'Objectif Print & Goodies Q4 (Révisé)',
        'status' => 'atteint',
    ]);

    // 5. Delete sales target
    $responseDelete = $this->actingAs($rh)->delete("/rh/sales-targets/{$target->id}");
    $responseDelete->assertRedirect('/rh/sales-targets');
    $this->assertDatabaseMissing('sales_targets', [
        'id' => $target->id,
    ]);
});
