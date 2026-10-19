<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Un compte dont le rôle impose la double authentification (administrateur) et qui ne l'a pas encore
 * activée est cantonné à l'écran « Mon compte » jusqu'à son activation, dès sa connexion.
 */
class EnsureTwoFactorIsConfigured
{
    public const SESSION_KEY = 'two_factor.setup_required';

    /**
     * Pages encore accessibles tant que la double authentification n'est pas activée.
     *
     * @var list<string>
     */
    private const ALLOWED_ROUTES = ['password.edit', 'two-factor.enable', 'two-factor.confirm', 'two-factor.disable', 'logout'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $request->session()->get(self::SESSION_KEY)) {
            return $next($request);
        }

        if ($user->hasTwoFactorEnabled() || ! $user->requiresTwoFactor()) {
            $request->session()->forget(self::SESSION_KEY);

            return $next($request);
        }

        if ($request->routeIs(...self::ALLOWED_ROUTES) || $request->is('livewire*')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Activez la double authentification depuis « Mon compte » pour continuer.');
        }

        return redirect()->route('password.edit')
            ->with('warning', 'Votre rôle impose la double authentification : activez-la pour accéder à l\'application.');
    }
}
