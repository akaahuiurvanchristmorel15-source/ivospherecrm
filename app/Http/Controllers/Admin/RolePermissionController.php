<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
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
