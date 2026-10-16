<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Subscriptions\SubscriptionPdfService;
use App\Services\Subscriptions\SubscriptionService;
use App\Support\TenantAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Espace d'un abonné : accueil, abonnement en cours, renouvellement et récapitulatif PDF.
 */
class SpaceController extends Controller
{
    public function home(Request $request): View|RedirectResponse
    {
        $tenant = TenantAccess::tenantOf($request->user());
        if (! $tenant || $tenant->is_main) {
            return redirect()->route('dashboard');
        }

        $shortcuts = collect([
            ['route' => 'commercial.pos.index', 'label' => 'Vente POS', 'description' => 'Encaisser une vente', 'icon' => 'pos'],
            ['route' => 'commercial.invoices.index', 'label' => 'Facturation', 'description' => 'Devis, commandes et factures', 'icon' => 'invoice'],
            ['route' => 'commercial.customers.index', 'label' => 'Clients', 'description' => 'Fichier clients', 'icon' => 'customers'],
            ['route' => 'stock.index', 'label' => 'Stock', 'description' => 'Articles, entrepôts et mouvements', 'icon' => 'stock'],
            ['route' => 'admin.users.index', 'label' => 'Utilisateurs', 'description' => 'Comptes et accès de votre équipe', 'icon' => 'users'],
            ['route' => 'admin.logs.index', 'label' => 'Journal & sécurité', 'description' => "Historique d'activité", 'icon' => 'logs'],
        ])->filter(fn (array $shortcut) => TenantAccess::allows($shortcut['route'], $request->user()))->values();

        $stats = [
            'products' => TenantAccess::allows('stock.index', $request->user()) ? Product::count() : null,
            'invoicesToday' => TenantAccess::allows('commercial.invoices.index', $request->user()) ? Invoice::whereDate('created_at', today())->count() : null,
            'users' => User::count(),
        ];

        return view('subscriptions.space.home', compact('tenant', 'shortcuts', 'stats'));
    }

    public function subscription(Request $request): View|RedirectResponse
    {
        $tenant = TenantAccess::tenantOf($request->user());
        if (! $tenant || $tenant->is_main) {
            return redirect()->route('dashboard');
        }

        return view('subscriptions.space.subscription', [
            'tenant' => $tenant,
            'subscriptions' => $tenant->subscriptions()->with('plan')->latest()->get(),
            'plans' => SubscriptionPlan::active()->get()->filter->canBeSubscribed()->values(),
            'usersCount' => User::count(),
        ]);
    }

    public function renew(Request $request, SubscriptionService $subscriptions): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403, "Seul l'administrateur de l'espace peut renouveler l'abonnement.");

        $tenant = TenantAccess::tenantOf($request->user());
        abort_if(! $tenant || $tenant->is_main, 404);

        $validated = $request->validate([
            'plan_id' => ['required', 'integer'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
        ]);

        $plan = SubscriptionPlan::find($validated['plan_id']);
        if (! $plan || ! $plan->canBeSubscribed()) {
            return back()->with('error', "Cette offre n'est pas disponible pour le moment.");
        }

        $subscription = $subscriptions->createPendingSubscription($tenant, $plan, $validated['billing_cycle']);

        try {
            return redirect()->away($subscriptions->gateway()->initiate($subscription->load(['tenant', 'plan'])));
        } catch (RuntimeException $exception) {
            report($exception);

            return back()->with('error', 'Le paiement en ligne est momentanément indisponible. Réessayez dans quelques instants.');
        }
    }

    public function pdf(Request $request, Subscription $subscription, SubscriptionPdfService $pdfService): Response
    {
        abort_unless((int) $subscription->tenant_id === (int) $request->user()->tenant_id && $subscription->isPaid(), 404);

        return OfferController::pdfResponse($subscription, $pdfService);
    }
}
