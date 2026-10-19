<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureTwoFactorIsConfigured;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        // Protection contre les essais de mots de passe en série : 5 tentatives par minute (e-mail + IP).
        $throttleKey = Str::transliterate(Str::lower($credentials['email']).'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Trop de tentatives de connexion. Réessayez dans {$seconds} secondes.",
            ]);
        }

        if (Auth::validate($credentials)) {
            RateLimiter::clear($throttleKey);

            /** @var User $user */
            $user = Auth::getLastAttempted();

            if (! $user->is_active) {
                throw ValidationException::withMessages([
                    'email' => 'Votre compte a été désactivé. Veuillez contacter un administrateur.',
                ]);
            }

            // Mot de passe correct : la session ne s'ouvre qu'après le code de l'application d'authentification.
            if ($user->hasTwoFactorEnabled()) {
                $request->session()->regenerate();
                $request->session()->put(TwoFactorChallengeController::SESSION_KEY, [
                    'user_id' => $user->id,
                    'remember' => $remember,
                    'expires_at' => now()->addMinutes(10)->getTimestamp(),
                ]);

                return redirect()->route('two-factor.challenge');
            }

            Auth::login($user, $remember);
            $request->session()->regenerate();

            if ($user->requiresTwoFactor()) {
                $request->session()->put(EnsureTwoFactorIsConfigured::SESSION_KEY, true);
            }

            ActivityLogger::log(
                action: 'connexion',
                description: "Connexion réussie de {$user->name} ({$user->primary_role})",
                subject: $user
            );

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        throw ValidationException::withMessages([
            'email' => 'Les identifiants fournis ne correspondent à aucun compte actif.',
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            ActivityLogger::log(
                action: 'deconnexion',
                description: "Déconnexion de {$user->name}",
                subject: $user
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
