<?php

use App\Models\Contract;
use App\Models\Document;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('user can create, view and calculate remaining days on a contract', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $endDate = Carbon::now()->addDays(20);

    $response = $this->actingAs($user)->post('/contracts', [
        'reference' => 'CTR-2026-TEST',
        'name' => 'Contrat Prestation Informatique',
        'type' => 'client',
        'party_name' => 'Client Test SA',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => $endDate->format('Y-m-d'),
        'amount' => 5000000,
        'currency' => 'FCFA',
        'status' => 'actif',
    ]);

    $contract = Contract::where('reference', 'CTR-2026-TEST')->first();
    expect($contract)->not->toBeNull();
    expect($contract->isExpiringSoon())->toBeTrue();
    expect($contract->days_remaining)->toBe(20);

    $showResponse = $this->actingAs($user)->get("/contracts/{$contract->id}");
    $showResponse->assertStatus(200);
    $showResponse->assertSee('CTR-2026-TEST');
});

test('user can explore GED folders, archive a document and access the vault', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    // GED Explorer
    $gedResponse = $this->actingAs($user)->get('/ged');
    $gedResponse->assertStatus(200);
    $gedResponse->assertSee('GED — Gestion Documentaire');

    // Archive standard document
    $docResponse = $this->actingAs($user)->post('/ged/documents', [
        'title' => 'Plaquette Commerciale 2026',
        'tags' => 'marketing, print, 2026',
    ]);
    $docResponse->assertRedirect('/ged');

    $doc = Document::where('title', 'Plaquette Commerciale 2026')->first();
    expect($doc)->not->toBeNull();
    expect($doc->is_vault)->toBeFalse();

    // Digital Vault
    $vaultResponse = $this->actingAs($user)->get('/ged/vault');
    $vaultResponse->assertStatus(200);
    $vaultResponse->assertSee('Coffre-Fort Numérique IVOSPHERE');

    // Archive vault document
    $vaultDocResponse = $this->actingAs($user)->post('/ged/documents', [
        'title' => 'Bilan Fiscal 2025 Signé',
        'is_vault' => 1,
    ]);
    $vaultDocResponse->assertRedirect('/ged/vault');

    $vaultDoc = Document::where('title', 'Bilan Fiscal 2025 Signé')->first();
    expect($vaultDoc->is_vault)->toBeTrue();
});
