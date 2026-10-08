<?php

use App\Models\CashRegister;
use App\Models\Domain;
use App\Models\FinancialAccount;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('finance manager can view cash registers index with batch addition section and domains', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();

    $response = $this->actingAs($finance)->get(route('finance.cash-registers.index'));

    $response->assertStatus(200);
    $response->assertSee('Gestion des Caisses');
    $response->assertSee('Ajouter Plusieurs Caisses Simultanément');
    $response->assertSee('Pré-remplir par Pôle');
    $response->assertSee('CAISSE-PRIN');
});

test('finance manager can view cash registers create page', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();

    $response = $this->actingAs($finance)->get(route('finance.cash-registers.create'));

    $response->assertStatus(200);
    $response->assertSee('Ajout de Caisses');
    $response->assertSee('Ajout Multiple (Plusieurs caisses à la fois)');
});

test('finance manager can add multiple cash registers in a single batch', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();
    $domainPrint = Domain::where('code', 'PRINT')->first();
    $domainSport = Domain::where('code', 'SPORT')->first();

    $payload = [
        'registers' => [
            [
                'name' => 'Caisse Comptoir A',
                'code' => 'CS-CPT-A',
                'domain_id' => $domainPrint->id,
                'balance' => 50000,
                'is_active' => 1,
            ],
            [
                'name' => 'Caisse Comptoir B',
                'code' => 'CS-CPT-B',
                'domain_id' => $domainSport->id,
                'balance' => 25000,
                'is_active' => 1,
            ],
            [
                'name' => 'Caisse Centrale Bis',
                'code' => 'CS-CENT-BIS',
                'domain_id' => null,
                'balance' => 0,
                'is_active' => 1,
            ],
        ],
    ];

    $response = $this->actingAs($finance)->post(route('finance.cash-registers.batch'), $payload);

    $response->assertRedirect(route('finance.cash-registers.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('cash_registers', [
        'name' => 'Caisse Comptoir A',
        'code' => 'CS-CPT-A',
        'domain_id' => $domainPrint->id,
        'balance' => 50000,
    ]);

    $this->assertDatabaseHas('cash_registers', [
        'name' => 'Caisse Comptoir B',
        'code' => 'CS-CPT-B',
        'domain_id' => $domainSport->id,
        'balance' => 25000,
    ]);

    $this->assertDatabaseHas('cash_registers', [
        'name' => 'Caisse Centrale Bis',
        'code' => 'CS-CENT-BIS',
        'domain_id' => null,
    ]);

    // Check synchronization with FinancialAccount
    $this->assertDatabaseHas('financial_accounts', [
        'code' => 'CS-CPT-A',
        'type' => 'caisse',
        'balance' => 50000,
    ]);
});

test('batch addition fails if codes are duplicate or missing', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();

    // Already exists CAISSE-PRIN
    $payload = [
        'registers' => [
            [
                'name' => 'Caisse Test Invalide',
                'code' => 'CAISSE-PRIN', // Duplicate
                'balance' => 1000,
            ],
        ],
    ];

    $response = $this->actingAs($finance)->post(route('finance.cash-registers.batch'), $payload);

    $response->assertSessionHasErrors(['registers.0.code']);
});

test('finance manager can toggle cash register active status', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();
    $caisse = CashRegister::first();
    $initialStatus = $caisse->is_active;

    $response = $this->actingAs($finance)->patch(route('finance.cash-registers.toggle', $caisse));

    $response->assertRedirect(route('finance.cash-registers.index'));
    expect($caisse->fresh()->is_active)->toBe(! $initialStatus);
});

test('finance manager can update a cash register and sync financial account', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();
    $caisse = CashRegister::create([
        'name' => 'Caisse Pour Update',
        'code' => 'CS-UPD-1',
        'balance' => 1000,
        'is_active' => true,
    ]);
    FinancialAccount::create([
        'name' => 'Caisse Pour Update',
        'code' => 'CS-UPD-1',
        'type' => 'caisse',
        'balance' => 1000,
        'is_active' => true,
    ]);

    $response = $this->actingAs($finance)->put(route('finance.cash-registers.update', $caisse), [
        'name' => 'Caisse Mise A Jour',
        'code' => 'CS-UPD-NEW',
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('finance.cash-registers.index'));
    expect($caisse->fresh()->name)->toBe('Caisse Mise A Jour');
    expect($caisse->fresh()->code)->toBe('CS-UPD-NEW');

    $this->assertDatabaseHas('financial_accounts', [
        'code' => 'CS-UPD-NEW',
        'name' => 'Caisse Mise A Jour',
    ]);
});

test('finance manager can delete an unused cash register', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();
    $caisse = CashRegister::create([
        'name' => 'Caisse A Supprimer',
        'code' => 'CS-DEL-99',
        'balance' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($finance)->delete(route('finance.cash-registers.destroy', $caisse));

    $response->assertRedirect(route('finance.cash-registers.index'));
    $this->assertDatabaseMissing('cash_registers', ['id' => $caisse->id]);
});

test('finance manager cannot delete a cash register with transactions', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();
    $caisse = CashRegister::first();

    Transaction::create([
        'reference' => 'TRX-TEST-DEL-1',
        'cash_register_id' => $caisse->id,
        'user_id' => $finance->id,
        'type' => 'entree',
        'category' => 'Test',
        'amount' => 500,
        'balance_before' => $caisse->balance,
        'balance_after' => $caisse->balance + 500,
        'date' => now()->format('Y-m-d'),
        'description' => 'Test transaction',
    ]);

    $response = $this->actingAs($finance)->delete(route('finance.cash-registers.destroy', $caisse));

    $response->assertRedirect(route('finance.cash-registers.index'));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('cash_registers', ['id' => $caisse->id]);
});
