<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Seconde étape de la connexion : code de l'application d'authentification ou code de récupération.
 */
class TwoFactorChallengeController extends Controller
{
    public const SESSION_KEY = 'login.two_factor';

    public function create(Request $request): View|RedirectResponse
    {
        if (! $this->pendingUser($request)) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request, TwoFactorAuthenticator $authenticator): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'La vérification a expiré. Saisissez à nouveau vos identifiants.']);
        }

        $validated = $request->validate([
            'code' => ['nullable', 'required_without:recovery_code', 'string', 'max:10'],
            'recovery_code' => ['nullable', 'required_without:code', 'string', 'max:20'],
        ], [
            'code.required_without' => 'Saisissez le code à 6 chiffres de votre application.',
            'recovery_code.required_without' => 'Saisissez un code de récupération.',
        ]);

        $field = filled($validated['recovery_code'] ?? null) ? 'recovery_code' : 'code';

        // 5 essais par minute pour ce compte : un code à 6 chiffres ne se devine pas par essais successifs.
        $throttleKey = 'two-factor:'.$user->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                $field => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }

        $isValid = $field === 'recovery_code'
            ? $authenticator->useRecoveryCode($user, $validated['recovery_code'])
            : $authenticator->verify($user, $validated['code']);

        if (! $isValid) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                $field => $field === 'recovery_code'
                    ? 'Ce code de récupération est invalide ou a déjà été utilisé.'
                    : 'Code invalide. Vérifiez l\'heure de votre téléphone et saisissez le code affiché.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        $remember = (bool) $request->session()->pull(self::SESSION_KEY.'.remember', false);
        $request->session()->forget(self::SESSION_KEY);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        ActivityLogger::log(
            action: 'connexion',
            description: $field === 'recovery_code'
                ? "Connexion de {$user->name} avec un code de récupération (".count($user->two_factor_recovery_codes ?? []).' restant(s))'
                : "Connexion réussie de {$user->name} ({$user->primary_role}) avec double authentification",
            subject: $user
        );

        $response = redirect()->intended(route('dashboard'));

        if ($field === 'recovery_code' && count($user->two_factor_recovery_codes ?? []) <= 2) {
            $response->with('warning', 'Il vous reste peu de codes de récupération : régénérez-les depuis « Mon compte ».');
        }

        return $response;
    }

    /**
     * Compte dont le mot de passe vient d'être vérifié, tant que la demande n'a pas expiré.
     */
    private function pendingUser(Request $request): ?User
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! is_array($pending) || ($pending['expires_at'] ?? 0) < now()->getTimestamp()) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }

        $user = User::find($pending['user_id'] ?? null);

        return $user && $user->is_active && $user->hasTwoFactorEnabled() ? $user : null;
    }
}
