<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

/**
 * Photo de profil : chaque utilisateur gère la sienne depuis « Mon compte ».
 */
class ProfilePhotoController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(3072)],
        ], [
            'avatar.required' => 'Choisissez une photo.',
            'avatar.max' => 'La photo doit faire moins de 3 Mo.',
        ]);

        $user = $request->user();
        $previous = $user->avatar;

        $path = $request->file('avatar')->storeAs('avatars', $user->id.'-'.Str::random(16).'.'.$request->file('avatar')->extension(), 'public');
        $user->forceFill(['avatar' => $path])->save();

        if ($previous && $previous !== $path) {
            Storage::disk('public')->delete($previous);
        }

        ActivityLogger::log('photo_profil', 'A changé sa photo de profil');

        return redirect()->route('password.edit')->with('success', 'Votre photo de profil a été mise à jour.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->forceFill(['avatar' => null])->save();
        }

        return redirect()->route('password.edit')->with('success', 'Votre photo de profil a été retirée.');
    }
}
