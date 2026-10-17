<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInvitation;
use App\Services\ActivityLogger;
use App\Support\TenantAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserInvitationController extends Controller
{
    /**
     * Durées de validité proposées (en jours).
     */
    public const VALIDITY_DAYS = [1, 3, 7, 30];

    /**
     * Génère un lien d'inscription à usage unique pour un futur utilisateur.
     */
    public function store(Request $request): RedirectResponse
    {
        $tenant = TenantAccess::tenantOf($request->user());
        $maxUsers = $tenant && ! $tenant->is_main ? $tenant->plan?->max_users : null;
        if ($maxUsers !== null && User::count() >= $maxUsers) {
            return back()->with('error', "Votre abonnement {$tenant->plan->name} est limité à {$maxUsers} utilisateur(s). Passez à un plan supérieur pour en ajouter.");
        }

        $validated = $request->validate([
            'invite_name' => ['nullable', 'string', 'max:255'],
            'invite_email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'invite_role' => ['required', 'exists:roles,id'],
            'invite_domains' => ['nullable', 'array'],
            'invite_domains.*' => ['exists:domains,id'],
            'invite_all_domains' => ['boolean'],
            'invite_validity' => ['required', 'integer', Rule::in(self::VALIDITY_DAYS)],
        ], [
            'invite_email.unique' => 'Un compte existe déjà avec cette adresse e-mail.',
            'invite_role.required' => 'Choisissez le rôle du futur utilisateur.',
        ]);

        [$invitation, $token] = UserInvitation::generate([
            'name' => $validated['invite_name'] ?? null,
            'email' => isset($validated['invite_email']) ? mb_strtolower($validated['invite_email']) : null,
            'role_ids' => [(int) $validated['invite_role']],
            'domain_ids' => array_map('intval', $validated['invite_domains'] ?? []),
            'all_domains' => $request->boolean('invite_all_domains'),
            'created_by' => $request->user()->id,
            'expires_at' => now()->addDays((int) $validated['invite_validity']),
        ]);

        ActivityLogger::log(
            action: 'invitation_utilisateur',
            description: "A généré un lien d'inscription".($invitation->email ? " pour {$invitation->email}" : ''),
            subject: $invitation
        );

        return redirect()->route('admin.users.index')
            ->with('invitation_link', route('invitations.show', $token))
            ->with('invitation_expires', $invitation->expires_at->format('d/m/Y à H:i'));
    }

    /**
     * Révoque un lien non encore utilisé.
     */
    public function destroy(UserInvitation $invitation): RedirectResponse
    {
        abort_if($invitation->used_at !== null, 403, 'Ce lien a déjà été utilisé.');

        $invitation->delete();

        ActivityLogger::log(
            action: 'revocation_invitation',
            description: "A révoqué un lien d'inscription".($invitation->email ? " ({$invitation->email})" : '')
        );

        return back()->with('success', "Le lien d'inscription a été révoqué : il n'est plus utilisable.");
    }
}
