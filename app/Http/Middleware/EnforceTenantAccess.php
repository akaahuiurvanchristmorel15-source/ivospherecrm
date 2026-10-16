<?php

namespace App\Http\Middleware;

use App\Support\TenantAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Espaces abonnés : seules les pages des modules du plan sont accessibles, et seulement
 * tant que l'abonnement est actif. L'espace principal (Ivosphere) n'est pas concerné.
 */
class EnforceTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->belongsToMainTenant()) {
            return $next($request);
        }

        TenantAccess::flush();
        $tenant = TenantAccess::tenantOf($user);
        if (! $tenant) {
            auth()->logout();

            return redirect()->route('login')->withErrors(['email' => "Ce compte n'est rattaché à aucun espace."]);
        }

        if ($tenant->status === 'suspended') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Votre espace a été suspendu. Contactez IVOSPHERE pour le réactiver.']);
        }

        $routeName = (string) $request->route()?->getName();

        if ($routeName !== '' && TenantAccess::allows($routeName, $user)) {
            return $next($request);
        }

        if (! $tenant->hasActiveSubscription()) {
            return redirect()->route('space.subscription')
                ->with('warning', "Votre abonnement n'est pas actif. Renouvelez-le pour retrouver l'accès à votre espace.");
        }

        // Pages d'accueil d'Ivosphere : redirection vers l'accueil de l'espace abonné.
        if (in_array($routeName, ['home', 'dashboard', 'menu.index'], true)) {
            return redirect()->route('space.home');
        }

        abort(403, "Cette fonctionnalité n'est pas incluse dans votre abonnement.");
    }
}
