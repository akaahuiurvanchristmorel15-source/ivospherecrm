<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserInvitation;
use App\Rules\RealEmailAddress;
use App\Services\EmployeeSyncService;
use App\Services\Subscriptions\GoogleEmailVerifier;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Inscription d'un collaborateur via un lien à usage unique généré par l'administrateur.
 */
class InvitationController extends Controller
{
    private const GOOGLE_IDENTITY = 'invitation_google_identity';

    public function show(Request $request, string $token, GoogleEmailVerifier $googleVerifier): View|Response
    {
        $invitation = UserInvitation::findUsable($token);

        if (! $invitation) {
            return response()->view('auth.invitation', ['invitation' => null], 410);
        }

        $googleIdentity = $this->googleIdentityFor($request, $invitation);

        // Lien réservé à une autre adresse que celle confirmée par Google
        if ($googleIdentity && $invitation->email && $googleIdentity['email'] !== $invitation->email) {
            $request->session()->forget(self::GOOGLE_IDENTITY);
            $googleIdentity = null;
            session()->now('error', "Ce lien est réservé à l'adresse {$invitation->email}. Connectez-vous à Google avec cette adresse.");
        }

        return view('auth.invitation', [
            'invitation' => $invitation,
            'token' => $token,
            'roles' => $invitation->roles(),
            'spaceName' => Tenant::find($invitation->tenant_id)?->name ?? 'IVOSPHERE',
            'googleEnabled' => $googleVerifier->isConfigured(),
            'googleIdentity' => $googleIdentity,
        ]);
    }

    /**
     * Envoie le collaborateur sur Google pour confirmer son adresse e-mail.
     */
    public function googleRedirect(Request $request, string $token, GoogleEmailVerifier $googleVerifier): RedirectResponse|Response
    {
        abort_unless($googleVerifier->isConfigured(), 404);

        $invitation = UserInvitation::findUsable($token);
        if (! $invitation) {
            return response()->view('auth.invitation', ['invitation' => null], 410);
        }

        $state = Str::random(40);
        $request->session()->put(GoogleEmailVerifier::PENDING_SESSION_KEY, [
            'state' => $state,
            'identity_key' => self::GOOGLE_IDENTITY,
            'identity_extra' => ['invitation' => $invitation->token_hash],
            'return_url' => route('invitations.show', $token),
        ]);

        return redirect()->away($googleVerifier->authorizationUrl($state));
    }

    public function googleForget(Request $request, string $token): RedirectResponse
    {
        $request->session()->forget(self::GOOGLE_IDENTITY);

        return redirect()->route('invitations.show', $token);
    }

    public function register(Request $request, string $token, EmployeeSyncService $syncService): RedirectResponse|Response
    {
        $invitation = UserInvitation::findUsable($token);

        if (! $invitation) {
            return response()->view('auth.invitation', ['invitation' => null], 410);
        }

        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => array_filter([
                'required', 'string', 'email:rfc,strict', 'max:255', new RealEmailAddress, 'unique:users,email',
                $invitation->email ? 'in:'.$invitation->email : null,
            ]),
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'email.unique' => 'Un compte existe déjà avec cette adresse e-mail.',
            'email.in' => "Ce lien est réservé à l'adresse {$invitation->email}.",
        ]);

        $googleIdentity = $this->googleIdentityFor($request, $invitation);
        $emailVerified = ($googleIdentity && $googleIdentity['email'] === $validated['email']) || $invitation->email !== null;

        $user = TenantContext::run((int) $invitation->tenant_id, function () use ($invitation, $validated, $syncService, $emailVerified) {
            $tenant = Tenant::find($invitation->tenant_id);
            $maxUsers = $tenant && ! $tenant->is_main ? $tenant->plan?->max_users : null;
            if ($maxUsers !== null && User::count() >= $maxUsers) {
                return null;
            }

            return DB::transaction(function () use ($invitation, $validated, $syncService, $emailVerified) {
                // Le lien ne sert qu'une fois : il est réservé avant la création du compte (deux envois simultanés ne passent pas).
                if (! $invitation->claim()) {
                    return false;
                }

                $user = User::create([
                    'tenant_id' => $invitation->tenant_id,
                    'name' => $validated['name'],
                    'email' => mb_strtolower($validated['email']),
                    'phone' => $validated['phone'] ?? null,
                    'password' => $validated['password'],
                    'all_domains' => $invitation->all_domains,
                    'is_active' => true,
                ]);

                $invitation->forceFill(['user_id' => $user->id])->saveQuietly();
                $user->forceFill(['email_verified_at' => $emailVerified ? now() : null])->save();
                $user->roles()->sync($invitation->role_ids);
                if (! empty($invitation->domain_ids)) {
                    $user->domains()->sync($invitation->domain_ids);
                }

                $syncService->syncUserToEmployee($user);

                return $user;
            });
        });

        if ($user === null) {
            return back()->withInput()->with('error', "Le nombre maximal d'utilisateurs de cet espace est atteint. Contactez votre administrateur.");
        }

        if ($user === false) {
            return response()->view('auth.invitation', ['invitation' => null], 410);
        }

        $request->session()->forget(self::GOOGLE_IDENTITY);

        return redirect()->route('login')
            ->withInput(['email' => $user->email])
            ->with('success', 'Votre compte a été créé. Connectez-vous avec votre adresse e-mail et votre mot de passe.');
    }

    /**
     * Identité Google confirmée pour ce lien précis (une vérification faite pour un autre lien n'est pas reprise).
     *
     * @return array{email: string, name: string, invitation: string}|null
     */
    private function googleIdentityFor(Request $request, UserInvitation $invitation): ?array
    {
        $identity = $request->session()->get(self::GOOGLE_IDENTITY);

        return is_array($identity) && ($identity['invitation'] ?? null) === $invitation->token_hash ? $identity : null;
    }
}
