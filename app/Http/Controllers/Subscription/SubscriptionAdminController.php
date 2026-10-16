<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Subscriptions\SubscriptionPdfService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Administration Ivosphere : abonnés, statut de leur espace et tarifs des offres.
 */
class SubscriptionAdminController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureMainTenant();

        $tenants = Tenant::where('is_main', false)
            ->with(['plan', 'latestSubscription'])
            ->withCount(['users' => fn ($query) => $query->withoutGlobalScopes()])
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%')->orWhere('email', 'like', '%'.$request->search.'%');
            }))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'active' => Tenant::where('is_main', false)->where('status', 'active')->where('subscription_ends_at', '>', now())->count(),
            'pending' => Tenant::where('is_main', false)->where('status', 'pending')->count(),
            'revenue' => (int) Subscription::where('status', 'paid')->sum('amount'),
        ];

        return view('subscriptions.admin.index', compact('tenants', 'summary'));
    }

    public function show(Tenant $tenant): View
    {
        $this->ensureMainTenant();
        abort_if($tenant->is_main, 404);

        return view('subscriptions.admin.show', [
            'tenant' => $tenant->load('plan'),
            'subscriptions' => $tenant->subscriptions()->with('plan')->latest()->get(),
            'users' => User::withoutGlobalScopes()->where('tenant_id', $tenant->id)->with('roles')->get(),
        ]);
    }

    public function toggle(Tenant $tenant): RedirectResponse
    {
        $this->ensureMainTenant();
        abort_if($tenant->is_main || $tenant->status === 'pending', 404);

        $tenant->update(['status' => $tenant->status === 'suspended' ? 'active' : 'suspended']);

        return back()->with('success', $tenant->status === 'suspended'
            ? "L'espace {$tenant->name} est suspendu."
            : "L'espace {$tenant->name} est réactivé.");
    }

    public function pdf(Tenant $tenant, int $subscriptionId, SubscriptionPdfService $pdfService): Response
    {
        $this->ensureMainTenant();
        $subscription = $tenant->subscriptions()->where('status', 'paid')->findOrFail($subscriptionId);

        return OfferController::pdfResponse($subscription, $pdfService);
    }

    public function plans(): View
    {
        $this->ensureMainTenant();

        return view('subscriptions.admin.plans', [
            'plans' => SubscriptionPlan::orderBy('sort_order')->get(),
            'modules' => config('subscriptions.modules'),
        ]);
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $this->ensureMainTenant();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'price_monthly' => ['required', 'integer', 'min:0'],
            'price_yearly' => ['required', 'integer', 'min:0'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'is_available' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $plan->update([
            ...$validated,
            'is_available' => $request->boolean('is_available'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', "L'offre {$plan->name} a été mise à jour.");
    }

    private function ensureMainTenant(): void
    {
        abort_unless(TenantContext::isMain(), 403);
    }
}
