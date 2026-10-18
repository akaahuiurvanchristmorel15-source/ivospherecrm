<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
    /**
     * Rôles fournis par la plateforme (ils ne peuvent pas être supprimés).
     *
     * @var list<string>
     */
    public const SYSTEM_ROLES = [
        'administrateur', 'responsable', 'responsable_rh', 'responsable_commercial', 'responsable_financiere',
        'responsable_communication', 'agent_commercial_terrain', 'agent_caissier_vendeur', 'agent_technique',
        'agent_monetique', 'agent_cyber',
    ];

    /**
     * Display the permissions matrix.
     */
    public function index(): View
    {
        $roles = Role::with('permissions')->withCount('users')->get();
        $permissions = Permission::all()->groupBy('group');
        $totalPermissionsCount = Permission::count();

        return view('admin.roles.index', compact('roles', 'permissions', 'totalPermissionsCount'));
    }

    /**
     * Crée un nouveau rôle, vide ou en reprenant les permissions d'un rôle existant.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'copy_from' => ['nullable', 'exists:roles,id'],
        ], [
            'name.unique' => 'Un rôle porte déjà ce nom.',
        ], ['name' => 'nom du rôle']);

        $baseSlug = Str::slug($validated['name'], '_') ?: 'role';
        $slug = $baseSlug;
        for ($suffix = 2; Role::where('slug', $slug)->exists(); $suffix++) {
            $slug = $baseSlug.'_'.$suffix;
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['copy_from'])) {
            $role->permissions()->sync(Role::findOrFail($validated['copy_from'])->permissions()->pluck('permissions.id'));
        }

        ActivityLogger::log(action: 'creation_role', description: "A créé le rôle {$role->name}", subject: $role);

        return redirect()->route('admin.roles.index', ['role' => $role->id])
            ->with('success', "Le rôle {$role->name} a été créé. Activez maintenant ses permissions.");
    }

    /**
     * Supprime un rôle créé par l'administrateur, s'il n'est attribué à personne.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->slug, self::SYSTEM_ROLES, true)) {
            return back()->with('error', 'Les rôles fournis par la plateforme ne peuvent pas être supprimés.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', "Le rôle {$role->name} est encore attribué à des utilisateurs : changez d'abord leur rôle.");
        }

        $role->permissions()->detach();
        $role->delete();

        ActivityLogger::log(action: 'suppression_role', description: "A supprimé le rôle {$role->name}");

        return redirect()->route('admin.roles.index')->with('success', "Le rôle {$role->name} a été supprimé.");
    }

    /**
     * Update permissions assigned to a role.
     */
    public function updateRolePermissions(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $permissionIds = $validated['permissions'] ?? [];
        $role->permissions()->sync($permissionIds);

        ActivityLogger::log(
            action: 'mise_a_jour_permissions',
            description: "A mis à jour les permissions accordées au rôle {$role->name}",
            subject: $role,
            properties: ['permissions_count' => count($permissionIds)]
        );

        return redirect()->route('admin.roles.index', ['role' => $role->id])
            ->with('success', "Les permissions du rôle {$role->name} ont été mises à jour avec succès.");
    }
}
