<?php

namespace App\Services\Subscriptions;

use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Throwable;

/**
 * Récapitulatif PDF de l'abonnement : plan, modules, période, paiement et compte administrateur.
 */
class SubscriptionPdfService
{
    public function html(Subscription $subscription, bool $isPdf = true): string
    {
        $subscription->loadMissing(['tenant', 'plan']);

        $ivosphere = TenantContext::run(Tenant::MAIN_ID, fn () => [
            'name' => Setting::get('company_name', 'GROUPE IVOSPHERE'),
            'email' => Setting::get('company_email', 'contact@ivosphere.com'),
            'phone' => Setting::get('company_phone'),
            'address' => Setting::get('company_address'),
        ]);

        $admin = $subscription->tenant->users()->withoutGlobalScopes()
            ->where('tenant_id', $subscription->tenant_id)
            ->oldest('id')
            ->first();

        return view('subscriptions.pdf', [
            'subscription' => $subscription,
            'tenant' => $subscription->tenant,
            'plan' => $subscription->plan,
            'admin' => $admin,
            'ivosphere' => $ivosphere,
            'isPdf' => $isPdf,
        ])->render();
    }

    /**
     * Génère et enregistre le PDF. Renvoie son chemin (disque local), ou null si Chromium est indisponible.
     */
    public function generate(Subscription $subscription): ?string
    {
        try {
            $pdf = $this->configureBrowsershot(Browsershot::html($this->html($subscription)))
                ->format('A4')
                ->margins(12, 12, 12, 12)
                ->showBackground()
                ->emulateMedia('print')
                ->pdf();
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }

        $path = 'subscriptions/Abonnement-'.$subscription->reference.'.pdf';
        Storage::disk('local')->put($path, $pdf);
        $subscription->forceFill(['pdf_path' => $path])->save();

        return $path;
    }

    /**
     * Applique les chemins Node.js / npm / Chromium définis dans config/services.php.
     */
    private function configureBrowsershot(Browsershot $browsershot): Browsershot
    {
        $config = config('services.browsershot');

        if (filled($config['node_binary'] ?? null)) {
            $browsershot->setNodeBinary($config['node_binary']);
        }

        if (filled($config['npm_binary'] ?? null)) {
            $browsershot->setNpmBinary($config['npm_binary']);
        }

        if (filled($config['chrome_path'] ?? null)) {
            $browsershot->setChromePath($config['chrome_path']);
        }

        if (filled($config['node_modules_path'] ?? null)) {
            $browsershot->setNodeModulePath($config['node_modules_path']);
        }

        return $browsershot;
    }
}
