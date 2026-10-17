<?php

namespace App\Services\Subscriptions;

use App\Mail\SubscriptionConfirmed;
use App\Models\Domain;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Inscription d'un abonné, activation après paiement et renouvellement.
 */
class SubscriptionService
{
    public function __construct(private SubscriptionPdfService $pdfService) {}

    public function gateway(): PaymentGateway
    {
        // La simulation (paiement factice) n'est jamais utilisable en production.
        if (config('subscriptions.payment.provider') === 'simulation' && ! app()->isProduction()) {
            return app(SimulationGateway::class);
        }

        return match (config('subscriptions.payment.provider')) {
            'cinetpay' => app(CinetPayGateway::class),
            default => app(PaystackGateway::class),
        };
    }

    /**
     * Crée l'espace (en attente), son compte administrateur et la souscription à payer.
     *
     * @param  array{company_name: string, email: string, phone: string, city?: ?string, address?: ?string, admin_name: string, admin_email: string, password: string}  $data
     */
    public function register(SubscriptionPlan $plan, array $data, string $billingCycle): Subscription
    {
        return DB::transaction(function () use ($plan, $data, $billingCycle) {
            $tenant = Tenant::create([
                'name' => $data['company_name'],
                'slug' => $this->uniqueSlug($data['company_name']),
                'email' => $data['email'],
                'phone' => $data['phone'],
                'city' => $data['city'] ?? null,
                'country' => "Côte d'Ivoire",
                'address' => $data['address'] ?? null,
                'subscription_plan_id' => $plan->id,
                'status' => 'pending',
            ]);

            $admin = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'is_active' => true,
                'all_domains' => true,
            ]);

            $administratorRole = Role::where('slug', 'administrateur')->first();
            if ($administratorRole) {
                $admin->roles()->sync([$administratorRole->id]);
            }

            return $this->createPendingSubscription($tenant, $plan, $billingCycle);
        });
    }

    public function createPendingSubscription(Tenant $tenant, SubscriptionPlan $plan, string $billingCycle): Subscription
    {
        return Subscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => $billingCycle === 'yearly' ? 'yearly' : 'monthly',
            'amount' => $plan->priceFor($billingCycle),
            'currency' => config('subscriptions.payment.currency', 'XOF'),
            'status' => 'pending',
            'payment_provider' => $this->gateway()->name(),
        ]);
    }

    /**
     * Interroge le prestataire et active l'abonnement si le paiement est confirmé.
     */
    public function confirm(Subscription $subscription): PaymentResult
    {
        if ($subscription->isPaid()) {
            return new PaymentResult(PaymentResult::PAID, $subscription->payment_transaction_id, $subscription->payment_method, $subscription->amount);
        }

        $result = $this->gateway()->verify($subscription);

        if ($result->isPaid()) {
            $this->activate($subscription, $result);
        } elseif ($result->status === PaymentResult::FAILED) {
            $subscription->update(['status' => 'failed']);
        }

        return $result;
    }

    /**
     * Active l'abonnement payé (idempotent : un second appel ne prolonge pas deux fois).
     */
    public function activate(Subscription $subscription, PaymentResult $result): Subscription
    {
        $activated = DB::transaction(function () use ($subscription, $result) {
            $subscription = Subscription::lockForUpdate()->findOrFail($subscription->id);
            if ($subscription->isPaid()) {
                return null;
            }

            $tenant = Tenant::lockForUpdate()->findOrFail($subscription->tenant_id);
            $isFirstActivation = $tenant->status === 'pending';

            // Un renouvellement anticipé prolonge à partir de l'échéance en cours.
            $startsAt = $tenant->subscription_ends_at?->isFuture() ? $tenant->subscription_ends_at->copy() : now();
            $endsAt = $subscription->billing_cycle === 'yearly' ? $startsAt->copy()->addYear() : $startsAt->copy()->addMonth();

            $subscription->update([
                'status' => 'paid',
                'payment_transaction_id' => $result->transactionId,
                'payment_method' => $result->method,
                'payment_payload' => array_merge($subscription->payment_payload ?? [], ['verification' => $result->payload]),
                'paid_at' => now(),
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);

            $tenant->update([
                'status' => 'active',
                'subscription_plan_id' => $subscription->subscription_plan_id,
                'subscription_ends_at' => $endsAt,
            ]);

            if ($isFirstActivation) {
                $this->provision($tenant);
            }

            return $subscription;
        });

        $subscription = $activated ?? $subscription->fresh();

        if ($activated) {
            $this->pdfService->generate($activated);
            $this->sendConfirmation($activated->fresh(['tenant', 'plan']));
        }

        return $subscription;
    }

    /**
     * Prépare un espace neuf : paramètres de l'entreprise, activité et magasin par défaut.
     */
    private function provision(Tenant $tenant): void
    {
        $defaults = TenantContext::run(Tenant::MAIN_ID, fn () => [
            'currency' => Setting::get('currency', 'FCFA'),
            'default_tax_rate' => Setting::get('default_tax_rate', 18),
        ]);

        TenantContext::run($tenant->id, function () use ($tenant, $defaults) {
            Setting::set('company_name', $tenant->name);
            Setting::set('company_email', (string) $tenant->email);
            Setting::set('company_phone', (string) $tenant->phone);
            Setting::set('company_address', trim(($tenant->address ? $tenant->address.', ' : '').($tenant->city ?? ''), ', '));
            Setting::set('currency', (string) $defaults['currency'], 'finance');
            Setting::set('default_tax_rate', (string) $defaults['default_tax_rate'], 'finance', 'number');

            $domain = Domain::create([
                'name' => $tenant->name,
                'code' => 'PRINCIPAL',
                'description' => 'Activité principale',
                'status' => 'actif',
                'is_active' => true,
            ]);

            Warehouse::create([
                'domain_id' => $domain->id,
                'name' => 'Magasin principal',
                'code' => 'MAG-01',
            ]);
        });
    }

    private function sendConfirmation(Subscription $subscription): void
    {
        $admin = User::withoutGlobalScopes()->where('tenant_id', $subscription->tenant_id)->oldest('id')->first();
        $recipients = array_values(array_unique(array_filter([$subscription->tenant->email, $admin?->email])));

        try {
            Mail::to($recipients)->send(new SubscriptionConfirmed($subscription, $admin?->email));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'espace';
        $slug = $base;
        $suffix = 2;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
