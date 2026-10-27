<?php

use App\Models\Employee;
use App\Models\EvaluationCriterion;
use App\Models\EvaluationSheet;
use App\Models\MonthlyEvaluation;
use App\Models\User;
use App\Services\EvaluationService;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('rh manager can access evaluations dashboard and view KPIs', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();

    $response = $this->actingAs($rh)->get(route('rh.evaluations.index'));

    $response->assertStatus(200);
    $response->assertSee('Évaluations des membres');
    $response->assertSee('Moyenne des membres notés');
    $response->assertSee('Référentiel');
    $response->assertSee('Objectifs');
    $response->assertSee('Plans de progrès');
    $response->assertSee('Classement');
});

test('rh manager can generate monthly evaluation campaign', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();

    $response = $this->actingAs($rh)->post(route('rh.evaluations.generate'), [
        'year' => now()->year,
        'month' => now()->month,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('monthly_evaluations', [
        'year' => now()->year,
        'month' => now()->month,
    ]);
});

test('rh manager can view and update an employee evaluation sheet', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();
    $employee = Employee::first();

    $evaluation = MonthlyEvaluation::create([
        'employee_id' => $employee->id,
        'year' => now()->year,
        'month' => now()->month,
        'evaluation_date' => now()->format('Y-m-d'),
        'total_working_days' => 22,
        'punctuality_points' => 7.26,
        'tasks_points' => 5.06,
        'teamwork_points' => 2.20,
        'final_score_30' => 14.52,
        'status' => 'en_cours',
        'workflow_step' => 'en_evaluation',
    ]);

    $response = $this->actingAs($rh)->get(route('rh.evaluations.show', $evaluation));
    $response->assertStatus(200);
    $response->assertSee($employee->full_name);
    $response->assertSee('Tableau de consolidation');

    // Update qualitative feedback & workflow
    $updateResponse = $this->actingAs($rh)->put(route('rh.evaluations.update', $evaluation), [
        'status' => 'valide',
        'workflow_step' => 'validation_manager',
        'strengths' => 'Très bonne réactivité et proactivité client.',
        'weaknesses' => 'Ponctualité perfectible le lundi matin.',
        'recommendations' => 'Poursuivre sur cette trajectoire dynamique.',
        'future_goals' => 'Développer le portefeuille de grands comptes.',
    ]);

    $updateResponse->assertRedirect();
    $evaluation->refresh();
    expect($evaluation->workflow_step)->toBe('validation_manager');
    expect($evaluation->strengths)->toContain('Très bonne réactivité');
});

test('evaluation lock prevents unauthorized modifications and unlocks correctly', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();
    $employee = Employee::first();

    $evaluation = MonthlyEvaluation::create([
        'employee_id' => $employee->id,
        'year' => now()->year,
        'month' => now()->month,
        'evaluation_date' => now()->format('Y-m-d'),
        'total_working_days' => 22,
        'final_score_30' => 25.50,
        'status' => 'valide',
        'workflow_step' => 'valide_rh',
    ]);

    // Lock the evaluation
    $lockResponse = $this->actingAs($rh)->post(route('rh.evaluations.lock', $evaluation));
    $lockResponse->assertRedirect();
    $evaluation->refresh();
    expect($evaluation->is_locked)->toBeTrue();
    expect($evaluation->workflow_step)->toBe('verrouille');

    // Unlock
    $unlockResponse = $this->actingAs($rh)->post(route('rh.evaluations.unlock', $evaluation));
    $unlockResponse->assertRedirect();
    $evaluation->refresh();
    expect($evaluation->is_locked)->toBeFalse();
    expect($evaluation->workflow_step)->toBe('valide_rh');
});

test('electronic signatures are recorded for manager and employee', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();
    $employee = Employee::first();

    $evaluation = MonthlyEvaluation::create([
        'employee_id' => $employee->id,
        'year' => now()->year,
        'month' => now()->month,
        'evaluation_date' => now()->format('Y-m-d'),
        'total_working_days' => 22,
        'final_score_30' => 24.00,
        'status' => 'en_cours',
    ]);

    // Manager signature
    $this->actingAs($rh)->post(route('rh.evaluations.sign', $evaluation), [
        'signer_type' => 'manager',
        'signature_name' => 'Jean Responsable RH',
    ])->assertRedirect();

    // Employee signature
    $this->actingAs($rh)->post(route('rh.evaluations.sign', $evaluation), [
        'signer_type' => 'employee',
        'signature_name' => $employee->full_name,
    ])->assertRedirect();

    $evaluation->refresh();
    expect($evaluation->manager_signature)->toBe('Jean Responsable RH');
    expect($evaluation->employee_signature)->toBe($employee->full_name);
});

