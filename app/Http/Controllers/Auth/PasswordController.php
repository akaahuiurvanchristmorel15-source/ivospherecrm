<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Services\ActivityLogger;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(Request $request, TwoFactorAuthenticator $authenticator): View
    {
        $user = $request->user();
        $isTwoFactorPending = $user->two_factor_secret && ! $user->hasTwoFactorEnabled();

        return view('auth.password', [
            'twoFactorQrCode' => $isTwoFactorPending ? $authenticator->qrCodeSvg($user) : null,
            'twoFactorSecret' => $isTwoFactorPending ? $user->two_factor_secret : null,
        ]);
    }

    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        // Nouvel identifiant de session : une session volée avant le changement devient inutilisable
        $request->session()->regenerate();

        ActivityLogger::log('password_changed', 'A modifié son mot de passe');

        return redirect()->route('password.edit')->with('success', 'Votre mot de passe a été modifié.');
    }
}
