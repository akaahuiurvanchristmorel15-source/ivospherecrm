<?php

use App\Models\DailyEvaluationScore;
use App\Models\Employee;
use App\Models\EvaluationCriterion;
use App\Models\EvaluationSheet;
use App\Models\MonthlyEvaluation;
use App\Models\Role;
use App\Models\User;
use App\Services\EvaluationService;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->admin = User::where('email', 'admin@ivosphere.com')->firstOrFail();
    $this->member = Employee::factory()->create(['user_id' => memberUserWithRole(null)->id]);
    $this->evaluation = MonthlyEvaluation::create([
        'employee_id' => $this->member->id,
        'year' => 2026,
        'month' => 11,
        'evaluation_date' => '2026-11-28',
        'status' => 'en_cours',
    ]);
});

function memberUserWithRole(?string $roleSlug): User
{
    $user = User::factory()->create();

    if ($roleSlug) {
        $user->roles()->attach(Role::where('slug', $roleSlug)->value('id'));
    }

    return $user;
}

function colleague(): User
{
    $user = memberUserWithRole(null);
    Employee::factory()->create(['user_id' => $user->id]);

    return $user;
}

/**
 * Note tous les critères qu'un groupe peut noter avec la même valeur, sauf ceux donnés dans $overrides (code => note|null).
 *
 * @param  array<string, int|null>  $overrides
 */
function rateAs(User $evaluator, string $group, int $score, array $overrides = []): void
{
    $scores = EvaluationCriterion::referential()->get()
        ->filter(fn (EvaluationCriterion $criterion) => $criterion->isRatableBy($group))
        ->mapWithKeys(fn (EvaluationCriterion $criterion) => [$criterion->id => [
            'score' => array_key_exists($criterion->code, $overrides) ? $overrides[$criterion->code] : $score,
            'observation' => 'Observation datée du 20 novembre 2026.',
        ]])
        ->all();

    app(EvaluationService::class)->saveSheet(test()->evaluation, $evaluator, $group, $scores);
}

function criterionScore(string $code): ?float
{
    $score = test()->evaluation->criteriaScores()
        ->where('evaluation_criterion_id', EvaluationCriterion::where('code', $code)->value('id'))
        ->value('score');

    return $score === null ? null : (float) $score;
}

test('the referential grid has 17 criteria spread over the 4 weighted blocks', function () {
    $criteria = EvaluationCriterion::referential()->get();

    expect($criteria)->toHaveCount(17)
        ->and($criteria->countBy('block')->all())->toBe(['A' => 4, 'B' => 4, 'C' => 6, 'D' => 3])
        ->and(EvaluationCriterion::where('is_active', true)->whereNull('block')->count())->toBe(0);
});

test('the month score follows the referential example of 13.6 out of 20', function () {
    $score = MonthlyEvaluation::monthScoreFromBlocks(['A' => 3.5, 'B' => 3.0, 'C' => 4.0, 'D' => 2.5]);

    expect($score)->toBe(13.6)
        ->and(MonthlyEvaluation::computeAppreciation($score))->toBe('Bien');
});

test('a member rated 3 everywhere by the direction gets 12 out of 20', function () {
    rateAs($this->admin, EvaluationSheet::GROUP_DIRECTION, 3);

    $this->evaluation->refresh();

    expect((float) $this->evaluation->final_score_20)->toBe(12.0)
        ->and($this->evaluation->appreciation)->toBe('Bien')
        ->and((float) $this->evaluation->block_c_score)->toBe(3.0);
});

test('a member rated 5 everywhere gets 20 out of 20', function () {
    rateAs($this->admin, EvaluationSheet::GROUP_DIRECTION, 5);

    expect((float) $this->evaluation->refresh()->final_score_20)->toBe(20.0)
        ->and($this->evaluation->appreciation)->toBe('Excellent');
});

