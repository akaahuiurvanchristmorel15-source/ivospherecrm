<?php

use App\Models\ActivityLog;
use App\Models\Domain;
use App\Models\Employee;
use App\Models\EmployeeObservation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed([DomainSeeder::class, RolePermissionSeeder::class]);
});

/**
 * Compte rattaché à un rôle et, éventuellement, à des domaines.
 *
 * @param  list<Domain>  $domains
 */
function observationUser(string $roleSlug, array $domains = []): User
{
    $user = User::factory()->create(['is_active' => true]);
    $user->roles()->attach(Role::where('slug', $roleSlug)->value('id'));
    $user->domains()->attach(collect($domains)->pluck('id'));

    return $user;
}

/**
 * @return array<string, mixed>
 */
function observationPayload(array $overrides = []): array
{
    return [
        'observed_on' => now()->toDateString(),
        'nature' => 'positive',
        'category' => 'relation_client',
        'title' => 'Excellent accueil client',
        'content' => 'A su apaiser un client mécontent au comptoir.',
        ...$overrides,
    ];
}

test('a responsable records an observation on an agent and it is logged', function () {
    $responsable = observationUser('responsable_rh');
    $employee = Employee::factory()->create();

    $response = $this->actingAs($responsable)->post(route('rh.observations.store'), observationPayload([
        'employee_id' => $employee->id,
        'visible_to_employee' => '1',
    ]));

    $response->assertRedirect()->assertSessionHas('success');

    $observation = EmployeeObservation::sole();
    expect($observation->employee_id)->toBe($employee->id)
        ->and($observation->author_id)->toBe($responsable->id)
        ->and($observation->nature)->toBe('positive')
        ->and($observation->visible_to_employee)->toBeTrue();

    expect(ActivityLog::where('action', 'creation_observation_collaborateur')
        ->where('subject_id', $observation->id)
        ->where('user_id', $responsable->id)
        ->exists())->toBeTrue();
});

test('an unchecked visibility box keeps the observation confidential', function () {
    $responsable = observationUser('responsable');
    $employee = Employee::factory()->create();

    $this->actingAs($responsable)->post(route('rh.observations.store'), observationPayload(['employee_id' => $employee->id]));

    expect(EmployeeObservation::sole()->visible_to_employee)->toBeFalse();
});

test('the observations list shows observations of followed agents', function () {
    $responsable = observationUser('responsable_rh');
    $observation = EmployeeObservation::factory()->create(['title' => 'Retards répétés le lundi']);

    $this->actingAs($responsable)->get(route('rh.observations.index'))
        ->assertOk()
        ->assertSee('Retards répétés le lundi')
        ->assertSee($observation->employee->full_name);
});

test('the employee profile shows its observations to a responsable', function () {
    $admin = observationUser('administrateur');
    $observation = EmployeeObservation::factory()->create(['title' => 'Très bon esprit d\'équipe']);

    $this->actingAs($admin)->get(route('rh.employees.show', $observation->employee))
        ->assertOk()
        ->assertSee('Observations des responsables')
        ->assertSee('Très bon esprit d&#039;équipe', false);
});

test('a domain responsable only sees and observes agents of their domains', function () {
    [$commercialDomain, $otherDomain] = Domain::take(2)->get()->all();
    $responsable = observationUser('responsable_commercial', [$commercialDomain]);
    $ownAgent = EmployeeObservation::factory()->for(Employee::factory()->state(['domain_id' => $commercialDomain->id]))->create(['title' => 'Agent du domaine']);
    $otherAgent = EmployeeObservation::factory()->for(Employee::factory()->state(['domain_id' => $otherDomain->id]))->create(['title' => 'Agent hors domaine']);

    $this->actingAs($responsable)->get(route('rh.observations.index'))
        ->assertOk()
        ->assertSee('Agent du domaine')
        ->assertDontSee('Agent hors domaine');

    $this->actingAs($responsable)
        ->post(route('rh.observations.store'), observationPayload(['employee_id' => $otherAgent->employee_id]))
        ->assertForbidden();

    $this->actingAs($responsable)
        ->post(route('rh.observations.store'), observationPayload(['employee_id' => $ownAgent->employee_id]))
        ->assertRedirect();

    expect(EmployeeObservation::where('employee_id', $ownAgent->employee_id)->count())->toBe(2);
});

test('a responsable cannot observe their own employee record', function () {
    $responsable = observationUser('responsable_rh');
    $ownRecord = Employee::factory()->create(['user_id' => $responsable->id]);

    $this->actingAs($responsable)
        ->post(route('rh.observations.store'), observationPayload(['employee_id' => $ownRecord->id]))
        ->assertForbidden();

    expect(EmployeeObservation::count())->toBe(0);
});

