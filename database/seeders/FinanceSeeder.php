<?php

namespace Database\Seeders;

use App\Models\AccountTransfer;
use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankReconciliationItem;
use App\Models\Budget;
use App\Models\CashRegister;
use App\Models\CashRegisterSession;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\FinancialAuditLog;
use App\Models\FinancialPeriodClosing;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $domains = Domain::all();
        if ($domains->isEmpty()) {
            return;
        }

        $user = User::where('email', 'finance@ivosphere.com')->first() ?: User::first();
        if (! $user) {
            return;
        }

        // Ensure suppliers exist
        $supplierAbc = Supplier::firstOrCreate(
            ['name' => 'ABC Fournitures'],
            ['code' => 'FOUR-ABC', 'company' => 'ABC Fournitures SARL', 'email' => 'contact@abcfournitures.ci', 'phone' => '+225 07 00 11 22', 'status' => 'actif']
        );

        $supplierXyz = Supplier::firstOrCreate(
            ['name' => 'XYZ TECH Distributeur'],
            ['code' => 'FOUR-XYZ', 'company' => 'XYZ TECH Group CI', 'email' => 'sales@xyztech.ci', 'phone' => '+225 05 33 44 55', 'status' => 'actif']
        );

        // 1. Unified Financial Accounts (Caisses, Banques, Mobile Money)
        $accountsData = [
            ['name' => 'Caisse Principale', 'code' => 'CP-01', 'type' => 'caisse', 'domain_id' => null, 'institution_name' => 'IVOSPHERE Siège', 'account_number' => 'CS-001', 'balance' => 2500000, 'initial_balance' => 2000000],
            ['name' => 'Caisse PRINT', 'code' => 'CP-PRINT', 'type' => 'caisse', 'domain_id' => $domains->firstWhere('code', 'PRINT')?->id, 'institution_name' => 'Atelier PRINT', 'account_number' => 'CS-PRT', 'balance' => 850000, 'initial_balance' => 500000],
            ['name' => 'Caisse SPORT', 'code' => 'CP-SPORT', 'type' => 'caisse', 'domain_id' => $domains->firstWhere('code', 'SPORT')?->id, 'institution_name' => 'Boutique SPORT', 'account_number' => 'CS-SPT', 'balance' => 620000, 'initial_balance' => 400000],
            ['name' => 'Caisse TECH', 'code' => 'CP-TECH', 'type' => 'caisse', 'domain_id' => $domains->firstWhere('code', 'TECH')?->id, 'institution_name' => 'Lab TECH', 'account_number' => 'CS-TCH', 'balance' => 1200000, 'initial_balance' => 800000],
            ['name' => 'Caisse MEDIA', 'code' => 'CP-MEDIA', 'type' => 'caisse', 'domain_id' => $domains->firstWhere('code', 'MEDIA')?->id, 'institution_name' => 'Studio MEDIA', 'account_number' => 'CS-MDA', 'balance' => 950000, 'initial_balance' => 600000],
            ['name' => 'Compte SGBCI Courant', 'code' => 'BNK-SGBCI', 'type' => 'banque', 'domain_id' => null, 'institution_name' => 'SGBCI', 'account_number' => 'CI059 01001 00293847501 44', 'balance' => 8200000, 'initial_balance' => 7000000],
            ['name' => 'Compte ECOBANK Business', 'code' => 'BNK-ECO', 'type' => 'banque', 'domain_id' => null, 'institution_name' => 'ECOBANK', 'account_number' => 'CI059 02002 00987654321 88', 'balance' => 4500000, 'initial_balance' => 4000000],
            ['name' => 'Orange Money Entreprise', 'code' => 'OM-01', 'type' => 'mobile_money', 'domain_id' => null, 'institution_name' => 'Orange Money', 'account_number' => '+225 07 89 00 12', 'balance' => 1150000, 'initial_balance' => 1000000],
            ['name' => 'Wave Business', 'code' => 'WAVE-01', 'type' => 'mobile_money', 'domain_id' => null, 'institution_name' => 'Wave Côte d\'Ivoire', 'account_number' => '+225 01 45 67 89', 'balance' => 750000, 'initial_balance' => 500000],
        ];

        $financialAccounts = [];
        foreach ($accountsData as $data) {
            $financialAccounts[$data['code']] = FinancialAccount::updateOrCreate(['code' => $data['code']], $data);
        }

        // Legacy Cash Registers & Bank Accounts for backwards compatibility
        $caissePrincipale = CashRegister::updateOrCreate(
            ['code' => 'CAISSE-PRIN'],
            ['name' => 'Caisse Principale', 'balance' => 2500000, 'is_active' => true]
        );

        foreach ($domains as $domain) {
            CashRegister::updateOrCreate(
                ['code' => 'CAISSE-'.$domain->code],
                [
                    'domain_id' => $domain->id,
                    'name' => 'Caisse '.$domain->code,
                    'balance' => rand(500000, 1500000),
                    'is_active' => true,
                ]
            );
        }

        BankAccount::updateOrCreate(
            ['account_number' => 'CI1234567890123456789012'],
            ['name' => 'Compte Courant SGBCI', 'bank_name' => 'SGBCI', 'balance' => 8200000, 'is_active' => true]
        );

        BankAccount::updateOrCreate(
            ['account_number' => 'CI9876543210987654321098'],
            ['name' => 'Compte Épargne ECOBANK', 'bank_name' => 'ECOBANK', 'balance' => 4500000, 'is_active' => true]
        );

        // 2. Inter-Account Transfers
        $bankAccount = $financialAccounts['BNK-SGBCI'];
        $caisseAccount = $financialAccounts['CP-01'];
        $waveAccount = $financialAccounts['WAVE-01'];

        AccountTransfer::updateOrCreate(
            ['reference' => 'TRF-2026-001'],
            [
                'from_account_id' => $bankAccount->id,
                'to_account_id' => $caisseAccount->id,
                'amount' => 500000,
                'fee' => 0,
                'user_id' => $user->id,
                'date' => Carbon::now()->subDays(3)->toDateString(),
                'status' => 'valide',
                'notes' => 'Alimentation caisse principale depuis SGBCI',
            ]
        );

        AccountTransfer::updateOrCreate(
            ['reference' => 'TRF-2026-002'],
            [
                'from_account_id' => $waveAccount->id,
                'to_account_id' => $bankAccount->id,
                'amount' => 300000,
                'fee' => 3000,
                'user_id' => $user->id,
                'date' => Carbon::now()->subDays(1)->toDateString(),
                'status' => 'valide',
                'notes' => 'Virement encaissements Wave vers compte bancaire',
            ]
        );

        // 3. Cash Register Sessions
        CashRegisterSession::updateOrCreate(
            ['reference' => 'SESS-2026-001'],
            [
                'financial_account_id' => $caisseAccount->id,
                'user_id' => $user->id,
                'opened_at' => Carbon::yesterday()->setHour(8)->setMinute(0),
                'closed_at' => Carbon::yesterday()->setHour(18)->setMinute(30),
                'opening_balance' => 500000,
                'total_inflow' => 450000,
                'total_outflow' => 20000,
                'theoretical_balance' => 930000,
                'real_balance' => 925000,
                'discrepancy' => -5000,
                'discrepancy_reason' => 'Écart monnaie rendu client en fin d\'après-midi',
                'status' => 'fermee',
                'validated_by' => $user->id,
            ]
        );

        CashRegisterSession::updateOrCreate(
            ['reference' => 'SESS-2026-002'],
            [
                'financial_account_id' => $caisseAccount->id,
                'user_id' => $user->id,
                'opened_at' => Carbon::today()->setHour(8)->setMinute(0),
                'closed_at' => null,
                'opening_balance' => 925000,
                'total_inflow' => 180000,
                'total_outflow' => 15000,
                'theoretical_balance' => 1090000,
                'real_balance' => null,
                'status' => 'ouverte',
            ]
        );

        // 4. Supplier Invoices (Dettes Fournisseurs)
        SupplierInvoice::updateOrCreate(
            ['reference' => 'FF-2026-001'],
            [
                'supplier_id' => $supplierAbc->id,
                'domain_id' => $domains->firstWhere('code', 'PRINT')?->id,
                'user_id' => $user->id,
                'date' => Carbon::now()->subDays(15),
                'due_date' => Carbon::now()->addDays(5),
                'total_amount' => 1200000,
                'paid_amount' => 400000,
                'status' => 'partielle',
                'notes' => 'Achat papier et encres offset',
            ]
        );

        SupplierInvoice::updateOrCreate(
            ['reference' => 'FF-2026-002'],
            [
                'supplier_id' => $supplierXyz->id,
                'domain_id' => $domains->firstWhere('code', 'TECH')?->id,
                'user_id' => $user->id,
                'date' => Carbon::now()->subDays(20),
                'due_date' => Carbon::now()->addDays(10),
                'total_amount' => 750000,
                'paid_amount' => 300000,
                'status' => 'partielle',
                'notes' => 'Composants serveurs et routeurs Cisco',
            ]
        );

        SupplierInvoice::updateOrCreate(
            ['reference' => 'FF-2026-003'],
            [
                'supplier_id' => $supplierAbc->id,
                'domain_id' => $domains->firstWhere('code', 'MEDIA')?->id,
                'user_id' => $user->id,
                'date' => Carbon::now()->subDays(25),
                'due_date' => Carbon::now()->subDays(2), // Overdue!
                'total_amount' => 450000,
                'paid_amount' => 0,
                'status' => 'non_payee',
                'notes' => 'Batteries et câblages studio régie',
            ]
        );

        // 5. Budgets by Domain with realistic targets & spending
        $budgetTargets = [
            'PRINT' => ['amount' => 3000000, 'spent' => 1850000],
            'SPORT' => ['amount' => 2000000, 'spent' => 1200000],
            'TECH' => ['amount' => 5000000, 'spent' => 3200000],
            'MEDIA' => ['amount' => 3000000, 'spent' => 1950000],
            'ASSURANCE' => ['amount' => 1000000, 'spent' => 450000],
        ];

        foreach ($domains as $domain) {
            $code = $domain->code;
            $target = $budgetTargets[$code] ?? ['amount' => 2500000, 'spent' => 1000000];

            Budget::updateOrCreate(
                ['domain_id' => $domain->id, 'name' => 'Budget Annuel '.$domain->name],
                [
                    'category' => 'Exploitation & Développement',
                    'amount' => $target['amount'],
                    'spent' => $target['spent'],
                    'period_start' => Carbon::now()->startOfYear(),
                    'period_end' => Carbon::now()->endOfYear(),
                    'status' => 'actif',
                    'notes' => 'Plafond budgétaire alloué au pôle '.$domain->name,
                ]
            );
        }

        // 6. Realistic Expenses with Approval Workflow
        $expenseCategories = [
            ['cat' => 'Achats', 'desc' => 'Réapprovisionnement consommables et matériel', 'amount' => 420000, 'status' => 'validee', 'lvl' => 'direction'],
            ['cat' => 'Salaires', 'desc' => 'Primes performance mensuelles équipe', 'amount' => 350000, 'status' => 'validee', 'lvl' => 'direction'],
            ['cat' => 'Loyer', 'desc' => 'Loyer locaux commerciaux et showroom', 'amount' => 850000, 'status' => 'validee', 'lvl' => 'direction'],
            ['cat' => 'Transport', 'desc' => 'Frais déplacement livraisons express', 'amount' => 45000, 'status' => 'validee', 'lvl' => 'responsable'],
            ['cat' => 'Carburant', 'desc' => 'Bons carburant flotte véhicules utilitaires', 'amount' => 120000, 'status' => 'validee', 'lvl' => 'finance'],
            ['cat' => 'Internet', 'desc' => 'Fibre optique entreprise 200 Mbps', 'amount' => 85000, 'status' => 'validee', 'lvl' => 'finance'],
            ['cat' => 'Électricité', 'desc' => 'Facture CIE siège & atelier', 'amount' => 210000, 'status' => 'validee', 'lvl' => 'finance'],
            ['cat' => 'Communication', 'desc' => 'Campagne sponsoring et réseaux sociaux', 'amount' => 180000, 'status' => 'validee', 'lvl' => 'finance'],
            ['cat' => 'Fournitures', 'desc' => 'Fournitures administratives et classeurs', 'amount' => 35000, 'status' => 'validee', 'lvl' => 'responsable'],
            ['cat' => 'Maintenance', 'desc' => 'Révision machine impression numérique', 'amount' => 145000, 'status' => 'en_attente', 'lvl' => 'finance'],
            ['cat' => 'Prestataires', 'desc' => 'Honoraires expert comptable et audit', 'amount' => 300000, 'status' => 'en_attente', 'lvl' => 'direction'],
        ];

        foreach ($expenseCategories as $i => $item) {
            $account = $financialAccounts['CP-01'];
            Expense::updateOrCreate(
                ['reference' => 'EXP-2026-'.str_pad($i + 1, 3, '0', STR_PAD_LEFT)],
                [
                    'domain_id' => $domains->random()->id,
                    'user_id' => $user->id,
                    'cash_register_id' => $caissePrincipale->id,
                    'financial_account_id' => $account->id,
                    'supplier_id' => $supplierAbc->id,
                    'category' => $item['cat'],
                    'description' => $item['desc'],
                    'amount' => $item['amount'],
                    'date' => Carbon::now()->subDays(rand(1, 20))->toDateString(),
                    'payment_method' => 'especes',
                    'status' => $item['status'],
                    'required_approval_level' => $item['lvl'],
                    'approved_by' => $item['status'] === 'validee' ? $user->id : null,
                ]
            );
        }

        // 7. Client Invoices & Partial Payments
        $customer = Customer::first();
        if ($customer) {
            $inv1 = Invoice::firstOrCreate(
                ['reference' => 'FAC-2026-0055'],
                [
                    'customer_id' => $customer->id,
                    'domain_id' => $domains->firstWhere('code', 'PRINT')?->id,
                    'user_id' => $user->id,
                    'date' => Carbon::now()->subDays(10)->toDateString(),
                    'due_date' => Carbon::now()->addDays(5)->toDateString(),
                    'total' => 1000000,
                    'paid_amount' => 300000,
                    'status' => 'partielle',
                    'notes' => 'Campagne d\'affichage et impressions grand format',
                ]
            );

            // Payment for inv1
            Payment::firstOrCreate(
                ['reference' => 'PAY-2026-0055-1'],
                [
                    'invoice_id' => $inv1->id,
                    'customer_id' => $customer->id,
                    'domain_id' => $inv1->domain_id,
                    'financial_account_id' => $financialAccounts['WAVE-01']->id,
                    'user_id' => $user->id,
                    'date' => Carbon::now()->subDays(5)->toDateString(),
                    'amount' => 300000,
                    'method' => 'mobile_money',
                    'status' => 'valide',
                    'notes' => 'Acompte 30% reçu par Wave',
                ]
            );

            // Overdue Invoice
            Invoice::firstOrCreate(
                ['reference' => 'FAC-2026-0042'],
                [
                    'customer_id' => $customer->id,
                    'domain_id' => $domains->firstWhere('code', 'TECH')?->id,
                    'user_id' => $user->id,
                    'date' => Carbon::now()->subDays(40)->toDateString(),
                    'due_date' => Carbon::now()->subDays(15)->toDateString(),
                    'total' => 1500000,
                    'paid_amount' => 500000,
                    'status' => 'partielle',
                    'notes' => 'Développement plateforme web & hébergement cloud',
                ]
            );

            Invoice::firstOrCreate(
                ['reference' => 'FAC-2026-0048'],
                [
                    'customer_id' => $customer->id,
                    'domain_id' => $domains->firstWhere('code', 'MEDIA')?->id,
                    'user_id' => $user->id,
                    'date' => Carbon::now()->subDays(35)->toDateString(),
                    'due_date' => Carbon::now()->subDays(10)->toDateString(),
                    'total' => 850000,
                    'paid_amount' => 0,
                    'status' => 'non_payee',
                    'notes' => 'Couverture photo & vidéo corporate',
                ]
            );
        }

        // 8. Bank Reconciliation
        $reconciliation = BankReconciliation::updateOrCreate(
            ['reference' => 'RAP-2026-09'],
            [
                'financial_account_id' => $financialAccounts['BNK-SGBCI']->id,
                'user_id' => $user->id,
                'statement_date' => Carbon::now()->startOfMonth()->addDays(20)->toDateString(),
                'statement_balance' => 8200000,
                'system_balance' => 8200000,
                'discrepancy' => 0,
                'status' => 'rapproche',
                'notes' => 'Rapprochement relevé bancaire SGBCI du mois',
            ]
        );

        BankReconciliationItem::updateOrCreate(
            ['reference' => 'LIG-001', 'bank_reconciliation_id' => $reconciliation->id],
            [
                'date' => Carbon::now()->subDays(10)->toDateString(),
                'description' => 'Virement client Société Générale CI',
                'amount' => 2500000,
                'type' => 'credit',
                'status' => 'matched',
            ]
        );

        BankReconciliationItem::updateOrCreate(
            ['reference' => 'LIG-002', 'bank_reconciliation_id' => $reconciliation->id],
            [
                'date' => Carbon::now()->subDays(8)->toDateString(),
                'description' => 'Prélèvement fournisseur télécoms',
                'amount' => 150000,
                'type' => 'debit',
                'status' => 'matched',
            ]
        );

        // 9. Financial Audit Log Entries
        FinancialAuditLog::create([
            'user_id' => $user->id,
            'action' => 'modification',
            'auditable_type' => 'Expense',
            'auditable_id' => 1,
            'auditable_reference' => 'DEP-0058',
            'old_values' => ['amount' => 450000],
            'new_values' => ['amount' => 400000],
            'amount_before' => 450000,
            'amount_after' => 400000,
            'reason' => 'Correction facture fournisseur ABC',
            'ip_address' => '127.0.0.1',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        // 10. Financial Period Closing (August 2026 closed)
        FinancialPeriodClosing::updateOrCreate(
            ['year' => 2026, 'month' => 8],
            [
                'period_label' => 'Août 2026',
                'closed_by' => $user->id,
                'closed_at' => Carbon::create(2026, 8, 31, 18, 0, 0),
                'is_locked' => true,
                'total_revenues' => 16400000,
                'total_expenses' => 6800000,
                'net_result' => 9600000,
                'closing_cash_balance' => 14500000,
                'notes' => 'Période clôturée et certifiée sans anomalie résiduelle.',
            ]
        );
    }
}
