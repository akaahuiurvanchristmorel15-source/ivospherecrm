<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BankAccount;
use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Event;
use App\Models\Expense;
use App\Models\InsuranceAppointment;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Models\Setting;
use App\Models\StockAlert;
use App\Models\TechProjectTask;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the main central application dashboard.
     */
    public function index(Request $request): View
    {
        $domainCode = $request->query('domain');
        $currentDomain = null;

        if ($domainCode && $domainCode !== 'all') {
            $currentDomain = Domain::where('code', strtoupper($domainCode))->first();
        }

        $domainId = $currentDomain?->id;
        $domains = Domain::where('is_active', true)->get();

        // ── FILTRES TEMPORELS ──
        $period = $request->query('period', 'this_month');
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                $periodLabel = "Aujourd'hui";
                break;
            case 'this_week':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                $periodLabel = 'Cette semaine';
                break;
            case 'this_quarter':
                $startDate = $now->copy()->startOfQuarter();
                $endDate = $now->copy()->endOfQuarter();
                $periodLabel = 'Ce trimestre';
                break;
            case 'this_year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                $periodLabel = 'Cette année';
                break;
            case 'custom':
                $dateFrom = $request->query('date_from');
                $dateTo = $request->query('date_to');
                $startDate = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : $now->copy()->startOfMonth();
                $endDate = $dateTo ? Carbon::parse($dateTo)->endOfDay() : $now->copy()->endOfDay();
                $periodLabel = 'Période personnalisée';
                break;
            case 'this_month':
            default:
                $period = 'this_month';
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $periodLabel = 'Ce mois';
                break;
        }

        // ── 1. CHIFFRE D'AFFAIRES (CA) ──
        $getCaForRange = function (?Carbon $start, ?Carbon $end) use ($domainId) {
            $paymentQuery = Payment::where('status', 'valide')
                ->when($domainId, fn ($q) => $q->where(fn ($sub) => $sub->where('domain_id', $domainId)->orWhereHas('invoice', fn ($inv) => $inv->where('domain_id', $domainId))));

            if ($start && $end) {
                $paymentQuery->whereDate('date', '>=', $start->toDateString())
                    ->whereDate('date', '<=', $end->toDateString());
            }

            $ca = (float) $paymentQuery->sum('amount');

            // Factures payées ou partielles non rattachées à des paiements séparés
            $untrackedInvoiceQuery = Invoice::where('status', '!=', 'annulee')
                ->whereDoesntHave('payments')
                ->when($domainId, fn ($q) => $q->where('domain_id', $domainId));

            if ($start && $end) {
                $untrackedInvoiceQuery->whereDate('date', '>=', $start->toDateString())
                    ->whereDate('date', '<=', $end->toDateString());
            }

            $untrackedCa = (float) $untrackedInvoiceQuery->sum('paid_amount');

            return $ca + $untrackedCa;
        };

        // Chiffres d'Affaires par périodes standards
        $caToday = $getCaForRange($now->copy()->startOfDay(), $now->copy()->endOfDay());
        $caWeek = $getCaForRange($now->copy()->startOfWeek(), $now->copy()->endOfWeek());
        $caMonth = $getCaForRange($now->copy()->startOfMonth(), $now->copy()->endOfMonth());
        $caQuarter = $getCaForRange($now->copy()->startOfQuarter(), $now->copy()->endOfQuarter());
        $caYear = $getCaForRange($now->copy()->startOfYear(), $now->copy()->endOfYear());

        // CA sur la période sélectionnée (filtre actif)
        $caPeriod = $getCaForRange($startDate, $endDate);

        // ── 2. DÉPENSES & BÉNÉFICE ESTIMÉ ──
        $depensesPeriod = (float) Expense::when($domainId, fn ($q) => $q->where('domain_id', $domainId))
            ->where('status', 'validee')
            ->whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', $endDate->toDateString())
            ->sum('amount');

        $beneficeEstime = $caPeriod - $depensesPeriod;

        // ── 3. TRÉSORERIE DISPONIBLE ──
        if ($domainId) {
            $tresorerie = (float) CashRegister::where('domain_id', $domainId)
                ->where('is_active', true)
                ->sum('balance');
        } else {
            $caisseSolde = (float) CashRegister::where('is_active', true)->sum('balance');
            $banqueSolde = (float) BankAccount::where('is_active', true)->sum('balance');
            $tresorerie = $caisseSolde + $banqueSolde;
        }

        // ── 4. FACTURES IMPAYÉES ──
        $unpaidInvoicesQuery = Invoice::when($domainId, fn ($q) => $q->where('domain_id', $domainId))
            ->whereIn('status', ['non_payee', 'partielle']);
        $facturesImpayeesCount = $unpaidInvoicesQuery->count();
        $facturesImpayeesMontant = (float) $unpaidInvoicesQuery->sum(DB::raw('total - paid_amount'));

        // ── 5. COMMERCE & CRM ──
        $nombreVentes = Invoice::when($domainId, fn ($q) => $q->where('domain_id', $domainId))
            ->whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', $endDate->toDateString())
            ->where('status', '!=', 'annulee')
            ->count();

        $nombreCommandes = Order::when($domainId, fn ($q) => $q->where('domain_id', $domainId))
            ->whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', $endDate->toDateString())
            ->count();

        $nombreClients = Customer::when($domainId, fn ($q) => $q->where('domain_id', $domainId))->count();
        $nombreProspects = Prospect::when($domainId, fn ($q) => $q->where('domain_id', $domainId))->count();

        $commandesEnAttenteCount = Order::when($domainId, fn ($q) => $q->where('domain_id', $domainId))
            ->where('status', 'en_attente')
            ->count();

        $devisEnAttenteCount = Quotation::when($domainId, fn ($q) => $q->where('domain_id', $domainId))
            ->whereIn('status', ['brouillon', 'envoyé'])
            ->count();

        // ── 6. OPÉRATIONS & STOCKS ──
        $stockAlertsQuery = StockAlert::when($domainId, function ($q) use ($domainId) {
            $q->whereHas('warehouse', fn ($w) => $w->where('domain_id', $domainId));
        });

        $produitsEnRupture = (clone $stockAlertsQuery)->where('current_quantity', '<=', 0)->count();
        $produitsBientotEnRupture = (clone $stockAlertsQuery)->where('status', 'active')->where('current_quantity', '>', 0)->count();

        // ── 7. PLANNING, TÂCHES & ÉVÈNEMENTS ──
        $rdvDuJourCount = InsuranceAppointment::whereDate('date', Carbon::today())->count();
        $tachesARealiserCount = TechProjectTask::where('status', '!=', 'terminé')->count();
        $evenementsAVenirCount = Event::whereDate('date', '>=', Carbon::today())
            ->where('status', '!=', 'annulé')
            ->count();

        // Listes rapides
        $pendingOrders = Order::with('customer')
            ->when($domainId, fn ($q) => $q->where('domain_id', $domainId))
            ->where('status', 'en_attente')
            ->latest('date')
            ->take(5)
            ->get();

        $pendingQuotations = Quotation::with('customer')
            ->when($domainId, fn ($q) => $q->where('domain_id', $domainId))
            ->whereIn('status', ['brouillon', 'envoyé'])
            ->latest('date')
            ->take(5)
            ->get();

        $todayAppointments = InsuranceAppointment::with(['customer', 'advisor'])
            ->whereDate('date', '>=', Carbon::today())
            ->orderBy('date')
            ->take(4)
            ->get();

        $pendingTasks = TechProjectTask::with(['project', 'assignee'])
            ->where('status', '!=', 'terminé')
            ->latest()
            ->take(4)
            ->get();

        $upcomingEvents = Event::with('customer')
            ->whereDate('date', '>=', Carbon::today())
            ->where('status', '!=', 'annulé')
            ->orderBy('date')
            ->take(4)
            ->get();

        // ── 8. ACTIVITÉS RÉCENTES (AUDIT TRAIL) ──
        $recentActivities = ActivityLog::with(['user', 'domain'])
            ->when($domainId, fn ($q) => $q->where('domain_id', $domainId))
            ->latest('created_at')
            ->take(8)
            ->get();

        // ── 9. RÉPARTITION COMMERCIALE PAR DOMAINE ──
        $domainPerformance = [];
        $totalConsolidatedCa = 0;

        foreach ($domains as $d) {
            $dPaymentCa = (float) Payment::where('status', 'valide')
                ->where(fn ($sub) => $sub->where('domain_id', $d->id)->orWhereHas('invoice', fn ($inv) => $inv->where('domain_id', $d->id)))
                ->whereDate('date', '>=', $startDate->toDateString())
                ->whereDate('date', '<=', $endDate->toDateString())
                ->sum('amount');

            $dUntrackedCa = (float) Invoice::where('status', '!=', 'annulee')
                ->where('domain_id', $d->id)
                ->whereDoesntHave('payments')
                ->whereDate('date', '>=', $startDate->toDateString())
                ->whereDate('date', '<=', $endDate->toDateString())
                ->sum('paid_amount');

            $dCa = $dPaymentCa + $dUntrackedCa;

            $dOrders = Order::where('domain_id', $d->id)
                ->whereDate('date', '>=', $startDate->toDateString())
                ->whereDate('date', '<=', $endDate->toDateString())
                ->count();

            $domainPerformance[$d->code] = [
                'name' => $d->name,
                'code' => $d->code,
                'color' => $d->color ?? 'indigo',
                'ca' => $dCa,
                'orders' => $dOrders,
            ];
            $totalConsolidatedCa += $dCa;
        }

        // Calcul des pourcentages
        foreach ($domainPerformance as $k => $item) {
            $domainPerformance[$k]['percent'] = $totalConsolidatedCa > 0
                ? round(($item['ca'] / $totalConsolidatedCa) * 100)
                : 0;
        }

        $currency = Setting::get('currency', 'FCFA');

        return view('dashboard.index', [
            'now' => $now,
            'domains' => $domains,
            'currentDomain' => $currentDomain,
            'period' => $period,
            'periodLabel' => $periodLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'currency' => $currency,
            'user' => Auth::user(),

            // Finances
            'caToday' => $caToday,
            'caWeek' => $caWeek,
            'caMonth' => $caMonth,
            'caQuarter' => $caQuarter,
            'caYear' => $caYear,
            'caPeriod' => $caPeriod,
            'depensesPeriod' => $depensesPeriod,
            'beneficeEstime' => $beneficeEstime,
            'tresorerie' => $tresorerie,
            'facturesImpayeesCount' => $facturesImpayeesCount,
            'facturesImpayeesMontant' => $facturesImpayeesMontant,

            // Commerce
            'nombreVentes' => $nombreVentes,
            'nombreCommandes' => $nombreCommandes,
            'nombreClients' => $nombreClients,
            'nombreProspects' => $nombreProspects,
            'commandesEnAttenteCount' => $commandesEnAttenteCount,
            'devisEnAttenteCount' => $devisEnAttenteCount,

            // Stocks
            'produitsEnRupture' => $produitsEnRupture,
            'produitsBientotEnRupture' => $produitsBientotEnRupture,

            // Planning & Tâches
            'rdvDuJourCount' => $rdvDuJourCount,
            'tachesARealiserCount' => $tachesARealiserCount,
            'evenementsAVenirCount' => $evenementsAVenirCount,

            // Listes
            'pendingOrders' => $pendingOrders,
            'pendingQuotations' => $pendingQuotations,
            'todayAppointments' => $todayAppointments,
            'pendingTasks' => $pendingTasks,
            'upcomingEvents' => $upcomingEvents,
            'recentActivities' => $recentActivities,
            'domainPerformance' => $domainPerformance,
            'totalConsolidatedCa' => $totalConsolidatedCa,
        ]);
    }
}