test('a criterion combines the evaluator groups with their weights brought back to 100 percent', function () {
    rateAs($this->admin, EvaluationSheet::GROUP_DIRECTION, 4);
    rateAs(memberUserWithRole('responsable_rh'), EvaluationSheet::GROUP_HR, 2);
    rateAs(colleague(), EvaluationSheet::GROUP_PEER, 5);
    rateAs(colleague(), EvaluationSheet::GROUP_PEER, 3);

    // C4 : Direction 4 (40), RH 2 (20), pairs (5 + 3) / 2 = 4 (15), pas de responsable de fonction => /75
    expect(criterionScore('C4'))->toBe(3.47)
        // C2 : seule la Direction l'a noté parmi les groupes présents
        ->and(criterionScore('C2'))->toBe(4.0);
});

test('the self evaluation does not count in the month score', function () {
    rateAs($this->admin, EvaluationSheet::GROUP_DIRECTION, 3);
    rateAs($this->member->user, EvaluationSheet::GROUP_SELF, 5);

    expect((float) $this->evaluation->refresh()->final_score_20)->toBe(12.0);
});

test('a criterion marked not concerned is left out of its block', function () {
    rateAs($this->admin, EvaluationSheet::GROUP_DIRECTION, 4, ['D2' => null, 'D3' => 2]);

    $this->evaluation->refresh();

    expect(criterionScore('D2'))->toBeNull()
        ->and((float) $this->evaluation->block_d_score)->toBe(3.0);
});

test('A1 is computed from the validated daily tasks unless the direction rates it', function () {
    DailyEvaluationScore::create([
        'employee_id' => $this->member->id,
        'monthly_evaluation_id' => $this->evaluation->id,
        'date' => '2026-11-03',
        'tasks_assigned_count' => 20,
        'tasks_validated_count' => 15,
    ]);

    $this->evaluation->recalculateScores();

    expect(criterionScore('A1'))->toBe(4.0)
        ->and($this->evaluation->criteriaScores()->first()->justification)->toContain('75 %');

    rateAs($this->admin, EvaluationSheet::GROUP_DIRECTION, 2);

    expect(criterionScore('A1'))->toBe(2.0);
});

test('the daily merit converts to the A1 scale', function (float $merit, int $expected) {
    expect(MonthlyEvaluation::meritToScore($merit))->toBe($expected);
})->with([
    [85, 5], [84, 4], [70, 4], [69, 3], [55, 3], [54, 2], [40, 2], [39, 1],
]);

test('the appreciation follows the 5 brackets of the referential', function (float $score, string $appreciation) {
    expect(MonthlyEvaluation::computeAppreciation($score))->toBe($appreciation);
})->with([
    [16.0, 'Excellent'], [15.99, 'Très bien'], [14.0, 'Très bien'], [13.99, 'Bien'],
    [12.0, 'Bien'], [11.99, 'Passable'], [10.0, 'Passable'], [9.99, 'Insuffisant'],
]);

test('a peer sheet only stores the criteria peers may rate', function () {
    $peer = colleague();
    $criteria = EvaluationCriterion::referential()->get()->keyBy('code');

    $this->actingAs($peer)
        ->put(route('rh.evaluation-sheets.update', $this->evaluation), [
            'evaluator_group' => EvaluationSheet::GROUP_PEER,
            'scores' => [
                $criteria['C3']->id => ['score' => 4],
                $criteria['C4']->id => ['score' => 3],
                $criteria['A2']->id => ['score' => 4],
            ],
        ])
        ->assertRedirect(route('rh.evaluation-sheets.index', ['year' => 2026, 'month' => 11]));

    $sheet = EvaluationSheet::where('evaluator_id', $peer->id)->firstOrFail();

    expect($sheet->scores->pluck('criterion.code')->sort()->values()->all())->toBe(['C3', 'C4', 'C6', 'D1'])
        ->and($sheet->scores->firstWhere('evaluation_criterion_id', $criteria['C3']->id)->score)->toBe(4)
        ->and($sheet->scores->firstWhere('evaluation_criterion_id', $criteria['C6']->id)->score)->toBeNull();
});