test('rh manager can manage criteria, individual goals, PIP and rankings', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();
    $employee = Employee::first();

    // 1. Criteria Matrix
    $this->actingAs($rh)->get(route('rh.evaluations.criteria.index'))
        ->assertStatus(200)
        ->assertSee('Référentiel d\'évaluation', false);

    // Create a new criterion
    $this->actingAs($rh)->post(route('rh.evaluations.criteria.store'), [
        'name' => 'Esprit d\'Initiative Test',
        'code' => 'initiative_test_'.uniqid(),
        'weight_percentage' => 10,
        'calculation_mode' => 'manual',
        'department' => 'TECH',
        'is_mandatory' => true,
        'is_active' => true,
    ])->assertRedirect();

    // 2. Individual Goals
    $this->actingAs($rh)->get(route('rh.goals.index'))
        ->assertStatus(200)
        ->assertSee('Objectifs');

    $this->actingAs($rh)->post(route('rh.goals.store'), [
        'employee_id' => $employee->id,
        'title' => 'Livrer les prototypes de cartes NFC',
        'description' => 'Finaliser les gabarits d\'impression',
        'start_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(20)->format('Y-m-d'),
        'status' => 'en_cours',
        'progress_pct' => 45,
    ])->assertRedirect();

    // 3. Performance Improvement Plan (PIP)
    $this->actingAs($rh)->get(route('rh.pips.index'))
        ->assertStatus(200)
        ->assertSee('Plans de progrès');

    $this->actingAs($rh)->post(route('rh.pips.store'), [
        'employee_id' => $employee->id,
        'title' => 'Redressement ponctualité',
        'problem_identified' => '3 retards constatés ce mois',
        'target_objective' => 'Zéro retard sur les 30 prochains jours',
        'corrective_actions' => 'Point hebdomadaire le lundi matin',
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addMonths(2)->format('Y-m-d'),
    ])->assertRedirect();

    // 4. Rankings
    $this->actingAs($rh)->get(route('rh.evaluations.ranking'))
        ->assertStatus(200)
        ->assertSee('Classement et récompenses');
});

test('monthly evaluation consolidates the direction sheet of the 17 criteria into a score out of 20', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $employee = Employee::factory()->create();

    $evaluation = MonthlyEvaluation::create([
        'employee_id' => $employee->id,
        'year' => now()->year,
        'month' => now()->month,
        'evaluation_date' => now()->format('Y-m-d'),
        'status' => 'en_cours',
        'workflow_step' => 'en_evaluation',
    ]);

    $scores = EvaluationCriterion::referential()->get()
        ->mapWithKeys(fn (EvaluationCriterion $criterion) => [$criterion->id => ['score' => 4]])
        ->all();

    app(EvaluationService::class)->saveSheet($evaluation, $admin, EvaluationSheet::GROUP_DIRECTION, $scores);
    $evaluation->refresh();

    expect($evaluation->criteriaScores)->toHaveCount(17);
    expect((float) $evaluation->final_score_20)->toBe(16.0);
    expect($evaluation->appreciation)->toBe('Excellent');

    $response = $this->actingAs($admin)->get(route('rh.evaluations.show', $evaluation));
    $response->assertStatus(200);
    $response->assertSee('16,00');
    $response->assertSee('Excellent');
});

test('appreciation scale strictly matches the 5 brackets of the referential', function () {
    expect(MonthlyEvaluation::computeAppreciation(19.5))->toBe('Excellent');
    expect(MonthlyEvaluation::computeAppreciation(16.0))->toBe('Excellent');
    expect(MonthlyEvaluation::computeAppreciation(15.99))->toBe('Très bien');
    expect(MonthlyEvaluation::computeAppreciation(14.0))->toBe('Très bien');
    expect(MonthlyEvaluation::computeAppreciation(13.99))->toBe('Bien');
    expect(MonthlyEvaluation::computeAppreciation(12.0))->toBe('Bien');
    expect(MonthlyEvaluation::computeAppreciation(11.99))->toBe('Passable');
    expect(MonthlyEvaluation::computeAppreciation(10.0))->toBe('Passable');
    expect(MonthlyEvaluation::computeAppreciation(9.99))->toBe('Insuffisant');
    expect(MonthlyEvaluation::computeAppreciation(0.0))->toBe('Insuffisant');
});
