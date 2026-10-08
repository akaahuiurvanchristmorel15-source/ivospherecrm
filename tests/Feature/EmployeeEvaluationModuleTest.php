<?php

use App\Models\Employee;
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
    $response->assertSee('Évaluations des Collaborateurs');
    $response->assertSee('Moyenne Entreprise /30');
    $response->assertSee('Critères RH');
    $response->assertSee('Objectifs');
    $response->assertSee('Plans d\'Amélioration', false);
    $response->assertSee('Palmarès');
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
    $response->assertSee('Fiche d\'Évaluation', false);

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
        ->assertSee('Matrice des Critères d\'Évaluation', false);

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
        ->assertSee('Suivi des Objectifs Individuels');

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
        ->assertSee('Plans d\'Amélioration de la Performance', false);

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
        ->assertSee('Palmarès & Classement Interne', false);
});

test('monthly evaluation calculates exact 10-criteria average out of 20 and sets appreciation', function () {
    $rh = User::where('email', 'rh@ivosphere.com')->first();
    $employee = Employee::first();

    $evaluation = MonthlyEvaluation::create([
        'employee_id' => $employee->id,
        'year' => now()->year,
        'month' => now()->month,
        'evaluation_date' => now()->format('Y-m-d'),
        'total_working_days' => 22,
        'status' => 'en_cours',
        'workflow_step' => 'en_evaluation',
    ]);

    // Initialize 10 standard criteria
    $service = app(EvaluationService::class);
    $service->syncCriteriaForEvaluation($evaluation);

    $evaluation->load('criteriaScores.criterion');
    expect($evaluation->criteriaScores)->toHaveCount(10);

    // Apply the user's exact example:
    // Ponctualité: 18, Assiduité: 20, Travail réalisé: 16, Qualité: 17, Équipe: 15,
    // Communication: 16, Discipline: 19, Délais: 17, Objectifs: 15, Compétences: 14.
    // Total = 167 / 200, Moyenne = 16.70 / 20, Appréciation = "Très bien"
    $scoresByCode = [
        'punctuality' => ['score' => 18, 'justification' => '22 jours programmés — 21 arrivées à l\'heure — 1 retard.'],
        'attendance' => ['score' => 20, 'justification' => '22 jours travaillés sur 22 jours ouvrés prévus (100% de présence effective).'],
        'tasks' => ['score' => 16, 'justification' => '16 fiches journalières de tâches finalisées et validées avec succès sur 20 prévues.'],
        'quality' => ['score' => 17, 'justification' => 'Rigueur et excellente conformité aux standards de livraison.'],
        'teamwork' => ['score' => 15, 'justification' => 'Bon esprit collaboratif et participation constructive.'],
        'communication' => ['score' => 16, 'justification' => 'Remontée fluide des informations et échanges clairs.'],
        'discipline' => ['score' => 19, 'justification' => 'Respect exemplaire du règlement intérieur et des consignes.'],
        'deadline_respect' => ['score' => 17, 'justification' => '17 livrables complétés avant ou à la date d\'échéance.'],
        'objectives' => ['score' => 15, 'justification' => '3 objectifs atteints sur 4 assignés.'],
        'skills_acquired' => ['score' => 14, 'justification' => 'Maîtrise progressive des nouveaux outils et méthodes.'],
    ];

    foreach ($evaluation->criteriaScores as $criterionScore) {
        $code = $criterionScore->criterion->code;
        if (isset($scoresByCode[$code])) {
            $criterionScore->update([
                'score' => $scoresByCode[$code]['score'],
                'justification' => $scoresByCode[$code]['justification'],
            ]);
        }
    }

    $evaluation->recalculateScores();
    $evaluation->refresh();

    // Verify 167/200, 16.70/20, and 'Très bien'
    expect((float) $evaluation->total_score_200)->toBe(167.0);
    expect((float) $evaluation->final_score_20)->toBe(16.70);
    expect($evaluation->appreciation)->toBe('Très bien');

    // View evaluation show page and verify score and justifications are displayed
    $response = $this->actingAs($rh)->get(route('rh.evaluations.show', $evaluation));
    $response->assertStatus(200);
    $response->assertSee('16,70');
    $response->assertSee('167 / 200');
    $response->assertSee('Très bien');
    $response->assertSee('22 jours programmés — 21 arrivées à l\'heure — 1 retard.');
});

test('appreciation scale strictly matches all 7 defined brackets', function () {
    expect(MonthlyEvaluation::computeAppreciation(19.5))->toBe('Excellent');
    expect(MonthlyEvaluation::computeAppreciation(18.0))->toBe('Excellent');
    expect(MonthlyEvaluation::computeAppreciation(17.99))->toBe('Très bien');
    expect(MonthlyEvaluation::computeAppreciation(16.70))->toBe('Très bien');
    expect(MonthlyEvaluation::computeAppreciation(16.0))->toBe('Très bien');
    expect(MonthlyEvaluation::computeAppreciation(15.99))->toBe('Bien');
    expect(MonthlyEvaluation::computeAppreciation(14.0))->toBe('Bien');
    expect(MonthlyEvaluation::computeAppreciation(13.99))->toBe('Assez bien');
    expect(MonthlyEvaluation::computeAppreciation(12.0))->toBe('Assez bien');
    expect(MonthlyEvaluation::computeAppreciation(11.99))->toBe('Passable');
    expect(MonthlyEvaluation::computeAppreciation(10.0))->toBe('Passable');
    expect(MonthlyEvaluation::computeAppreciation(9.99))->toBe('Insuffisant');
    expect(MonthlyEvaluation::computeAppreciation(8.0))->toBe('Insuffisant');
    expect(MonthlyEvaluation::computeAppreciation(7.99))->toBe('Très insuffisant');
    expect(MonthlyEvaluation::computeAppreciation(3.5))->toBe('Très insuffisant');
    expect(MonthlyEvaluation::computeAppreciation(0.0))->toBe('Très insuffisant');
});
