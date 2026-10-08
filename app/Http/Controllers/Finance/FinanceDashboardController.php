<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\AccountTransfer;
use App\Models\BankReconciliation;
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
use App\Models\Revenue;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Services\FinanceService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceDashboardController extends Controller
{
    public function index(Request $request)
    {
        $domainId = $request->input('domain_id');
        $period = $request->input('period', 'month');

        // Date range based on period
        $now = Carbon::now();
        switch ($period) {
            case 'today':
            case 'day':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                $periodLabel = "Aujourd'hui";
                break;
            case 'this_week':
            case 'week':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                $periodLabel = 'Cette semaine';
                break;
            case 'this_quarter':
            case 'quarter':
                $startDate = $now->copy()->startOfQuarter();
                $endDate = $now->copy()->endOfQuarter();
                $periodLabel = 'Ce trimestre (T'.$now->quarter.')';
                break;
            case 'year':
            case 'this_year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                $periodLabel = 'Cette année ('.$now->year.')';
                break;
            case 'all':
                $startDate = Carbon::create(2020, 1, 1);
                $endDate = $now->copy()->endOfYear();
                $periodLabel = 'Tout l\'historique';
                break;
            case 'month':
            case 'this_month':
            default:
                $period = 'month';
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $periodLabel = 'Ce mois ('.ucfirst($now->locale('fr')->translatedFormat('F Y')).')';
                break;
        }

        $domains = Domain::all();
        $selectedDomain = $domainId ? Domain::find($domainId) : null;

        // Fonction unifiée de calcul des encaissements / recettes
        $getRevenueForRange = function ($sDate, $eDate, $dId = null) {
            $rTotal = (float) Revenue::forDateRange($sDate, $eDate)
                ->when($dId, fn ($q) => $q->byDomain($dId))
                ->sum('amount');

            $pTotal = (float) Payment::where('status', 'valide')
                ->whereDate('date', '>=', $sDate->toDateString())
                ->whereDate('date', '<=', $eDate->toDateString())
                ->when($dId, fn ($q) => $q->where(fn ($sub) => $sub->where('domain_id', $dId)->orWhereHas('invoice', fn ($inv) => $inv->where('domain_id', $dId))))
                ->sum('amount');

            // Déduplication des paiements déjà enregistrés en Revenue (via payment_id)
            $dedup = (float) Revenue::whereNotNull('payment_id')
                ->forDateRange($sDate, $eDate)
                ->when($dId, fn ($q) => $q->byDomain($dId))
                ->sum('amount');

            return max(0, $rTotal + $pTotal - $dedup);
        };

        // 1. Top 7 KPIs
        // ENCAISSEMENTS (Revenues & Paiements validés)
        $totalRecettes = $getRevenueForRange($startDate, $endDate, $domainId);

        // DÉPENSES (Validated Expenses)
        $expensesQuery = Expense::where('status', 'validee')
            ->whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', $endDate->toDateString());
        if ($domainId) {
            $expensesQuery->byDomain($domainId);
        }
        $totalDepenses = (float) $expensesQuery->sum('amount');

        // TRÉSORERIE (Total current balance across all active accounts)
        $financialAccountsQuery = FinancialAccount::active();
        if ($domainId) {
            $financialAccountsQuery->where(function ($q) use ($domainId) {
                $q->where('domain_id', $domainId)->orWhereNull('domain_id');
            });
        }
        $financialAccounts = $financialAccountsQuery->get();
        $totalTresorerie = (float) $financialAccounts->sum('balance');

        // Legacy Cash Registers for backward compatibility & tests
        $cashRegisters = CashRegister::active()->with('domain')->get();

        // CRÉANCES (Unpaid client invoices: remaining balance)
        $clientInvoicesQuery = Invoice::whereIn('status', ['non_payee', 'partielle']);
        if ($domainId) {
            $clientInvoicesQuery->where('domain_id', $domainId);
        }
        $totalCreances = (float) $clientInvoicesQuery->get()->sum(fn ($i) => max(0, $i->total - $i->paid_amount));
        $facturesImpayeesCount = $clientInvoicesQuery->count();

        // DETTES (Unpaid supplier invoices: remaining balance)
        $supplierInvoicesQuery = SupplierInvoice::whereIn('status', ['non_payee', 'partielle']);
        if ($domainId) {
            $supplierInvoicesQuery->where('domain_id', $domainId);
        }
        $totalDettes = (float) $supplierInvoicesQuery->get()->sum(fn ($si) => $si->remaining_amount);

        // BÉNÉFICE (Net result)
        $soldeNet = $totalRecettes - $totalDepenses;
        $benefice = $soldeNet;

        // 2. Évolution de la Trésorerie (12 mois de l'année en cours)
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $mStart = Carbon::create($now->year, $i, 1)->startOfMonth();
            $mEnd = Carbon::create($now->year, $i, 1)->endOfMonth();

            $mRev = $getRevenueForRange($mStart, $mEnd, $domainId);

            $mExp = (float) Expense::where('status', 'validee')
                ->when($domainId, fn ($q) => $q->where('domain_id', $domainId))
                ->whereDate('date', '>=', $mStart->toDateString())
                ->whereDate('date', '<=', $mEnd->toDateString())
                ->sum('amount');

            $monthlyData[] = [
                'month' => ucfirst($mStart->translatedFormat('M')),
                'month_full' => ucfirst($mStart->translatedFormat('F')),
                'revenue' => $mRev,
                'expense' => $mExp,
                'net' => $mRev - $mExp,
            ];
        }

        // 3. Section À TRAITER
        // Factures en retard
        $overdueInvoices = Invoice::with(['customer', 'domain'])
            ->whereIn('status', ['non_payee', 'partielle'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->orderBy('due_date')
            ->get();

        // Dépenses à valider
        $pendingExpenses = Expense::with(['domain', 'user', 'supplier'])
            ->whereIn('status', ['en_attente', 'soumise'])
            ->orderBy('date', 'desc')
            ->get();

        // Sessions de caisse ouvertes
        $openSessions = CashRegisterSession::with(['account', 'user'])
            ->where('status', 'ouverte')
            ->get();

        // 4. Rentabilité par Domaine (PRINT, SPORT, TECH, MEDIA, ASSURANCE)
        $domainProfitability = $domains->map(function ($domain) use ($startDate, $endDate, $getRevenueForRange) {
            $rev = $getRevenueForRange($startDate, $endDate, $domain->id);
            $exp = (float) Expense::where('domain_id', $domain->id)
                ->where('status', 'validee')
                ->whereDate('date', '>=', $startDate->toDateString())
                ->whereDate('date', '<=', $endDate->toDateString())
                ->sum('amount');
            $net = $rev - $exp;
            $margin = $rev > 0 ? round(($net / $rev) * 100, 1) : 0;

            return [
                'domain' => $domain,
                'revenues' => $rev,
                'expenses' => $exp,
                'net' => $net,
                'margin' => $margin,
            ];
        });

        // 5. Suivi des Budgets par Domaine (Prévu vs Réel)
        $budgets = Budget::with('domain')->where('status', 'actif')->get()->map(function ($b) use ($startDate, $endDate) {
            $realSpent = (float) Expense::where('domain_id', $b->domain_id)
                ->where('status', 'validee')
                ->forDateRange($startDate, $endDate)
                ->sum('amount');

            $available = max(0, (float) $b->amount - $realSpent);
            $usagePercent = $b->amount > 0 ? min(100, round(($realSpent / $b->amount) * 100, 1)) : 0;

            return [
                'model' => $b,
                'domain_name' => $b->domain?->name ?? 'Général',
                'budget_amount' => (float) $b->amount,
                'real_spent' => $realSpent,
                'available' => $available,
                'usage_percent' => $usagePercent,
                'is_overbudget' => $realSpent > $b->amount,
            ];
        });

        // 6. Créances Clients (Argent à recevoir) avec ventilation
        $clientInvoices = Invoice::with(['customer', 'domain'])
            ->whereIn('status', ['non_payee', 'partielle'])
            ->orderBy('due_date')
            ->get()
            ->map(function ($inv) {
                $rem = max(0, (float) $inv->total - (float) $inv->paid_amount);
                $daysOverdue = $inv->due_date && $inv->due_date->isPast() ? (int) $inv->due_date->diffInDays(now()) : 0;

                $inv->remaining_balance = $rem;
                $inv->days_overdue = $daysOverdue;
                $inv->age_category = match (true) {
                    $daysOverdue > 90 => '90j+',
                    $daysOverdue > 60 => '60j',
                    $daysOverdue > 30 => '30j',
                    $daysOverdue > 0 => 'retard',
                    default => 'a_jour',
                };

                return $inv;
            });

        // 7. Dettes Fournisseurs
        $supplierInvoices = SupplierInvoice::with(['supplier', 'domain'])
            ->whereIn('status', ['non_payee', 'partielle'])
            ->orderBy('due_date')
            ->get();

        // 8. Transferts de Trésorerie
        $transfers = AccountTransfer::with(['fromAccount', 'toAccount', 'user'])
            ->orderBy('date', 'desc')
            ->take(15)
            ->get();

        // 9. Rapprochements Bancaires
        $reconciliations = BankReconciliation::with(['account', 'user', 'items'])
            ->orderBy('statement_date', 'desc')
            ->take(5)
            ->get();

        // 10. Journal d'Audit Financier
        $auditLogs = FinancialAuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        // 11. Clôtures de Périodes
        $closings = FinancialPeriodClosing::with('closedBy')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $currentMonthClosed = FinanceService::isPeriodLocked(now());

        // Customers & Suppliers for modal quick actions
        $customers = Customer::orderBy('company_name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('finance.index', compact(
            'period',
            'periodLabel',
            'domainId',
            'selectedDomain',
            'domains',
            'totalRecettes',
            'totalDepenses',
            'totalTresorerie',
            'totalCreances',
            'totalDettes',
            'facturesImpayeesCount',
            'soldeNet',
            'benefice',
            'monthlyData',
            'financialAccounts',
            'cashRegisters',
            'overdueInvoices',
            'pendingExpenses',
            'openSessions',
            'domainProfitability',
            'budgets',
            'clientInvoices',
            'supplierInvoices',
            'transfers',
            'reconciliations',
            'auditLogs',
            'closings',
            'currentMonthClosed',
            'customers',
            'suppliers'
        ));
    }

    /**
     * Effectuer un transfert entre deux comptes de trésorerie (Point 3).
     */
    public static function storeTransfer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:financial_accounts,id',
            'to_account_id' => 'required|exists:financial_accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:1',
            'fee' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $transfer = FinanceService::transferFunds(
                fromAccountId: (int) $validated['from_account_id'],
                toAccountId: (int) $validated['to_account_id'],
                amount: (float) $validated['amount'],
                fee: (float) ($validated['fee'] ?? 0),
                user: auth()->user(),
                reason: $validated['reason'] ?? 'Virement de fonds inter-comptes'
            );

            return back()->with('success', "Transfert {$transfer->reference} de ".number_format($transfer->amount, 0, ',', ' ').' FCFA effectué avec succès.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Ouvrir une session journalière de caisse (Point 16).
     */
    public static function openSession(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'financial_account_id' => 'required|exists:financial_accounts,id',
            'opening_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $session = FinanceService::openCashSession(
                accountId: (int) $validated['financial_account_id'],
                openingBalance: (float) $validated['opening_balance'],
                user: auth()->user(),
                notes: $validated['notes']
            );

            return back()->with('success', "Caisse ouverte avec succès ({$session->reference}). Solde initial : ".number_format($session->opening_balance, 0, ',', ' ').' FCFA.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Fermer une session de caisse avec contrôle d'écart (Point 16).
     */
    public static function closeSession(Request $request, CashRegisterSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'real_balance' => 'required|numeric|min:0',
            'discrepancy_reason' => 'nullable|string|max:255',
        ]);

        try {
            FinanceService::closeCashSession(
                session: $session,
                realBalance: (float) $validated['real_balance'],
                reason: $validated['discrepancy_reason'] ?? null,
                validator: auth()->user()
            );

            $msg = "Session {$session->reference} clôturée avec succès.";
            if ($session->discrepancy != 0) {
                $msg .= ' Écart enregistré : '.number_format($session->discrepancy, 0, ',', ' ').' FCFA.';
            }

            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Enregistrer un paiement client (Points 4, 7, 8, 28).
     */
    public static function storePayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'financial_account_id' => 'required|exists:financial_accounts,id',
            'amount' => 'required|numeric|min:1',
            'method' => 'required|string|max:50',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $payment = FinanceService::recordPayment($validated, auth()->user());

            return back()->with('success', "Paiement {$payment->reference} de ".number_format($payment->amount, 0, ',', ' ').' FCFA enregistré. Facture et trésorerie mises à jour.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Enregistrer une dette fournisseur (Point 11).
     */
    public static function storeSupplierInvoice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'domain_id' => 'nullable|exists:domains,id',
            'total_amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $ref = 'FF-'.now()->format('Y').'-'.str_pad((string) (SupplierInvoice::count() + 1), 4, '0', STR_PAD_LEFT);

        $invoice = SupplierInvoice::create([
            'reference' => $ref,
            'supplier_id' => $validated['supplier_id'],
            'domain_id' => $validated['domain_id'] ?? null,
            'user_id' => auth()->id(),
            'date' => $validated['date'],
            'due_date' => $validated['due_date'],
            'total_amount' => $validated['total_amount'],
            'paid_amount' => 0,
            'status' => 'non_payee',
            'notes' => $validated['notes'] ?? null,
        ]);

        FinanceService::logAudit(
            userId: auth()->id(),
            action: 'creation',
            auditableType: SupplierInvoice::class,
            auditableId: $invoice->id,
            reference: $invoice->reference,
            amountAfter: (float) $invoice->total_amount,
            reason: "Enregistrement dette fournisseur {$invoice->supplier?->name} ({$invoice->total_amount} FCFA)"
        );

        return back()->with('success', "Facture fournisseur {$invoice->reference} enregistrée avec succès.");
    }

    /**
     * Enregistrer un rapprochement bancaire (Point 18).
     */
    public static function storeReconciliation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'financial_account_id' => 'required|exists:financial_accounts,id',
            'statement_date' => 'required|date',
            'statement_balance' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $account = FinancialAccount::findOrFail($validated['financial_account_id']);
        $diff = (float) $validated['statement_balance'] - (float) $account->balance;
        $ref = 'RAP-'.now()->format('Ym').'-'.rand(10, 99);

        $rec = BankReconciliation::create([
            'reference' => $ref,
            'financial_account_id' => $account->id,
            'user_id' => auth()->id(),
            'statement_date' => $validated['statement_date'],
            'statement_balance' => $validated['statement_balance'],
            'system_balance' => $account->balance,
            'discrepancy' => $diff,
            'status' => abs($diff) < 0.01 ? 'rapproche' : 'ecart',
            'notes' => $validated['notes'],
        ]);

        return back()->with('success', "Rapprochement bancaire {$rec->reference} enregistré (Écart : ".number_format($diff, 0, ',', ' ').' FCFA).');
    }

    /**
     * Clôturer et verrouiller le mois comptable (Points 24, 25).
     */
    public static function storeClosing(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'notes' => 'nullable|string',
        ]);

        try {
            $closing = FinanceService::closePeriod(
                year: (int) $validated['year'],
                month: (int) $validated['month'],
                user: auth()->user(),
                notes: $validated['notes']
            );

            return back()->with('success', "Période {$closing->period_label} clôturée et verrouillée avec succès 🔒.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Assistant Financier IA (Point 26).
     */
    public static function aiQuery(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
        ]);

        $response = FinanceService::askAiAssistant($validated['question']);

        return response()->json($response);
    }

    /**
     * Export du rapport financier en format CSV (Points 20, 21).
     */
    public function exportReport(Request $request): StreamedResponse
    {
        $revenues = Revenue::with(['domain', 'user'])->orderBy('date', 'desc')->get();
        $expenses = Expense::with(['domain', 'user', 'supplier'])->where('status', 'validee')->orderBy('date', 'desc')->get();

        return response()->streamDownload(function () use ($revenues, $expenses) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Entête
            fputcsv($handle, ['RAPPORT FINANCIER IVOSPHERE CRM', now()->format('d/m/Y H:i')], ';');
            fputcsv($handle, [], ';');

            // Recettes
            fputcsv($handle, ['RECETTES ET ENCAISSEMENTS'], ';');
            fputcsv($handle, ['Date', 'Référence', 'Pôle / Domaine', 'Source', 'Description', 'Mode de paiement', 'Montant (FCFA)'], ';');
            foreach ($revenues as $r) {
                fputcsv($handle, [
                    $r->date ? $r->date->format('d/m/Y') : '-',
                    $r->reference,
                    $r->domain?->name ?? 'Général',
                    $r->source,
                    $r->description,
                    $r->payment_method,
                    number_format((float) $r->amount, 0, ',', ' '),
                ], ';');
            }

            fputcsv($handle, [], ';');
            fputcsv($handle, ['DÉPENSES ET DÉCAISSEMENTS VALIDÉS'], ';');
            fputcsv($handle, ['Date', 'Référence', 'Pôle / Domaine', 'Catégorie', 'Description', 'Fournisseur', 'Montant (FCFA)'], ';');
            foreach ($expenses as $e) {
                fputcsv($handle, [
                    $e->date ? $e->date->format('d/m/Y') : '-',
                    $e->reference,
                    $e->domain?->name ?? 'Général',
                    $e->category,
                    $e->description,
                    $e->supplier?->name ?? '-',
                    number_format((float) $e->amount, 0, ',', ' '),
                ], ';');
            }

            fclose($handle);
        }, 'ivosphere-rapport-financier-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
