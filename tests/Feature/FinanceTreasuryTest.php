<?php

use App\Models\CashRegister;
use App\Models\CashRegisterSession;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\Invoice;
use App\Models\Revenue;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->actingAs(User::where('email', 'admin@ivosphere.com')->first());
});

/**
 * Crée une caisse par le formulaire, comme un utilisateur : la caisse et son compte de trésorerie lié.
 */
function createRegister(string $code, float $balance): CashRegister
{
    test()->post(route('finance.cash-registers.store'), ['name' => "Caisse {$code}", 'code' => $code, 'balance' => $balance, 'is_active' => 1]);

    return CashRegister::where('code', $code)->firstOrFail();
}

/**
 * Solde de la caisse et de son compte lié, qui doivent toujours être égaux.
 *
 * @return array{0: float, 1: float}
 */
function balancesOf(CashRegister $register): array
{
    return [(float) $register->fresh()->balance, (float) FinancialAccount::where('code', $register->code)->value('balance')];
}

test('a revenue credits the register and its treasury account together', function () {
    $register = createRegister('CSE-A', 10000);

    $this->post(route('finance.revenues.store'), [
        'cash_register_id' => $register->id,
        'source' => 'Vente comptoir',
        'description' => 'Recette du jour',
        'amount' => 5000,
        'date' => now()->toDateString(),
        'payment_method' => 'especes',
    ])->assertRedirect(route('finance.revenues.index'));

    expect(balancesOf($register))->toBe([15000.0, 15000.0])
        ->and(Transaction::where('cash_register_id', $register->id)->whereNotNull('financial_account_id')->count())->toBe(1);

    $this->delete(route('finance.revenues.destroy', Revenue::first()));

    expect(balancesOf($register))->toBe([10000.0, 10000.0]);
});

test('a manual withdrawal larger than the balance is refused with a clear message', function () {
    $register = createRegister('CSE-B', 3000);

    $this->from(route('finance.transactions.index'))
        ->post(route('finance.transactions.store'), ['cash_register_id' => $register->id, 'type' => 'retrait', 'amount' => 5000, 'description' => 'Achat fournitures'])
        ->assertRedirect(route('finance.transactions.index'))
        ->assertSessionHas('error', fn (string $message) => str_contains($message, 'Solde insuffisant'));

    $this->post(route('finance.transactions.store'), ['cash_register_id' => $register->id, 'type' => 'depot', 'amount' => 2000, 'description' => 'Fonds de caisse']);

    expect(balancesOf($register))->toBe([5000.0, 5000.0]);
});

test('an approved expense is taken from its own register, not another one', function () {
    $first = createRegister('CSE-C', 50000);
    $chosen = createRegister('CSE-D', 20000);

    $this->post(route('finance.expenses.store'), [
        'cash_register_id' => $chosen->id,
        'category' => 'fournitures',
        'description' => 'Papier',
        'amount' => 8000,
        'date' => now()->toDateString(),
        'payment_method' => 'especes',
    ]);

    $this->patch(route('finance.expenses.approve', Expense::first()))->assertSessionHas('success');

    expect(balancesOf($chosen))->toBe([12000.0, 12000.0])
        ->and(balancesOf($first))->toBe([50000.0, 50000.0])
        ->and(Expense::first()->status)->toBe('validee');
});

test('a customer payment credits the chosen account and its register', function () {
    $register = createRegister('CSE-E', 0);
    $customer = Customer::create(['code' => 'CLI-T', 'name' => 'Client Test', 'status' => 'actif']);
    $invoice = Invoice::create([
        'reference' => 'FAC-T-1', 'customer_id' => $customer->id, 'user_id' => auth()->id(),
        'date' => now(), 'due_date' => now(), 'status' => 'non_payee', 'total' => 30000, 'paid_amount' => 0,
    ]);

    $this->post(route('finance.payments.store'), [
        'invoice_id' => $invoice->id,
        'financial_account_id' => FinancialAccount::where('code', 'CSE-E')->value('id'),
        'amount' => 30000,
        'method' => 'especes',
        'date' => now()->toDateString(),
    ])->assertSessionHas('success');

    expect(balancesOf($register))->toBe([30000.0, 30000.0])
        ->and($invoice->fresh()->status)->toBe('payee');
});

test('a transfer moves money between two registers and keeps both balances in sync', function () {
    $from = createRegister('CSE-F', 40000);
    $to = createRegister('CSE-G', 0);

    $this->post(route('finance.transfers.store'), [
        'from_account_id' => FinancialAccount::where('code', 'CSE-F')->value('id'),
        'to_account_id' => FinancialAccount::where('code', 'CSE-G')->value('id'),
        'amount' => 15000,
        'fee' => 500,
    ])->assertSessionHas('success');

    expect(balancesOf($from))->toBe([24500.0, 24500.0])
        ->and(balancesOf($to))->toBe([15000.0, 15000.0]);
});

test('a cash session expects the opening float plus the day movements', function () {
    $register = createRegister('CSE-H', 10000);
    $accountId = FinancialAccount::where('code', 'CSE-H')->value('id');

    $this->post(route('finance.sessions.open'), ['financial_account_id' => $accountId, 'opening_balance' => 10000])->assertSessionHas('success');
    $this->post(route('finance.transactions.store'), ['cash_register_id' => $register->id, 'type' => 'depot', 'amount' => 4000, 'description' => 'Vente']);
    $this->post(route('finance.transactions.store'), ['cash_register_id' => $register->id, 'type' => 'retrait', 'amount' => 1500, 'description' => 'Taxi']);

    $this->get(route('finance.index'))->assertOk()->assertSee('Vous devriez avoir')->assertSee('12 500 FCFA');

    $session = CashRegisterSession::firstOrFail();
    $this->post(route('finance.sessions.close', $session), ['real_balance' => 12000])
        ->assertSessionHas('error', fn (string $message) => str_contains($message, 'justification'));

    $this->post(route('finance.sessions.close', $session), ['real_balance' => 12500])->assertSessionHas('success');

    expect($session->fresh()->status)->toBe('fermee')
        ->and((float) $session->fresh()->discrepancy)->toBe(0.0);
});
