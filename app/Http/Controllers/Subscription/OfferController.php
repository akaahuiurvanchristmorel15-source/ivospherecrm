<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Subscriptions\PaystackGateway;
use App\Services\Subscriptions\SubscriptionPdfService;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Parcours public : offres, inscription, paiement en ligne et confirmation.
 */
class OfferController extends Controller
{
    public function __construct(private SubscriptionService $subscriptions) {}

    public function index(): View
    {
        $plans = SubscriptionPlan::active()->get()->groupBy('family');

        return view('subscriptions.offers.index', [
            'commercialPlans' => $plans->get('commercial', collect()),
            'enterprisePlans' => $plans->get('entreprise', collect()),
        ]);
    }

    public function signup(Request $request, SubscriptionPlan $plan): View|RedirectResponse
    {
        if (! $plan->canBeSubscribed()) {
            return redirect()->route('offres.index')->with('warning', "L'offre {$plan->name} sera bientôt disponible.");
        }

        return view('subscriptions.offers.signup', [
            'plan' => $plan,
            'billingCycle' => $request->query('cycle') === 'yearly' ? 'yearly' : 'monthly',
        ]);
    }

    public function register(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        if (! $plan->canBeSubscribed()) {
            return redirect()->route('offres.index')->with('warning', "L'offre {$plan->name} sera bientôt disponible.");
        }

        $validated = $request->validate([
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'company_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'admin_name' => ['required', 'string', 'max:150'],
            'admin_email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ], [
            'admin_email.unique' => 'Cette adresse e-mail est déjà utilisée par un compte IVOSPHERE.',
            'terms.accepted' => "Vous devez accepter les conditions d'abonnement.",
        ]);

        $subscription = $this->subscriptions->register($plan, $validated, $validated['billing_cycle']);
        $request->session()->put('subscription_reference', $subscription->reference);

        return $this->redirectToPayment($subscription);
    }

    public function simulation(Subscription $subscription): View
    {
        $this->ensureSimulationEnabled($subscription);

        return view('subscriptions.offers.simulation', ['subscription' => $subscription->load(['tenant', 'plan'])]);
    }

    public function simulate(Request $request, Subscription $subscription): RedirectResponse
    {
        $this->ensureSimulationEnabled($subscription);

        $validated = $request->validate(['outcome' => ['required', 'in:paid,failed']]);
        $subscription->update([
            'payment_payload' => array_merge($subscription->payment_payload ?? [], ['simulated_status' => $validated['outcome']]),
        ]);

        return redirect()->route('offres.payment.return', $subscription);
    }

    /**
     * Retour du client après paiement : l'état est toujours revérifié auprès du prestataire.
     */
    public function paymentReturn(Request $request, Subscription $subscription): View
    {
        $result = $this->subscriptions->confirm($subscription);
        $subscription->refresh()->load(['tenant', 'plan']);

        return view('subscriptions.offers.result', [
            'subscription' => $subscription,
            'result' => $result,
            'ownsSubscription' => $this->ownsSubscription($request, $subscription),
        ]);
    }

    /**
     * Notification serveur à serveur du prestataire (le contenu reçu n'est jamais cru sur parole).
     */
    public function notify(Request $request): Response
    {
        $gateway = $this->subscriptions->gateway();

        // Paystack signe ses notifications : une notification non signée est ignorée.
        if ($gateway instanceof PaystackGateway) {
            abort_unless($gateway->hasValidSignature($request), 401);
            $reference = (string) $request->input('data.reference', '');
        } else {
            $reference = (string) ($request->input('cpm_trans_id') ?? $request->input('transaction_id', ''));
        }

        $subscription = $reference !== '' ? Subscription::where('reference', $reference)->first() : null;

        if ($subscription) {
            try {
                $this->subscriptions->confirm($subscription);
            } catch (RuntimeException $exception) {
                report($exception);
            }
        }

        return response('OK');
    }

    public function retry(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_unless($this->ownsSubscription($request, $subscription) && ! $subscription->isPaid(), 403);

        return $this->redirectToPayment($subscription);
    }

    public function pdf(Request $request, Subscription $subscription, SubscriptionPdfService $pdfService): SymfonyResponse
    {
        abort_unless($subscription->isPaid() && $this->ownsSubscription($request, $subscription), 403);

        return self::pdfResponse($subscription, $pdfService);
    }

    /**
     * Télécharge le PDF enregistré, le génère s'il manque, sinon affiche la version imprimable.
     */
    public static function pdfResponse(Subscription $subscription, SubscriptionPdfService $pdfService): SymfonyResponse
    {
        $path = $subscription->pdf_path;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            $path = $pdfService->generate($subscription);
        }

        if ($path) {
            return Storage::disk('local')->download($path, 'Abonnement-'.$subscription->reference.'.pdf', ['Content-Type' => 'application/pdf']);
        }

        return response($pdfService->html($subscription, false));
    }

    private function redirectToPayment(Subscription $subscription): RedirectResponse
    {
        try {
            return redirect()->away($this->subscriptions->gateway()->initiate($subscription->load(['tenant', 'plan'])));
        } catch (RuntimeException $exception) {
            report($exception);

            return redirect()->route('offres.payment.return', $subscription)
                ->with('error', 'Le paiement en ligne est momentanément indisponible. Réessayez dans quelques instants.');
        }
    }

    private function ensureSimulationEnabled(Subscription $subscription): void
    {
        abort_unless($this->subscriptions->gateway()->name() === 'simulation' && ! $subscription->isPaid(), 404);
    }

    /**
     * L'inscrit (même session) ou un utilisateur de l'espace concerné.
     */
    private function ownsSubscription(Request $request, Subscription $subscription): bool
    {
        if ($request->session()->get('subscription_reference') === $subscription->reference) {
            return true;
        }

        return Auth::check() && (int) Auth::user()->tenant_id === (int) $subscription->tenant_id;
    }
}
