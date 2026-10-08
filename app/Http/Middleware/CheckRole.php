<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')->withErrors(['email' => 'Votre compte est désactivé.']);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (! empty($roles) && ! $user->hasAnyRole($roles)) {
            abort(403, "Accès interdit : vous n'avez pas le rôle requis pour accéder à cette page.");
        }

        return $next($request);
    }
}
