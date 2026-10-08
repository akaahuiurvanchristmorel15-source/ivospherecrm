<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\EmployeeSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): View
    {
        $query = User::with(['roles', 'domains', 'employee'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($roleSlug = $request->input('role')) {
            $query->whereHas('roles', function ($q) use ($roleSlug) {
                $q->where('slug', $roleSlug);
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::all();
        $domains = Domain::all();

        return view('admin.users.index', compact('users', 'roles', 'domains'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = Role::with('permissions')->get();
        $domains = Domain::where('is_active', true)->get();

        return view('admin.users.create_edit', [
            'user' => new User,
            'employee' => new Employee,
            'roles' => $roles,
            'domains' => $domains,
            'isEdit' => false,
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request, EmployeeSyncService $syncService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', Password::defaults()],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
            'domains' => ['nullable', 'array'],
            'domains.*' => ['exists:domains,id'],
            'all_domains' => ['boolean'],
            'is_active' => ['boolean'],
            'employee_code' => ['nullable', 'string', 'max:50', 'unique:employees,employee_code'],
            'position' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['nullable', 'date'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'all_domains' => $request->boolean('all_domains'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->roles()->sync($validated['roles']);

        if (! empty($validated['domains'])) {
            $user->domains()->sync($validated['domains']);
        }

        // Harmonisation automatique : chaque agent et responsable est un employé
        $syncService->syncUserToEmployee($user, [
            'employee_code' => $request->input('employee_code'),
            'position' => $request->input('position'),
            'department' => $request->input('department'),
            'hire_date' => $request->input('hire_date'),
        ]);

        ActivityLogger::log(
            action: 'creation_utilisateur',
            description: "A créé l'utilisateur {$user->name} ({$user->email}) avec profil employé associé",
            subject: $user
        );

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur {$user->name} et sa fiche collaborateur ont été enregistrés avec succès.");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $roles = Role::with('permissions')->get();
        $domains = Domain::where('is_active', true)->get();

        return view('admin.users.create_edit', [
            'user' => $user->load(['roles.permissions', 'domains', 'employee']),
            'employee' => $user->employee ?? new Employee,
            'roles' => $roles,
            'domains' => $domains,
            'isEdit' => true,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user, EmployeeSyncService $syncService): RedirectResponse
    {
        $employeeId = $user->employee?->id;
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', Password::defaults()],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
            'domains' => ['nullable', 'array'],
            'domains.*' => ['exists:domains,id'],
            'all_domains' => ['boolean'],
            'is_active' => ['boolean'],
            'employee_code' => ['nullable', 'string', 'max:50', Rule::unique('employees', 'employee_code')->ignore($employeeId)],
            'position' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['nullable', 'date'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->all_domains = $request->boolean('all_domains');
        $user->is_active = $request->boolean('is_active');

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $user->roles()->sync($validated['roles']);

        if (! empty($validated['domains'])) {
            $user->domains()->sync($validated['domains']);
        } else {
            $user->domains()->detach();
        }

        // Harmonisation automatique : chaque agent et responsable est un employé
        $syncService->syncUserToEmployee($user, [
            'employee_code' => $request->input('employee_code'),
            'position' => $request->input('position'),
            'department' => $request->input('department'),
            'hire_date' => $request->input('hire_date'),
        ]);

        ActivityLogger::log(
            action: 'modification_utilisateur',
            description: "A modifié l'utilisateur {$user->name} et synchronisé sa fiche collaborateur",
            subject: $user
        );

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur {$user->name} a été mis à jour.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $userName = $user->name;

        if ($user->employee) {
            $user->employee->update([
                'status' => 'inactif',
                'user_id' => null,
            ]);
        }

        $user->delete();

        ActivityLogger::log(
            action: 'suppression_utilisateur',
            description: "A supprimé l'utilisateur {$userName}"
        );

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur {$userName} a été supprimé.");
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        if ($user->employee) {
            $user->employee->update([
                'status' => $user->is_active ? 'actif' : 'inactif',
            ]);
        }

        ActivityLogger::log(
            action: 'changement_statut_utilisateur',
            description: 'A '.($user->is_active ? 'activé' : 'désactivé')." le compte de {$user->name}",
            subject: $user
        );

        return back()->with('success', 'Statut du compte et du profil collaborateur mis à jour.');
    }
}
