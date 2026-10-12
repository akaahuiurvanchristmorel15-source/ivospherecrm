<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('auth.password');
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