test('a user without the observations permission is refused', function () {
    $agent = observationUser('agent_commercial_terrain');

    $this->actingAs($agent)->get(route('rh.observations.index'))->assertForbidden();
    $this->actingAs($agent)
        ->post(route('rh.observations.store'), observationPayload(['employee_id' => Employee::factory()->create()->id]))
        ->assertForbidden();
});

test('guests are redirected to the login page', function () {
    $this->get(route('rh.observations.index'))->assertRedirect(route('login'));
});

test('the author updates their observation', function () {
    $author = observationUser('responsable_rh');
    $observation = EmployeeObservation::factory()->for($author, 'author')->create();

    $this->actingAs($author)
        ->put(route('rh.observations.update', $observation), observationPayload(['nature' => 'a_ameliorer', 'title' => 'Titre corrigé']))
        ->assertRedirect();

    expect($observation->fresh())
        ->nature->toBe('a_ameliorer')
        ->title->toBe('Titre corrigé');
    expect(ActivityLog::where('action', 'maj_observation_collaborateur')->exists())->toBeTrue();
});

test('a refused update reports its errors in the observation own error bag', function () {
    $author = observationUser('responsable_rh');
    $observation = EmployeeObservation::factory()->for($author, 'author')->create(['title' => 'Original']);

    $this->actingAs($author)
        ->put(route('rh.observations.update', $observation), observationPayload(['title' => '', 'content' => '']))
        ->assertSessionHasErrors(['content' => 'Le texte de l\'observation est obligatoire.'], errorBag: 'observation'.$observation->id)
        ->assertSessionHasErrors('title', errorBag: 'observation'.$observation->id);

    expect($observation->fresh()->title)->toBe('Original');
});

test('another responsable cannot update or delete an observation they did not write', function () {
    $observation = EmployeeObservation::factory()->create(['title' => 'Original']);
    $otherResponsable = observationUser('responsable_rh');

    $this->actingAs($otherResponsable)
        ->put(route('rh.observations.update', $observation), observationPayload(['title' => 'Piraté']))
        ->assertForbidden();
    $this->actingAs($otherResponsable)
        ->delete(route('rh.observations.destroy', $observation))
        ->assertForbidden();

    expect($observation->fresh()->title)->toBe('Original');
});

test('the administrator deletes any observation and it is logged', function () {
    $admin = observationUser('administrateur');
    $observation = EmployeeObservation::factory()->create();

    $this->actingAs($admin)->delete(route('rh.observations.destroy', $observation))->assertRedirect();

    $this->assertModelMissing($observation);
    expect(ActivityLog::where('action', 'suppression_observation_collaborateur')->exists())->toBeTrue();
});

test('an empty observation is rejected on every required field', function () {
    $responsable = observationUser('responsable_rh');

    $this->actingAs($responsable)
        ->post(route('rh.observations.store'), [])
        ->assertSessionHasErrors(['employee_id', 'observed_on', 'nature', 'category', 'title', 'content']);

    expect(EmployeeObservation::count())->toBe(0);
});

test('invalid observation values are rejected', function (array $overrides, string|array $field) {
    $responsable = observationUser('responsable_rh');
    $employee = Employee::factory()->create();

    $this->actingAs($responsable)
        ->post(route('rh.observations.store'), observationPayload(['employee_id' => $employee->id, ...$overrides]))
        ->assertSessionHasErrors($field);

    expect(EmployeeObservation::count())->toBe(0);
})->with([
    'date in the future' => [fn () => ['observed_on' => now()->addDay()->toDateString()], ['observed_on' => 'La date de l\'observation ne peut pas être dans le futur.']],
    'unknown nature' => [['nature' => 'excellente'], 'nature'],
    'unknown category' => [['category' => 'salaire'], 'category'],
    'title too long' => [['title' => str_repeat('a', 256)], 'title'],
    'unknown employee' => [['employee_id' => 999999], 'employee_id'],
]);

test('an agent sees only the observations shared with them in their space', function () {
    $agentUser = observationUser('agent_commercial_terrain');
    $employee = Employee::factory()->create(['user_id' => $agentUser->id]);
    EmployeeObservation::factory()->for($employee)->visibleToEmployee()->create(['title' => 'Bravo pour le chiffre du mois']);
    EmployeeObservation::factory()->for($employee)->create(['title' => 'Note confidentielle du responsable']);

    $this->actingAs($agentUser)->get(route('rh.portal.index'))
        ->assertOk()
        ->assertSee('Bravo pour le chiffre du mois')
        ->assertDontSee('Note confidentielle du responsable');
});
