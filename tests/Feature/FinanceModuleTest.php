<?php

use App\Models\AccountTransfer;
use App\Models\CashRegister;
use App\Models\CashRegisterSession;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\FinancialAuditLog;
use App\Models\FinancialPeriodClosing;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('finance manager can access finance dashboard with WMS KPIs and tabs', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();

    $response = $this->actingAs($finance)->get('/finance');

    $response->assertStatus(200);
    $response->assertSee('Finance & Trésorerie');
    $response->assertSee('Trésorerie disponible');
    $response->assertSee('Encaissements');
    $response->assertSee('Dépenses');
    $response->assertSee('Créances clients');
});

test('finance manager can view cash registers list', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();

    $response = $this->actingAs($finance)->get('/finance/cash-registers');

    $response->assertStatus(200);
    $response->assertSee('CAISSE-PRIN');
});

test('finance manager can view and approve pending expense', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();
    $caisse = CashRegister::first();

    $expense = Expense::create([
        'reference' => 'DEP-TEST-999',
        'domain_id' => $caisse->domain_id,
        'user_id' => $finance->id,
        'cash_register_id' => $caisse->id,
        'category' => 'Fournitures',
        'description' => 'Achat fournitures de bureau test',
        'amount' => 5000,
        'date' => now()->format('Y-m-d'),
        'payment_method' => 'especes',
        'status' => 'en_attente',
    ]);

    $balanceBefore = $caisse->balance;

    $response = $this->actingAs($finance)->patch("/finance/expenses/{$expense->id}/approve");

    $response->assertRedirect();
    expect($expense->fresh()->status)->toBe('validee');
    expect((float) $caisse->fresh()->balance)->toBe((float) ($balanceBefore - 5000));
});

test('inter-account transfer executes properly and updates account balances', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();
    $fromAccount = FinancialAccount::where('type', 'banque')->first();
    $toAccount = FinancialAccount::where('type', 'caisse')->first();

    $fromBefore = (float) $fromAccount->balance;
    $toBefore = (float) $toAccount->balance;

    $response = $this->actingAs($finance)->post('/finance/transfers', [
        'from_account_id' => $fromAccount->id,
        'to_account_id' => $toAccount->id,
        'amount' => 100000,
        'fee' => 0,
        'reason' => 'Test Virement de fonds',
    ]);

    $response->assertRedirect();
    expect((float) $fromAccount->fresh()->balance)->toBe($fromBefore - 100000);
    expect((float) $toAccount->fresh()->balance)->toBe($toBefore + 100000);
    expect(AccountTransfer::where('notes', 'Test Virement de fonds')->exists())->toBeTrue();
});

test('client payment triggers automated multi-table updates', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();
    $customer = Customer::first();
    $account = FinancialAccount::first();

    $invoice = Invoice::create([
        'reference' => 'FAC-TEST-AUTO',
        'customer_id' => $customer->id,
        'user_id' => $finance->id,
        'date' => now()->toDateString(),
        'due_date' => now()->addDays(15)->toDateString(),
        'total' => 500000,
        'paid_amount' => 0,
        'status' => 'non_payee',
    ]);

    $accountBefore = (float) $account->balance;

    $response = $this->actingAs($finance)->post('/finance/payments', [
        'invoice_id' => $invoice->id,
        'financial_account_id' => $account->id,
        'amount' => 200000,
        'method' => 'mobile_money',
        'date' => now()->toDateString(),
        'notes' => 'Acompte Wave',
    ]);

    $response->assertRedirect();
    $freshInvoice = $invoice->fresh();
    expect((float) $freshInvoice->paid_amount)->toBe(200000.0);
    expect($freshInvoice->status)->toBe('partielle');
    expect((float) $account->fresh()->balance)->toBe($accountBefore + 200000);
    expect(FinancialAuditLog::where('auditable_type', Payment::class)->exists())->toBeTrue();
});

test('cash session can be opened and closed with discrepancy justification', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();
    $caisse = FinancialAccount::where('type', 'caisse')->first();

    // Close any already open session on this caisse
    CashRegisterSession::where('financial_account_id', $caisse->id)
        ->where('status', 'ouverte')
        ->update(['status' => 'fermee', 'closed_at' => now(), 'real_balance' => 500000]);

    // Open session
    $openResponse = $this->actingAs($finance)->post('/finance/sessions/open', [
        'financial_account_id' => $caisse->id,
        'opening_balance' => 300000,
        'notes' => 'Session test matinale',
    ]);
    $openResponse->assertRedirect();

    $session = CashRegisterSession::where('financial_account_id', $caisse->id)
        ->where('status', 'ouverte')
        ->first();

    expect($session)->not->toBeNull();
    expect((float) $session->opening_balance)->toBe(300000.0);

    // Close session with discrepancy
    $closeResponse = $this->actingAs($finance)->post("/finance/sessions/{$session->id}/close", [
        'real_balance' => 295000,
        'discrepancy_reason' => 'Erreur monnaie -5000 FCFA',
    ]);
    $closeResponse->assertRedirect();

    $freshSession = $session->fresh();
    expect($freshSession->status)->toBe('fermee');
    expect((float) $freshSession->discrepancy)->toBe(-5000.0);
});

test('supplier debt can be created and tracked', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();
    $supplier = Supplier::first();

    $response = $this->actingAs($finance)->post('/finance/supplier-invoices', [
        'supplier_id' => $supplier->id,
        'total_amount' => 450000,
        'date' => now()->toDateString(),
        'due_date' => now()->addDays(20)->toDateString(),
        'notes' => 'Achat consommables informatiques',
    ]);

    $response->assertRedirect();
    expect(SupplierInvoice::where('supplier_id', $supplier->id)->where('total_amount', 450000)->exists())->toBeTrue();
});

test('monthly period can be closed and locked', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();

    // Remove any previous closing for month 10
    FinancialPeriodClosing::where('year', 2026)->where('month', 10)->delete();

    $response = $this->actingAs($finance)->post('/finance/closings', [
        'year' => 2026,
        'month' => 10,
        'notes' => 'Clôture mensuelle certifiée',
    ]);

    $response->assertRedirect();
    expect(FinancialPeriodClosing::where('year', 2026)->where('month', 10)->where('is_locked', true)->exists())->toBeTrue();
});

test('ai financial assistant answers natural language questions', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();

    $response = $this->actingAs($finance)->postJson('/finance/ai-query', [
        'question' => 'Quelle est notre trésorerie disponible ?',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['intent', 'answer', 'metric']);
    expect($response->json('intent'))->toBe('treasury_inquiry');
});

test('financial export downloads csv with correct headers', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();

    $response = $this->actingAs($finance)->get('/finance/export');

    $response->assertStatus(200);
    expect($response->headers->get('content-type'))->toContain('text/csv');
});
