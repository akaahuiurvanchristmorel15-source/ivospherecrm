<?php

namespace App\Services;

use App\Models\CashRegister;
use App\Models\FinancialAccount;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Point d'entrée unique pour faire bouger l'argent.
 *
 * Une caisse existe à la fois comme « caisse » (cash_registers) et comme « compte de trésorerie »
 * (financial_accounts, même code). Chaque mouvement met à jour les deux soldes et crée une seule
 * transaction rattachée aux deux, pour que tous les écrans affichent le même montant.
 */
class TreasuryService
{
    /**
     * Types de transaction qui ajoutent de l'argent.
     *
     * @var list<string>
     */
    public const CREDIT_TYPES = ['encaissement', 'depot'];

    /**
     * Retrouve la caisse et le compte de trésorerie liés (même code).
     *
     * @return array{0: ?CashRegister, 1: ?FinancialAccount}
     */
    public static function pair(CashRegister|FinancialAccount $target): array
    {
        if ($target instanceof CashRegister) {
            return [$target, FinancialAccount::where('code', $target->code)->first()];
        }

        return [CashRegister::where('code', $target->code)->first(), $target];
    }

    /**
     * Ajoute (encaissement, dépôt) ou retire (retrait) de l'argent sur une caisse ou un compte.
     *
     * @param  array{description: string, user_id?: int|null, domain_id?: int|null, date?: mixed, reference?: string|null}  $details
     *
     * @throws Exception si le solde est insuffisant pour un retrait (sauf $allowNegative)
     */
    public static function move(CashRegister|FinancialAccount $target, string $type, float $amount, array $details, bool $allowNegative = false): Transaction
    {
        if ($amount <= 0) {
            throw new Exception('Le montant doit être supérieur à zéro.');
        }

        return DB::transaction(function () use ($target, $type, $amount, $details, $allowNegative) {
            [$register, $account] = self::pair($target);
            $register = $register ? CashRegister::lockForUpdate()->find($register->id) : null;
            $account = $account ? FinancialAccount::lockForUpdate()->find($account->id) : null;

            // Le solde affiché à l'utilisateur est celui de l'élément qu'il a choisi
            $reference = $target instanceof CashRegister ? $register : $account;
            $balanceBefore = (float) $reference->balance;
            $delta = in_array($type, self::CREDIT_TYPES, true) ? $amount : -$amount;

            if ($delta < 0 && ! $allowNegative && $balanceBefore + $delta < -0.001) {
                throw new Exception('Solde insuffisant sur « '.$reference->name.' » : '.self::money($balanceBefore).' disponible(s), '.self::money($amount).' demandé(s).');
            }

            $register?->update(['balance' => (float) $register->balance + $delta]);
            $account?->update(['balance' => (float) $account->balance + $delta]);

            return Transaction::create([
                'cash_register_id' => $register?->id,
                'financial_account_id' => $account?->id,
                'domain_id' => $details['domain_id'] ?? $register?->domain_id ?? $account?->domain_id,
                'user_id' => $details['user_id'] ?? auth()->id(),
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore + $delta,
                'description' => $details['description'],
                'reference' => $details['reference'] ?? 'TRX-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'date' => $details['date'] ?? now()->toDateString(),
            ]);
        });
    }

    private static function money(float $amount): string
    {
        return number_format($amount, 0, ',', ' ').' FCFA';
    }
}