test('a score of 1, 2 or 5 must be justified by an observation', function () {
    $criterion = EvaluationCriterion::where('code', 'C3')->firstOrFail();

    $this->actingAs(colleague())
        ->put(route('rh.evaluation-sheets.update', $this->evaluation), [
            'evaluator_group' => EvaluationSheet::GROUP_PEER,
            'scores' => [$criterion->id => ['score' => 5, 'observation' => '']],
        ])
        ->assertSessionHasErrors(["scores.{$criterion->id}.observation" => 'Une note de 1, 2 ou 5 doit être justifiée par une observation datée (un fait, un chiffre, un exemple).']);

    expect(EvaluationSheet::count())->toBe(0);
});

test('a member cannot rate himself as a responsible, only as self evaluation', function () {
    $this->member->user->roles()->attach(Role::where('slug', 'administrateur')->value('id'));

    $this->actingAs($this->member->user)
        ->put(route('rh.evaluation-sheets.update', $this->evaluation), ['evaluator_group' => EvaluationSheet::GROUP_DIRECTION])
        ->assertSessionHasErrors(['evaluator_group' => 'Vous ne pouvez pas évaluer ce membre au titre de ce groupe d\'évaluateurs.']);

    expect(EvaluationSheet::count())->toBe(0);
});

test('a user without any evaluator role cannot fill a sheet', function () {
    $this->actingAs(memberUserWithRole(null))
        ->put(route('rh.evaluation-sheets.update', $this->evaluation), ['evaluator_group' => EvaluationSheet::GROUP_PEER])
        ->assertForbidden();
});

test('a sheet cannot be filled once the evaluation is locked', function () {
    $this->evaluation->lock($this->admin->id);

    $this->actingAs($this->admin)
        ->put(route('rh.evaluation-sheets.update', $this->evaluation), ['evaluator_group' => EvaluationSheet::GROUP_DIRECTION])
        ->assertForbidden();
});

test('function responsibles are offered their groups and the direction is reserved to the administrator', function () {
    $groups = EvaluationSheet::groupsAvailableTo(memberUserWithRole('responsable_financiere'), $this->member);

    expect($groups)->toContain('finances', 'cyber')
        ->not->toContain(EvaluationSheet::GROUP_DIRECTION, EvaluationSheet::GROUP_HR, EvaluationSheet::GROUP_PEER);
});

test('the member can contest the locked score in writing within 48 hours', function () {
    $this->evaluation->lock($this->admin->id);

    $this->actingAs($this->member->user)
        ->post(route('rh.portal.contest-evaluation', $this->evaluation), ['contestation' => 'Le retard du 21 était justifié par écrit.'])
        ->assertRedirect();

    expect($this->evaluation->refresh()->contestation)->toBe('Le retard du 21 était justifié par écrit.')
        ->and($this->evaluation->contested_at)->not->toBeNull();
});

test('a contestation is refused after 48 hours', function () {
    $this->evaluation->lock($this->admin->id);
    $this->travel(49)->hours();

    $this->actingAs($this->member->user)
        ->post(route('rh.portal.contest-evaluation', $this->evaluation), ['contestation' => 'Contestation hors délai.'])
        ->assertForbidden();
});

test('only the evaluated member can contest the score', function () {
    $this->evaluation->lock($this->admin->id);

    $this->actingAs(colleague())
        ->post(route('rh.portal.contest-evaluation', $this->evaluation), ['contestation' => 'Je conteste pour un collègue.'])
        ->assertForbidden();
});

test('the manager answers a contestation in writing', function () {
    $this->evaluation->update(['contestation' => 'Note trop basse.', 'contested_at' => now()]);

    $this->actingAs($this->admin)
        ->post(route('rh.evaluations.answer-contestation', $this->evaluation), ['contestation_response' => 'Les observations datées confirment la note.'])
        ->assertRedirect();

    expect($this->evaluation->refresh()->contestation_response)->toBe('Les observations datées confirment la note.')
        ->and($this->evaluation->contestation_answered_by)->toBe($this->admin->id);
});

