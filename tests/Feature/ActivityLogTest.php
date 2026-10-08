<?php

use App\Models\ActivityLog;
use App\Models\Domain;
use App\Models\User;
use App\Services\ActivityLogger;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);
});

test('activity logger service records events properly', function () {
    $user = User::where('email', 'commercial@ivosphere.com')->first();
    $domain = Domain::where('code', 'PRINT')->first();

    $this->actingAs($user);

    $log = ActivityLogger::log(
        action: 'test_action',
        description: 'Action de test pour le devis DEV-2026-0099',
        domainId: $domain->id,
        properties: ['montant' => '500 000 FCFA']
    );

    expect($log)->toBeInstanceOf(ActivityLog::class);
    expect($log->user_id)->toBe($user->id);
    expect($log->domain_id)->toBe($domain->id);
    expect($log->action)->toBe('test_action');
    expect($log->properties['montant'])->toBe('500 000 FCFA');

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'test_action',
        'user_id' => $user->id,
    ]);
});

test('admin can access audit log page', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get('/admin/logs');

    $response->assertStatus(200);
    $response->assertSee("Journal d'Activité (Audit Trail)", false);
});
