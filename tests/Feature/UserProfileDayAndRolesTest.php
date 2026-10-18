<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->admin = User::where('email', 'admin@ivosphere.com')->firstOrFail();
    $this->agent = User::create(['name' => 'Awa Koné', 'email' => 'awa@ivosphere.ci', 'password' => 'MotDePasse!2026', 'is_active' => true]);
    $this->agent->roles()->sync([Role::where('slug', 'agent_caissier_vendeur')->value('id')]);
});

test('a user adds, replaces and removes their profile photo', function () {
    Storage::fake('public');

    $this->actingAs($this->agent)
        ->post(route('profile.photo.update'), ['avatar' => UploadedFile::fake()->image('moi.jpg', 300, 300)])
        ->assertRedirect(route('password.edit'));

    $first = $this->agent->fresh()->avatar;
    Storage::disk('public')->assertExists($first);
    $this->actingAs($this->agent->fresh())->get(route('password.edit'))->assertOk()->assertSee(Storage::disk('public')->url($first), false);

    $this->actingAs($this->agent)->post(route('profile.photo.update'), ['avatar' => UploadedFile::fake()->image('nouvelle.png')]);
    Storage::disk('public')->assertMissing($first);

    $this->actingAs($this->agent)->delete(route('profile.photo.destroy'))->assertRedirect(route('password.edit'));
    expect($this->agent->fresh()->avatar)->toBeNull();
});

test('only images are accepted as profile photo', function () {
    Storage::fake('public');

    $this->actingAs($this->agent)
        ->post(route('profile.photo.update'), ['avatar' => UploadedFile::fake()->create('document.pdf', 20, 'application/pdf')])
        ->assertSessionHasErrors('avatar');

    expect($this->agent->fresh()->avatar)->toBeNull();
});

test('each user sees their own day and the administrator can open anyone\'s day', function () {
    $this->actingAs($this->agent);
    ActivityLogger::log('test_journee', 'A encaissé la vente du matin');
    $expense = ['reference' => 'DEP-JOUR-1', 'user_id' => $this->agent->id, 'category' => 'Fournitures', 'description' => 'Papier', 'date' => today()->toDateString(), 'created_at' => now(), 'updated_at' => now()];
    if (Schema::hasColumn('expenses', 'amount')) {
        $expense['amount'] = 5000;
    }
    DB::table('expenses')->insert($expense);

    $this->get(route('my-day'))
        ->assertOk()
        ->assertSee('Ma journée')
        ->assertSee('A encaissé la vente du matin')
        ->assertSee('5 000 F');

    $this->get(route('my-day', ['date' => today()->subDay()->toDateString()]))
        ->assertOk()
        ->assertDontSee('A encaissé la vente du matin');

    $this->get(route('my-day', ['date' => today()->addDay()->toDateString()]))->assertSessionHasErrors('date');
    $this->get(route('admin.users.day', $this->admin))->assertForbidden();

    $this->actingAs($this->admin)
        ->get(route('admin.users.day', $this->agent))
        ->assertOk()
        ->assertSee('Journée de')
        ->assertSee('Awa Koné')
        ->assertSee('A encaissé la vente du matin');

    $this->actingAs($this->admin)->get(route('admin.users.index'))->assertOk()->assertSee(route('admin.users.day', $this->agent), false);
});

test('the administrator creates a role from an existing one and deletes it when unused', function () {
    $model = Role::where('slug', 'agent_caissier_vendeur')->firstOrFail();

    $this->actingAs($this->admin)
        ->post(route('admin.roles.store'), ['name' => 'Agent de livraison', 'description' => 'Livre les commandes', 'copy_from' => $model->id])
        ->assertSessionHas('success');

    $role = Role::where('name', 'Agent de livraison')->firstOrFail();
    expect($role->slug)->toBe('agent_de_livraison')
        ->and($role->permissions()->pluck('permissions.id')->sort()->values()->all())->toBe($model->permissions()->pluck('permissions.id')->sort()->values()->all());

    $this->actingAs($this->admin)->post(route('admin.roles.store'), ['name' => 'Agent de livraison'])->assertSessionHasErrors('name');

    $this->actingAs($this->admin)->get(route('admin.roles.index', ['role' => $role->id]))->assertOk()->assertSee('Supprimer ce rôle');

    $this->agent->roles()->sync([$role->id]);
    $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $role))->assertSessionHas('error');

    $this->agent->roles()->sync([$model->id]);
    $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $role))->assertSessionHas('success');
    expect(Role::find($role->id))->toBeNull();

    $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $model))->assertSessionHas('error');
    expect(Role::find($model->id))->not->toBeNull();
});

test('only administrators can create roles', function () {
    $this->actingAs($this->agent)->post(route('admin.roles.store'), ['name' => 'Pirate'])->assertForbidden();

    expect(Role::where('name', 'Pirate')->exists())->toBeFalse()
        ->and(Permission::count())->toBeGreaterThan(0);
});
