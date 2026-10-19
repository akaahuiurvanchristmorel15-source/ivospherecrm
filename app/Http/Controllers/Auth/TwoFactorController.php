<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureTwoFactorIsConfigured;
use App\Services\ActivityLogger;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Activation, confirmation et désactivation de la double authentification depuis « Mon compte ».
 */
class TwoFactorController extends Controller
{
    /**
     * Génère la clé secrète : elle reste inactive tant qu'un premier code ne l'a pas confirmée.
     */
    public function store(Request $request, TwoFactorAuthenticator $authenticator): RedirectResponse
    {
        $request->validateWithBag('twoFactor', ['current_password' => ['required', 'current_password']], [
            'current_password.current_password' => 'Le mot de passe est incorrect.',
        ]);

        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return $this->backToProfile()->with('error', 'La double authentification est déjà activée.');
        }

        $user->forceFill([
            'two_factor_secret' => $authenticator->generateSecret(),
            'two_factor_recovery_codes' => $authenticator->generateRecoveryCodes(),
            'two_factor_confirmed_at' => null,
        ])->save();

        return $this->backToProfile();
    }

    public function confirm(Request $request, TwoFactorAuthenticator $authenticator): RedirectResponse
    {
        $validated = $request->validateWithBag('twoFactor', ['code' => ['required', 'string', 'max:10']], [
            'code.required' => 'Saisissez le code à 6 chiffres affiché par votre application.',
        ]);

        $user = $request->user();

        if (! $user->two_factor_secret || $user->hasTwoFactorEnabled()) {
            return $this->backToProfile();
        }

        if (! $authenticator->verify($user, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => 'Code invalide. Scannez à nouveau le QR code ou vérifiez l\'heure de votre téléphone.',
            ])->errorBag('twoFactor')->redirectTo(route('password.edit').'#double-authentification');
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();
        $request->session()->forget(EnsureTwoFactorIsConfigured::SESSION_KEY);

        ActivityLogger::log('two_factor_enabled', 'A activé la double authentification', $user);

        return $this->backToProfile()
            ->with('success', 'Double authentification activée. Conservez vos codes de récupération en lieu sûr.')
            ->with('two_factor_recovery_codes', $user->two_factor_recovery_codes);
    }

    public function regenerateRecoveryCodes(Request $request, TwoFactorAuthenticator $authenticator): RedirectResponse
    {
        $request->validateWithBag('twoFactor', ['current_password' => ['required', 'current_password']], [
            'current_password.current_password' => 'Le mot de passe est incorrect.',
        ]);

        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return $this->backToProfile();
        }

        $user->forceFill(['two_factor_recovery_codes' => $authenticator->generateRecoveryCodes()])->save();

        ActivityLogger::log('two_factor_recovery_codes', 'A régénéré ses codes de récupération', $user);

        return $this->backToProfile()
            ->with('success', 'Nouveaux codes de récupération générés : les anciens ne fonctionnent plus.')
            ->with('two_factor_recovery_codes', $user->two_factor_recovery_codes);
    }

    /**
     * Désactive la double authentification, ou annule une activation non confirmée.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            $request->validateWithBag('twoFactor', ['current_password' => ['required', 'current_password']], [
                'current_password.current_password' => 'Le mot de passe est incorrect.',
            ]);

            if ($user->requiresTwoFactor()) {
                return $this->backToProfile()->with('error', 'Votre rôle impose la double authentification : elle ne peut pas être désactivée.');
            }
        }

        $wasEnabled = $user->hasTwoFactorEnabled();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        if ($wasEnabled) {
            ActivityLogger::log('two_factor_disabled', 'A désactivé la double authentification', $user);

            return $this->backToProfile()->with('success', 'Double authentification désactivée.');
        }

        return $this->backToProfile();
    }

    private function backToProfile(): RedirectResponse
    {
        return redirect()->to(route('password.edit').'#double-authentification');
    }
}
