<?php

namespace App\Services;

use App\Models\AccountTransfer;
use App\Models\CashRegister;
use App\Models\CashRegisterSession;
use App\Models\Domain;
use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\FinancialAuditLog;
use App\Models\FinancialPeriodClosing;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceService
{
    /**
     * Determine required approval level for an expense based on amount thresholds.
     */
    public static function getRequiredApprovalLevel(float $amount): string
    {
        if ($amount < 50000) {
            return 'responsable';
        }

        if ($amount <= 250000) {
            return 'finance';
        }

        return 'direction';
    }

    /**
     * Check if a specific period (year, month) is locked.
     */
    public static function isPeriodLocked(Carbon|string $date): bool
    {
        $d = is_string($date) ? Carbon::parse($date) : $date;

        return FinancialPeriodClosing::where('year', $d->year)
            ->where('month', $d->month)
            ->where('is_locked', true)
            ->exists();
    }

    /**
     * Log a financial audit entry.
     */
    public static function logAudit(
        int $userId,
        string $action,
        string $auditableType,
        int $auditableId,
        ?string $reference = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?float $amountBefore = null,
        ?float $amountAfter = null,
        ?string $reason = null
    ): FinancialAuditLog {
        return FinancialAuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'auditable_reference' => $reference,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'amount_before' => $amountBefore,
            'amount_after' => $amountAfter,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    /**
     * Enregistrer un paiement client avec mises à jour automatiques multiples :
     * 1. Facture (solde payé, reste, statut)
     * 2. Compte financier / Caisse (solde crédité)
     * 3. Transaction de trésorerie
     * 4. Enregistrement Recette (Revenue)
     * 5. Journal d'audit financier
     */
    public static function recordPayment(array $data, User $user): Payment
    {
        return DB::transaction(function () use ($data, $user) {
            $invoice = Invoice::findOrFail($data['invoice_id']);

            if (self::isPeriodLocked($data['date'] ?? now())) {
                throw new Exception('Impossible d\'enregistrer une opération sur une période clôturée.');
            }

            $amount = (float) $data['amount'];
            if ($amount <= 0) {
                throw new Exception('Le montant du paiement doit être supérieur à zéro.');
            }

            $remaining = max(0, (float) $invoice->total - (float) $invoice->paid_amount);
            if ($amount > ($remaining + 0.01)) {
                throw new Exception("Le montant ({$amount} FCFA) dépasse le reste à payer ({$remaining} FCFA).");
            }

            // Determine financial account
            $accountId = $data['financial_account_id'] ?? null;
            $account = null;
            if ($accountId) {
                $account = FinancialAccount::find($accountId);
            }

            if (! $account) {
                // Fallback to domain or principal cash account
                $account = FinancialAccount::where('type', 'caisse')->first();
            }

            $ref = 'PAY-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));

            $payment = Payment::create([
                'reference' => $ref,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'domain_id' => $invoice->domain_id,
                'financial_account_id' => $account?->id,
                'user_id' => $user->id,
                'date' => $data['date'] ?? now()->toDateString(),
                'amount' => $amount,
                'method' => $data['method'] ?? 'especes',
                'status' => 'valide',
                'notes' => $data['notes'] ?? "Paiement pour facture {$invoice->reference}",
            ]);

            // Update Invoice
            $oldPaid = (float) $invoice->paid_amount;
            $newPaid = $oldPaid + $amount;
            $invoice->paid_amount = $newPaid;
            $invoice->status = $newPaid >= (float) $invoice->total ? 'payee' : 'partielle';
            $invoice->save();

            // Credit Financial Account
            if ($account) {
                $balanceBefore = (float) $account->balance;
                $balanceAfter = $balanceBefore + $amount;
                $account->increment('balance', $amount);

                // Create Transaction
                Transaction::create([
                    'financial_account_id' => $account->id,
                    'cash_register_id' => CashRegister::first()?->id,
                    'domain_id' => $invoice->domain_id,
                    'user_id' => $user->id,
                    'type' => 'encaissement',
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'description' => "Encaissement paiement {$payment->reference} (Facture {$invoice->reference})",
                    'reference' => 'TRX-'.time().'-'.rand(10, 99),
                    'date' => $payment->date,
                ]);
            }

            // Sync Revenue
            Revenue::create([
                'reference' => 'REC-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'domain_id' => $invoice->domain_id,
                'user_id' => $user->id,
                'cash_register_id' => CashRegister::first()?->id,
                'financial_account_id' => $account?->id,
                'payment_id' => $payment->id,
                'source' => 'Paiement Facture Client',
                'description' => "Règlement {$invoice->reference} par client {$invoice->customer?->company_name}",
                'amount' => $amount,
                'date' => $payment->date,
                'payment_method' => $payment->method,
                'notes' => $payment->notes,
            ]);

            // Consign Audit Log
            self::logAudit(
                userId: $user->id,
                action: 'paiement',
                auditableType: Payment::class,
                auditableId: $payment->id,
                reference: $payment->reference,
                amountBefore: $oldPaid,
                amountAfter: $newPaid,
                reason: "Enregistrement paiement facture {$invoice->reference}"
            );

            return $payment;
        });
    }

    /**
     * Transfer funds between two financial accounts (Bank, Cash, Mobile Money).
     */
    public static function transferFunds(
        int $fromAccountId,
        int $toAccountId,
        float $amount,
        float $fee,
        User $user,
        ?string $reason = null
    ): AccountTransfer {
        return DB::transaction(function () use ($fromAccountId, $toAccountId, $amount, $fee, $user, $reason) {
            if ($fromAccountId === $toAccountId) {
                throw new Exception('Le compte source et le compte destination doivent être différents.');
            }

            if ($amount <= 0) {
                throw new Exception('Le montant du transfert doit être supérieur à zéro.');
            }

            $from = FinancialAccount::lockForUpdate()->findOrFail($fromAccountId);
            $to = FinancialAccount::lockForUpdate()->findOrFail($toAccountId);

            $totalDebit = $amount + $fee;
            if ($from->balance < $totalDebit) {
                throw new Exception("Solde insuffisant sur le compte '{$from->name}' ({$from->balance} FCFA disponibles, requis: {$totalDebit} FCFA).");
            }

            $ref = 'TRF-'.now()->format('Y').'-'.str_pad((string) (AccountTransfer::count() + 1), 4, '0', STR_PAD_LEFT);

            // Deduct from source
            $fromBefore = (float) $from->balance;
            $fromAfter = $fromBefore - $totalDebit;
            $from->decrement('balance', $totalDebit);

            // Add to destination
            $toBefore = (float) $to->balance;
            $toAfter = $toBefore + $amount;
            $to->increment('balance', $amount);

            $transfer = AccountTransfer::create([
                'reference' => $ref,
                'from_account_id' => $from->id,
                'to_account_id' => $to->id,
                'amount' => $amount,
                'fee' => $fee,
                'user_id' => $user->id,
                'date' => now()->toDateString(),
                'status' => 'valide',
                'notes' => $reason,
            ]);

            // Transaction Out
            Transaction::create([
                'financial_account_id' => $from->id,
                'cash_register_id' => CashRegister::first()?->id,
                'user_id' => $user->id,
                'type' => 'retrait',
                'amount' => $totalDebit,
                'balance_before' => $fromBefore,
                'balance_after' => $fromAfter,
                'description' => "Transfert sortant {$ref} vers {$to->name}".($fee > 0 ? " (dont frais {$fee} FCFA)" : ''),
                'reference' => $ref.'-OUT',
                'date' => now()->toDateString(),
            ]);

            // Transaction In
            Transaction::create([
                'financial_account_id' => $to->id,
                'cash_register_id' => CashRegister::first()?->id,
                'user_id' => $user->id,
                'type' => 'depot',
                'amount' => $amount,
                'balance_before' => $toBefore,
                'balance_after' => $toAfter,
                'description' => "Transfert entrant {$ref} depuis {$from->name}",
                'reference' => $ref.'-IN',
                'date' => now()->toDateString(),
            ]);

            self::logAudit(
                userId: $user->id,
                action: 'transfert',
                auditableType: AccountTransfer::class,
                auditableId: $transfer->id,
                reference: $transfer->reference,
                amountBefore: $fromBefore,
                amountAfter: $fromAfter,
                reason: "Virement de {$from->name} vers {$to->name} : {$amount} FCFA"
            );

            return $transfer;
        });
    }

    /**
     * Ouvrir une session journalière de caisse.
     */
    public static function openCashSession(
        int $accountId,
        float $openingBalance,
        User $user,
        ?string $notes = null
    ): CashRegisterSession {
        $existing = CashRegisterSession::where('financial_account_id', $accountId)
            ->where('status', 'ouverte')
            ->first();

        if ($existing) {
            throw new Exception("Une session de caisse est déjà ouverte pour ce compte ({$existing->reference}). Clôturez-la avant d'en ouvrir une nouvelle.");
        }

        $account = FinancialAccount::findOrFail($accountId);
        $ref = 'SESS-'.now()->format('Ymd').'-'.rand(100, 999);

        $session = CashRegisterSession::create([
            'reference' => $ref,
            'financial_account_id' => $account->id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_balance' => $openingBalance,
            'theoretical_balance' => $openingBalance,
            'status' => 'ouverte',
            'notes' => $notes,
        ]);

        self::logAudit(
            userId: $user->id,
            action: 'creation',
            auditableType: CashRegisterSession::class,
            auditableId: $session->id,
            reference: $session->reference,
            amountAfter: $openingBalance,
            reason: "Ouverture caisse {$account->name} avec solde initial : {$openingBalance} FCFA"
        );

        return $session;
    }

    /**
     * Fermer une session de caisse avec contrôle d'écart obligatoire.
     */
    public static function closeCashSession(
        CashRegisterSession $session,
        float $realBalance,
        ?string $reason = null,
        ?User $validator = null
    ): CashRegisterSession {
        return DB::transaction(function () use ($session, $realBalance, $reason, $validator) {
            if ($session->status === 'fermee') {
                throw new Exception('Cette session de caisse est déjà clôturée.');
            }

            // Calculate movements during session
            $inflows = (float) Transaction::where('financial_account_id', $session->financial_account_id)
                ->whereIn('type', ['encaissement', 'depot'])
                ->where('created_at', '>=', $session->opened_at)
                ->sum('amount');

            $outflows = (float) Transaction::where('financial_account_id', $session->financial_account_id)
                ->whereIn('type', ['retrait'])
                ->where('created_at', '>=', $session->opened_at)
                ->sum('amount');

            $theoretical = (float) $session->opening_balance + $inflows - $outflows;
            $diff = $realBalance - $theoretical;

            if (abs($diff) > 0.01 && empty($reason)) {
                throw new Exception("Un écart de {$diff} FCFA est constaté. La justification de l'écart est obligatoire.");
            }

            $session->update([
                'closed_at' => now(),
                'total_inflow' => $inflows,
                'total_outflow' => $outflows,
                'theoretical_balance' => $theoretical,
                'real_balance' => $realBalance,
                'discrepancy' => $diff,
                'discrepancy_reason' => $reason,
                'status' => 'fermee',
                'validated_by' => $validator?->id,
            ]);

            self::logAudit(
                userId: $validator?->id ?? auth()->id() ?? $session->user_id,
                action: 'cloture',
                auditableType: CashRegisterSession::class,
                auditableId: $session->id,
                reference: $session->reference,
                amountBefore: $theoretical,
                amountAfter: $realBalance,
                reason: "Clôture caisse : Théorique {$theoretical} FCFA, Réel {$realBalance} FCFA (Écart: {$diff} FCFA)"
            );

            return $session;
        });
    }

    /**
     * Valider une dépense selon le workflow à seuils.
     */
    public static function approveExpense(Expense $expense, User $user, ?string $reason = null): Expense
    {
        return DB::transaction(function () use ($expense, $user, $reason) {
            if ($expense->status === 'validee') {
                throw new Exception('Cette dépense a déjà été validée.');
            }

            // Find financial account or cash register
            $account = $expense->financialAccount
                ?: ($expense->cash_register_id ? FinancialAccount::where('code', 'CP-01')->first() : null)
                ?: FinancialAccount::first();

            if ($account && (float) $account->balance < (float) $expense->amount) {
                throw new Exception("Solde insuffisant sur le compte '{$account->name}' ({$account->balance} FCFA disponibles, requis: {$expense->amount} FCFA).");
            }

            if ($account) {
                $before = (float) $account->balance;
                $after = $before - (float) $expense->amount;
                $account->decrement('balance', $expense->amount);

                // Transaction
                Transaction::create([
                    'financial_account_id' => $account->id,
                    'cash_register_id' => $expense->cash_register_id,
                    'domain_id' => $expense->domain_id,
                    'user_id' => $user->id,
                    'type' => 'retrait',
                    'amount' => $expense->amount,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'description' => "Paiement dépense {$expense->reference} : {$expense->description}",
                    'reference' => 'TRX-EXP-'.time(),
                    'date' => now()->toDateString(),
                ]);
            }

            // Update legacy cash register if exists
            if ($expense->cash_register_id) {
                $caisse = $expense->cashRegister;
                if ($caisse && $caisse->balance >= $expense->amount) {
                    $caisse->decrement('balance', $expense->amount);
                }
            }

            $expense->update([
                'status' => 'validee',
                'approved_by' => $user->id,
            ]);

            self::logAudit(
                userId: $user->id,
                action: 'validation',
                auditableType: Expense::class,
                auditableId: $expense->id,
                reference: $expense->reference,
                amountAfter: (float) $expense->amount,
                reason: $reason ?? "Validation de la dépense {$expense->reference}"
            );

            return $expense;
        });
    }

    /**
     * Clôturer et verrouiller un mois comptable.
     */
    public static function closePeriod(int $year, int $month, User $user, ?string $notes = null): FinancialPeriodClosing
    {
        return DB::transaction(function () use ($year, $month, $user, $notes) {
            $existing = FinancialPeriodClosing::where('year', $year)->where('month', $month)->first();
            if ($existing && $existing->is_locked) {
                throw new Exception("La période {$month}/{$year} est déjà clôturée et verrouillée.");
            }

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $totalRevenues = (float) Revenue::whereBetween('date', [$startDate, $endDate])->sum('amount');
            $totalExpenses = (float) Expense::where('status', 'validee')->whereBetween('date', [$startDate, $endDate])->sum('amount');
            $netResult = $totalRevenues - $totalExpenses;
            $closingCash = (float) FinancialAccount::active()->sum('balance');

            $monthName = $startDate->translatedFormat('F Y');

            $closing = FinancialPeriodClosing::updateOrCreate(
                ['year' => $year, 'month' => $month],
                [
                    'period_label' => ucfirst($monthName),
                    'closed_by' => $user->id,
                    'closed_at' => now(),
                    'is_locked' => true,
                    'total_revenues' => $totalRevenues,
                    'total_expenses' => $totalExpenses,
                    'net_result' => $netResult,
                    'closing_cash_balance' => $closingCash,
                    'notes' => $notes ?? "Clôture générale du mois de {$monthName}",
                ]
            );

            self::logAudit(
                userId: $user->id,
                action: 'cloture',
                auditableType: FinancialPeriodClosing::class,
                auditableId: $closing->id,
                reference: "CLOT-{$year}-{$month}",
                amountBefore: $totalRevenues,
                amountAfter: $netResult,
                reason: "Clôture et verrouillage de la période {$monthName}"
            );

            return $closing;
        });
    }

    /**
     * Assistant Financier IA en langage naturel.
     */
    public static function askAiAssistant(string $question): array
    {
        $q = strtolower(trim($question));

        $totalTreasury = (float) FinancialAccount::active()->sum('balance');
        $overdueInvoices = Invoice::whereIn('status', ['non_payee', 'partielle'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->get();
        $overdueCount = $overdueInvoices->count();
        $overdueAmount = (float) $overdueInvoices->sum(fn ($i) => max(0, $i->total - $i->paid_amount));

        $monthRevenues = (float) Revenue::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');
        $monthExpenses = (float) Expense::where('status', 'validee')->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');
        $monthProfit = $monthRevenues - $monthExpenses;

        $pendingExpenses = Expense::whereIn('status', ['en_attente', 'soumise'])->get();

        if (str_contains($q, 'trésorerie') || str_contains($q, 'disponible') || str_contains($q, 'solde')) {
            $accounts = FinancialAccount::active()->get()->map(fn ($a) => "• {$a->name} ({$a->type}) : ".number_format($a->balance, 0, ',', ' ').' FCFA')->implode("\n");

            return [
                'intent' => 'treasury_inquiry',
                'answer' => 'La trésorerie totale disponible dans IVOSPHERE est de **'.number_format($totalTreasury, 0, ',', ' ')." FCFA**.\n\nDétail des comptes :\n{$accounts}",
                'metric' => number_format($totalTreasury, 0, ',', ' ').' FCFA',
            ];
        }

        if (str_contains($q, 'retard') || str_contains($q, 'impay') || str_contains($q, 'créance')) {
            $samples = $overdueInvoices->take(3)->map(fn ($i) => "• {$i->reference} ({$i->customer?->company_name}) : reste ".number_format(max(0, $i->total - $i->paid_amount), 0, ',', ' ')." FCFA (Éch. {$i->due_date->format('d/m/Y')})")->implode("\n");

            return [
                'intent' => 'overdue_inquiry',
                'answer' => "Il y a actuellement **{$overdueCount} factures en retard** pour un montant total de créances impayées de **".number_format($overdueAmount, 0, ',', ' ')." FCFA**.\n\nPrincipales factures en retard :\n{$samples}",
                'metric' => "{$overdueCount} factures (".number_format($overdueAmount, 0, ',', ' ').' FCFA)',
            ];
        }

        if (str_contains($q, 'tech') || str_contains($q, 'dépensé en tech') || str_contains($q, 'domaine')) {
            $techDomain = Domain::where('code', 'TECH')->orWhere('name', 'like', '%TECH%')->first();
            $techExpenses = $techDomain ? (float) Expense::where('domain_id', $techDomain->id)->where('status', 'validee')->whereMonth('date', now()->month)->sum('amount') : 0;
            $techRevenues = $techDomain ? (float) Revenue::where('domain_id', $techDomain->id)->whereMonth('date', now()->month)->sum('amount') : 0;

            return [
                'intent' => 'domain_performance',
                'answer' => 'Pour le pôle **IVOSPHERE TECH** ce mois-ci :\n• Dépenses : **'.number_format($techExpenses, 0, ',', ' ')." FCFA**\n• Recettes : **".number_format($techRevenues, 0, ',', ' ')." FCFA**\n• Marge nette : **".number_format($techRevenues - $techExpenses, 0, ',', ' ').' FCFA**',
                'metric' => number_format($techExpenses, 0, ',', ' ').' FCFA',
            ];
        }

        if (str_contains($q, 'rentabilité') || str_contains($q, 'bénéfice') || str_contains($q, 'résultat')) {
            return [
                'intent' => 'profitability_inquiry',
                'answer' => 'Le résultat net de ce mois est de **+'.number_format($monthProfit, 0, ',', ' ').' FCFA** (Recettes : '.number_format($monthRevenues, 0, ',', ' ').' FCFA | Dépenses : '.number_format($monthExpenses, 0, ',', ' ').' FCFA). Le taux de marge globale est de **'.($monthRevenues > 0 ? round(($monthProfit / $monthRevenues) * 100, 1) : 0).'%**.',
                'metric' => number_format($monthProfit, 0, ',', ' ').' FCFA',
            ];
        }

        // Default overview answer
        return [
            'intent' => 'general_financial_summary',
            'answer' => "Synthèse financière IVOSPHERE en direct :\n• Trésorerie disponible : **".number_format($totalTreasury, 0, ',', ' ')." FCFA**\n• Recettes du mois : **".number_format($monthRevenues, 0, ',', ' ')." FCFA**\n• Dépenses du mois : **".number_format($monthExpenses, 0, ',', ' ')." FCFA**\n• Factures en retard : **{$overdueCount}** (".number_format($overdueAmount, 0, ',', ' ')." FCFA)\n• Dépenses en attente d'approbation : **".$pendingExpenses->count().'**',
            'metric' => number_format($totalTreasury, 0, ',', ' ').' FCFA',
        ];
    }
}
