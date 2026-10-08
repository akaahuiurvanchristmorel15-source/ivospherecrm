<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\EmployeeSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Employee::query()->with(['domain', 'user.roles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->orderBy('first_name')->paginate(15)->withQueryString();
        $domains = Domain::orderBy('name')->get();

        return view('rh.employees.index', compact('employees', 'domains'));
    }

    public function create(): View
    {
        $domains = Domain::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $unlinkedUsers = User::whereDoesntHave('employee')->where('is_active', true)->orderBy('name')->get();

        $employee = new Employee([
            'employee_code' => Employee::generateEmployeeCode(),
            'hire_date' => now(),
            'contract_type' => 'CDI',
            'status' => 'actif',
        ]);

        return view('rh.employees.create_edit', [
            'isEdit' => false,
            'employee' => $employee,
            'domains' => $domains,
            'roles' => $roles,
            'unlinkedUsers' => $unlinkedUsers,
        ]);
    }

    public function store(Request $request, EmployeeSyncService $syncService): RedirectResponse
    {
        if (! $request->filled('employee_code')) {
            $request->merge(['employee_code' => Employee::generateEmployeeCode()]);
        }

        $validated = $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'employee_code' => 'required|string|max:50|unique:employees,employee_code',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:M,F,Autre',
            'address' => 'nullable|string',
            'national_id' => 'nullable|string',
            'hire_date' => 'nullable|date',
            'contract_type' => 'nullable|string',
            'salary' => 'nullable|numeric',
            'status' => 'required|in:actif,inactif,suspendu',
            'user_id' => 'nullable|exists:users,id',
            'create_user_account' => 'nullable|boolean',
            'role_id' => 'nullable|exists:roles,id',
            'user_password' => 'nullable|string|min:6',
        ]);

        $employee = Employee::create([
            'domain_id' => $validated['domain_id'],
            'employee_code' => $validated['employee_code'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'position' => $validated['position'] ?? null,
            'department' => $validated['department'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'national_id' => $validated['national_id'] ?? null,
            'hire_date' => $validated['hire_date'] ?? null,
            'contract_type' => $validated['contract_type'] ?? 'CDI',
            'salary' => $validated['salary'] ?? 0,
            'status' => $validated['status'],
            'user_id' => $validated['user_id'] ?? null,
        ]);

        // Si demandé ou si un rôle est sélectionné, synchroniser/créer le compte utilisateur
        if ($request->boolean('create_user_account') || $request->filled('role_id') || ! empty($validated['user_id'])) {
            $syncService->syncEmployeeToUser($employee, [
                'create_user' => $request->boolean('create_user_account') || $request->filled('role_id'),
                'role_id' => $request->input('role_id'),
                'password' => $request->input('user_password'),
            ]);
        }

        ActivityLogger::log('creation_employe', "Création de l'employé {$employee->full_name}", $employee);

        return redirect()->route('rh.employees.index')->with('success', 'Employé créé avec succès.');
    }

    public function show(Employee $employee): View
    {
        $employee->load([
            'contracts',
            'leaveRequests.approver',
            'attendances' => function ($q) {
                $q->latest('date')->take(10);
            },
            'documents',
            'domain',
            'user.roles',
        ]);

        return view('rh.employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        $domains = Domain::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $unlinkedUsers = User::whereDoesntHave('employee', function ($q) use ($employee) {
            $q->where('id', '!=', $employee->id);
        })->where('is_active', true)->orderBy('name')->get();

        return view('rh.employees.create_edit', [
            'isEdit' => true,
            'employee' => $employee->load('user.roles'),
            'domains' => $domains,
            'roles' => $roles,
            'unlinkedUsers' => $unlinkedUsers,
        ]);
    }

    public function update(Request $request, Employee $employee, EmployeeSyncService $syncService): RedirectResponse
    {
        $validated = $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'employee_code' => 'nullable|string',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,'.$employee->id,
            'phone' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:M,F,Autre',
            'address' => 'nullable|string',
            'national_id' => 'nullable|string',
            'hire_date' => 'nullable|date',
            'contract_type' => 'nullable|string',
            'salary' => 'nullable|numeric',
            'status' => 'required|in:actif,inactif,suspendu',
            'user_id' => 'nullable|exists:users,id',
            'create_user_account' => 'nullable|boolean',
            'role_id' => 'nullable|exists:roles,id',
            'user_password' => 'nullable|string|min:6',
        ]);

        $employee->update([
            'domain_id' => $validated['domain_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'position' => $validated['position'] ?? null,
            'department' => $validated['department'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'national_id' => $validated['national_id'] ?? null,
            'hire_date' => $validated['hire_date'] ?? null,
            'contract_type' => $validated['contract_type'] ?? 'CDI',
            'salary' => $validated['salary'] ?? 0,
            'status' => $validated['status'],
            'user_id' => $validated['user_id'] ?? $employee->user_id,
        ]);

        if ($employee->user || $request->boolean('create_user_account') || $request->filled('role_id')) {
            $syncService->syncEmployeeToUser($employee, [
                'create_user' => $request->boolean('create_user_account') || $request->filled('role_id'),
                'role_id' => $request->input('role_id'),
                'password' => $request->input('user_password'),
            ]);
        }

        ActivityLogger::log('modification_employe', "Modification de l'employé {$employee->full_name}", $employee);

        return redirect()->route('rh.employees.index')->with('success', 'Employé mis à jour avec succès.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->update(['status' => 'inactif']);
        if ($employee->user) {
            $employee->user->update(['is_active' => false]);
        }

        ActivityLogger::log('desactivation_employe', "Désactivation de l'employé {$employee->full_name}", $employee);

        return redirect()->route('rh.employees.index')->with('success', 'Employé désactivé avec succès.');
    }
}
