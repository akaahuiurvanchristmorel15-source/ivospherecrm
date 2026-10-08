<?php

use App\Models\Domain;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);
    $this->seed(SettingSeeder::class);
});

test('admin can view all 5 domains', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get('/admin/domains');

    $response->assertStatus(200);
    $response->assertSee('IVOSPHERE PRINT');
    $response->assertSee('IVOSPHERE SPORT');
    $response->assertSee('IVOSPHERE TECH');
    $response->assertSee('IVOSPHERE MEDIA');
    $response->assertSee('IVOSPHERE ASSURANCE');
});

test('admin can toggle domain status', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $domain = Domain::where('code', 'SPORT')->first();

    $this->actingAs($admin)->patch("/admin/domains/{$domain->id}/toggle");

    $domain->refresh();
    expect($domain->is_active)->toBeFalse();
    expect($domain->status)->toBe('inactif');
});

test('admin can update settings', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->post('/admin/settings', [
        'company_name' => 'IVOSPHERE HOLDING',
        'company_tagline' => 'Solutions Multi-Domaines',
        'company_email' => 'direction@ivosphere.com',
        'company_phone' => '+225 27 00 00 00 00',
        'company_address' => 'Abidjan Plateau',
        'currency' => 'FCFA',
        'default_tax_rate' => 18,
        'fiscal_year' => '2026',
    ]);

    $response->assertRedirect(route('admin.settings.index'));
    expect(Setting::get('company_name'))->toBe('IVOSPHERE HOLDING');
});

test('admin can configure administrative codes such as RCCM CC NIF CNPS in company identity settings', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->post('/admin/settings', [
        'company_name' => 'GROUPE IVOSPHERE SA',
        'company_tagline' => 'Excellence & Innovation Multi-Domaines',
        'company_legal_form' => 'Société Anonyme (SA)',
        'company_capital' => '50 000 000 FCFA',
        'company_email' => 'direction@ivosphere.ci',
        'company_phone' => '+225 27 22 11 22 33',
        'company_address' => 'Abidjan Cocody Angré 8e Tranche',
        'company_postal_box' => '01 BP 999 Abidjan 01',
        'company_rccm' => 'CI-ABJ-03-2026-B14-99887',
        'company_cc' => '9988776 Z',
        'company_tax_regime' => 'Régime Réel Normal (RRN)',
        'company_tax_center' => 'DGE - Direction des Grandes Entreprises',
        'company_cnps' => '889900-B',
        'company_bank_name' => 'Société Générale Côte d\'Ivoire',
        'company_bank_rib' => 'CI034 01001 999988887777 99',
        'currency' => 'FCFA',
        'default_tax_rate' => 18,
        'fiscal_year' => '2026',
    ]);

    $response->assertRedirect(route('admin.settings.index'));
    $response->assertSessionHas('success');

    expect(Setting::get('company_rccm'))->toBe('CI-ABJ-03-2026-B14-99887')
        ->and(Setting::get('company_cc'))->toBe('9988776 Z')
        ->and(Setting::get('company_legal_form'))->toBe('Société Anonyme (SA)')
        ->and(Setting::get('company_tax_regime'))->toBe('Régime Réel Normal (RRN)')
        ->and(Setting::get('company_tax_center'))->toBe('DGE - Direction des Grandes Entreprises')
        ->and(Setting::get('company_cnps'))->toBe('889900-B');

    $pageResponse = $this->actingAs($admin)->get(route('admin.settings.index'));
    $pageResponse->assertStatus(200);
    $pageResponse->assertSee('CI-ABJ-03-2026-B14-99887');
    $pageResponse->assertSee('9988776 Z');
    $pageResponse->assertSee('Code RCCM');
    $pageResponse->assertSee('Compte Contribuable');
});

test('admin can view domain creation interface on settings page', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get(route('admin.settings.index'));

    $response->assertStatus(200);
    $response->assertSee('Domaines d\'Activité', false);
    $response->assertSee('Nouveau Domaine');
    $response->assertSee('Ajout groupé');
    $response->assertSee('⚡ Suggestions de Pôles');
    $response->assertSee('IVOSPHERE PRINT');
});

test('admin can create domain from settings page and is redirected back to settings', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)
        ->from(route('admin.settings.index'))
        ->post(route('admin.domains.store'), [
            'name' => 'IVOSPHERE ENERGY',
            'code' => 'ENERGY',
            'description' => 'Pôle énergie solaire et renouvelable',
            'color' => 'amber',
            'icon' => 'building-office',
            'is_active' => 1,
        ]);

    $response->assertRedirect(route('admin.settings.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('domains', [
        'name' => 'IVOSPHERE ENERGY',
        'code' => 'ENERGY',
        'color' => 'amber',
        'status' => 'actif',
    ]);
});

test('admin can batch create domains from settings page and is redirected back to settings', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)
        ->from(route('admin.settings.index'))
        ->post(route('admin.domains.batch'), [
            'domains' => [
                [
                    'name' => 'IVOSPHERE MINING',
                    'code' => 'MINING',
                    'description' => 'Exploitation et géologie',
                    'color' => 'slate',
                    'icon' => 'briefcase',
                    'is_active' => 1,
                ],
                [
                    'name' => 'IVOSPHERE SANTE',
                    'code' => 'SANTE',
                    'description' => 'Cliniques et télémédecine',
                    'color' => 'rose',
                    'icon' => 'shield-check',
                    'is_active' => 1,
                ],
            ],
        ]);

    $response->assertRedirect(route('admin.settings.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('domains', [
        'name' => 'IVOSPHERE MINING',
        'code' => 'MINING',
    ]);
    $this->assertDatabaseHas('domains', [
        'name' => 'IVOSPHERE SANTE',
        'code' => 'SANTE',
    ]);
});