test('the member of the month needs at least 14 out of 20 and none is named in the trial month', function () {
    $service = app(EvaluationService::class);
    $this->evaluation->update(['final_score_20' => 13.9, 'appreciation' => 'Bien']);

    expect($service->memberOfTheMonth(2026, 11))->toBeNull();

    $this->evaluation->update(['final_score_20' => 14.0, 'appreciation' => 'Très bien']);
    MonthlyEvaluation::create(['employee_id' => $this->member->id, 'year' => 2026, 'month' => 10, 'is_trial' => true, 'evaluation_date' => '2026-10-28', 'final_score_20' => 18, 'appreciation' => 'Excellent']);

    expect($service->memberOfTheMonth(2026, 11)?->id)->toBe($this->evaluation->id)
        ->and($service->memberOfTheMonth(2026, 10))->toBeNull();
});

test('the quarterly reward keeps the 3 best averages of at least 12 over November to January', function () {
    $service = app(EvaluationService::class);
    $averages = [15, 13, 12.5, 11.9];

    foreach ($averages as $average) {
        $employee = Employee::factory()->create();
        foreach ([[2026, 11], [2027, 1]] as [$year, $month]) {
            MonthlyEvaluation::create(['employee_id' => $employee->id, 'year' => $year, 'month' => $month, 'evaluation_date' => "{$year}-{$month}-28", 'final_score_20' => $average, 'appreciation' => 'Bien']);
        }
    }

    $rewards = $service->quarterlyRewards(2026, 12);

    expect($service->quarterMonths(2026, 12))->toBe([['year' => 2026, 'month' => 11], ['year' => 2026, 'month' => 12], ['year' => 2027, 'month' => 1]])
        ->and($rewards['rewarded']->pluck('average')->all())->toBe([15.0, 13.0, 12.5]);
});

test('two months in a row under 10 call for a formal interview, the trial month excepted', function () {
    $previous = MonthlyEvaluation::create(['employee_id' => $this->member->id, 'year' => 2026, 'month' => 10, 'is_trial' => true, 'evaluation_date' => '2026-10-28', 'final_score_20' => 8, 'appreciation' => 'Insuffisant']);
    $this->evaluation->update(['final_score_20' => 9, 'appreciation' => 'Insuffisant']);

    expect($this->evaluation->requiresFormalInterview())->toBeFalse();

    $previous->update(['is_trial' => false]);

    expect($this->evaluation->requiresFormalInterview())->toBeTrue();
});

test('the evaluation pages render the referential', function () {
    rateAs($this->admin, EvaluationSheet::GROUP_DIRECTION, 3);

    $this->actingAs($this->admin)->get(route('rh.evaluations.show', $this->evaluation))
        ->assertOk()->assertSee('Tableau de consolidation')->assertSee('Bien');
    $this->actingAs($this->admin)->get(route('rh.evaluations.print', $this->evaluation))->assertOk();
    $this->actingAs($this->admin)->get(route('rh.evaluations.ranking', ['year' => 2026, 'month' => 11]))->assertOk();
    $this->actingAs($this->admin)->get(route('rh.evaluations.index', ['year' => 2026, 'month' => 11]))
        ->assertOk()->assertSee('Déroulé du mois')->assertSee($this->member->full_name);
    $this->actingAs($this->admin)->get(route('rh.evaluations.criteria.index'))
        ->assertOk()->assertSee('Qui note');
    $this->actingAs($this->admin)->get(route('rh.goals.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('rh.pips.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('rh.evaluations.history', $this->member))->assertOk();
    $this->actingAs($this->admin)->get(route('rh.evaluation-sheets.index', ['year' => 2026, 'month' => 11]))
        ->assertOk()->assertSee($this->member->full_name);
    $this->actingAs($this->admin)->get(route('rh.evaluation-sheets.edit', ['evaluation' => $this->evaluation, 'group' => 'direction']))
        ->assertOk()->assertSee('Atteinte des objectifs');
    $this->actingAs($this->member->user)->get(route('rh.portal.index'))->assertOk();
});
