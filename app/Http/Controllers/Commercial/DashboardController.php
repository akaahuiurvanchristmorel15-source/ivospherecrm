<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Services\CommercialAgentBriefing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, CommercialAgentBriefing $briefing): View
    {
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
                $periodLabel = 'Ce trimestre (T'.$now->quarter.')';
                break;
            case 'this_year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                $periodLabel = 'Cette année ('.$now->year.')';
                break;
            case 'this_month':
            default:
                $period = 'this_month';
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $periodLabel = 'Ce mois ('.ucfirst($now->translatedFormat('F Y')).')';
                break;
        }

        $getCaForRange = function (?Carbon $start, ?Carbon $end) {
            $paymentQuery = Payment::where('status', 'valide');
            if ($start && $end) {
                $paymentQuery->whereDate('date', '>=', $start->toDateString())
                    ->whereDate('date', '<=', $end->toDateString());
            }
            $ca = (float) $paymentQuery->sum('amount');

            // Factures payées ou partielles non rattachées à des enregistrements de paiement séparés
            $untrackedInvoiceQuery = Invoice::where('status', '!=', 'annulee')
                ->whereDoesntHave('payments');
            if ($start && $end) {
                $untrackedInvoiceQuery->whereDate('date', '>=', $start->toDateString())
                    ->whereDate('date', '<=', $end->toDateString());
            }
            $untrackedCa = (float) $untrackedInvoiceQuery->sum('paid_amount');

            return $ca + $untrackedCa;
        };

        // Chiffres d'affaires par périodes clés
        $caToday = $getCaForRange($now->copy()->startOfDay(), $now->copy()->endOfDay());
        $caWeek = $getCaForRange($now->copy()->startOfWeek(), $now->copy()->endOfWeek());
        $caMonth = $getCaForRange($now->copy()->startOfMonth(), $now->copy()->endOfMonth());
        $caQuarter = $getCaForRange($now->copy()->startOfQuarter(), $now->copy()->endOfQuarter());
        $caYear = $getCaForRange($now->copy()->startOfYear(), $now->copy()->endOfYear());
        $caPeriod = $getCaForRange($startDate, $endDate);

        // Devis en cours
        $devisEnCours = Quotation::whereIn('status', ['envoye', 'envoyé', 'brouillon'])->count();

        // Commandes actives
        $commandesActives = Order::whereIn('status', ['en_attente', 'confirmee', 'confirmée', 'en_cours'])->count();

        // Créances impayées
        $unpaidInvoices = Invoice::whereIn('status', ['non_payee', 'partielle']);
        $impayes = (float) $unpaidInvoices->sum('total') - (float) $unpaidInvoices->sum('paid_amount');

        // Pipeline prospects par étape
        $pipeline = collect([
            'nouveau' => ['Nouveau', 10],
            'contacte' => ['Contacté', 25],
            'interesse' => ['Intéressé', 50],
            'devis_envoye' => ['Devis envoyé', 70],
            'negociation' => ['Négociation', 85],
            'gagne' => ['Gagné', 100],
        ])->map(fn (array $stage, string $key) => [
            'label' => $stage[0],
            'probability' => $stage[1],
            'count' => Prospect::where('stage', $key)->orWhere('status', $key)->count(),
        ]);

        $latestQuotations = Quotation::with('customer')->latest()->take(5)->get();

        $agent = $briefing->build(explode(' ', trim((string) $request->user()->name))[0] ?: 'à vous', $now);

        return view('commercial.index', compact(
            'agent',
            'pipeline',
            'latestQuotations',
            'period',
            'periodLabel',
            'startDate',
            'endDate',
            'caToday',
            'caWeek',
            'caMonth',
            'caQuarter',
            'caYear',
            'caPeriod',
            'devisEnCours',
            'commandesActives',
            'impayes'
        ));
    }
}
