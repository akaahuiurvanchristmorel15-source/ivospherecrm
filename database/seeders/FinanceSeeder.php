<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\CashRegister;
use App\Models\Domain;
use App\Models\FinancialAccount;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $domains = Domain::all();
        if ($domains->isEmpty()) {
            return;
        }

        // 1. Unified Financial Accounts (Caisses, Banques, Mobile Money) initialized with 0 balance
        $accountsData = [
            ['name' => 'Caisse Principale', 'code' => 'CP-01', 'type' => 'caisse', 'domain_id' => null, 'institution_name' => 'IVOSPHERE Siège', 'account_number' => 'CS-001', 'balance' => 0, 'initial_balance' => 0],
            ['name' => 'Caisse PRINT', 'code' => 'CP-PRINT', 'type' => 'caisse', 'domain_id' => $domains->firstWhere('code', 'PRINT')?->id, 'institution_name' => 'Atelier PRINT', 'account_number' => 'CS-PRT', 'balance' => 0, 'initial_balance' => 0],
            ['name' => 'Caisse SPORT', 'code' => 'CP-SPORT', 'type' => 'caisse', 'domain_id' => $domains->firstWhere('code', 'SPORT')?->id, 'institution_name' => 'Boutique SPORT', 'account_number' => 'CS-SPT', 'balance' => 0, 'initial_balance' => 0],
            ['name' => 'Caisse TECH', 'code' => 'CP-TECH', 'type' => 'caisse', 'domain_id' => $domains->firstWhere('code', 'TECH')?->id, 'institution_name' => 'Lab TECH', 'account_number' => 'CS-TCH', 'balance' => 0, 'initial_balance' => 0],
            ['name' => 'Caisse MEDIA', 'code' => 'CP-MEDIA', 'type' => 'caisse', 'domain_id' => $domains->firstWhere('code', 'MEDIA')?->id, 'institution_name' => 'Studio MEDIA', 'account_number' => 'CS-MDA', 'balance' => 0, 'initial_balance' => 0],
            ['name' => 'Compte SGBCI Courant', 'code' => 'BNK-SGBCI', 'type' => 'banque', 'domain_id' => null, 'institution_name' => 'SGBCI', 'account_number' => 'CI059 01001 00293847501 44', 'balance' => 0, 'initial_balance' => 0],
            ['name' => 'Compte ECOBANK Business', 'code' => 'BNK-ECO', 'type' => 'banque', 'domain_id' => null, 'institution_name' => 'ECOBANK', 'account_number' => 'CI059 02002 00987654321 88', 'balance' => 0, 'initial_balance' => 0],
            ['name' => 'Orange Money Entreprise', 'code' => 'OM-01', 'type' => 'mobile_money', 'domain_id' => null, 'institution_name' => 'Orange Money', 'account_number' => '+225 07 89 00 12', 'balance' => 0, 'initial_balance' => 0],
            ['name' => 'Wave Business', 'code' => 'WAVE-01', 'type' => 'mobile_money', 'domain_id' => null, 'institution_name' => 'Wave Côte d\'Ivoire', 'account_number' => '+225 01 45 67 89', 'balance' => 0, 'initial_balance' => 0],
        ];

        foreach ($accountsData as $data) {
            FinancialAccount::updateOrCreate(['code' => $data['code']], $data);
        }

        // Legacy Cash Registers & Bank Accounts with 0 balance
        CashRegister::updateOrCreate(
            ['code' => 'CAISSE-PRIN'],
            ['name' => 'Caisse Principale', 'balance' => 0, 'is_active' => true]
        );

        foreach ($domains as $domain) {
            CashRegister::updateOrCreate(
                ['code' => 'CAISSE-'.$domain->code],
                [
                    'domain_id' => $domain->id,
                    'name' => 'Caisse '.$domain->code,
                    'balance' => 0,
                    'is_active' => true,
                ]
            );
        }

        BankAccount::updateOrCreate(
            ['account_number' => 'CI1234567890123456789012'],
            ['name' => 'Compte Courant SGBCI', 'bank_name' => 'SGBCI', 'balance' => 0, 'is_active' => true]
        );

        BankAccount::updateOrCreate(
            ['account_number' => 'CI9876543210987654321098'],
            ['name' => 'Compte Épargne ECOBANK', 'bank_name' => 'ECOBANK', 'balance' => 0, 'is_active' => true]
        );
    }
}
