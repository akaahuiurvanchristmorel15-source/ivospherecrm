<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
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

        if (! empty($permissions) && ! $user->hasAnyPermission($permissions)) {
            abort(403, 'Accès interdit : vous ne possédez pas les permissions nécessaires pour effectuer cette action.');
        }

        return $next($request);
    }
}
