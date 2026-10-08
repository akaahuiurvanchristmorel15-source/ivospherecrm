<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeSyncService
{
    /**
     * Synchronise un compte utilisateur vers une fiche employé correspondante.
     * Si l'employé n'existe pas, il est automatiquement créé avec matricule, poste et département.
     */
    public function syncUserToEmployee(User $user, array $extra = []): Employee
    {
        // 1. Chercher la fiche employé existante par user_id ou email
        $employee = Employee::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        // Séparation prénom / nom
        $nameParts = preg_split('/\s+/', trim($user->name), 2);
        $firstName = $nameParts[0] ?? 'Collaborateur';
        $lastName = ! empty($nameParts[1]) ? $nameParts[1] : $firstName;

        // Détection du rôle principal
        $primaryRole = $user->roles()->first() ?? $user->roles->first();
        $roleSlug = $primaryRole?->slug ?? '';
        $roleName = $primaryRole?->name ?? 'Collaborateur';

        // Déduction automatique du poste si non renseigné
        $position = ! empty($extra['position']) ? $extra['position'] : ($employee?->position ?: $this->resolvePosition($roleSlug, $roleName));

        // Déduction automatique du département si non renseigné
        $department = ! empty($extra['department']) ? $extra['department'] : ($employee?->department ?: $this->resolveDepartment($roleSlug));

        // Déduction du domaine
        $domainId = ! empty($extra['domain_id'])
            ? (int) $extra['domain_id']
            : ($employee?->domain_id ?: ($user->domains()->first()?->id ?? Domain::where('is_active', true)->first()?->id ?? 1));

        // Code employé / Matricule
        $employeeCode = ! empty($extra['employee_code'])
            ? $extra['employee_code']
            : ($employee?->employee_code ?: $this->generateUniqueEmployeeCode($user, $roleSlug));

        $attributes = [
            'user_id' => $user->id,
            'domain_id' => $domainId,
            'employee_code' => $employeeCode,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user->email,
            'phone' => $user->phone ?? $employee?->phone,
            'position' => $position,
            'department' => $department,
            'status' => $user->is_active ? 'actif' : 'inactif',
            'hire_date' => ! empty($extra['hire_date']) ? $extra['hire_date'] : ($employee?->hire_date ?? now()->toDateString()),
            'contract_type' => ! empty($extra['contract_type']) ? $extra['contract_type'] : ($employee?->contract_type ?? 'CDI'),
            'salary' => isset($extra['salary']) ? (float) $extra['salary'] : ($employee?->salary ?? 0),
        ];

        if ($employee) {
            $employee->update($attributes);
        } else {
            $employee = Employee::create($attributes);
        }

        return $employee;
    }

    /**
     * Synchronise une fiche employé vers un compte utilisateur (ou le crée si demandé).
     */
    public function syncEmployeeToUser(Employee $employee, array $credentials = []): ?User
    {
        $user = null;
        if ($employee->user_id) {
            $user = User::find($employee->user_id);
        }

        if (! $user && ! empty($employee->email)) {
            $user = User::where('email', $employee->email)->first();
        }

        if ($user) {
            $user->name = $employee->full_name;
            $user->email = $employee->email;
            if (! empty($employee->phone)) {
                $user->phone = $employee->phone;
            }
            $user->is_active = ($employee->status === 'actif');

            if (! empty($credentials['password'])) {
                $user->password = Hash::make($credentials['password']);
            }

            $user->save();

            if (! empty($credentials['role_id'])) {
                $user->roles()->sync([(int) $credentials['role_id']]);
            }

            if ($employee->domain_id && ! $user->canAccessDomain($employee->domain_id)) {
                $user->domains()->syncWithoutDetaching([$employee->domain_id]);
            }

            if ($employee->user_id !== $user->id) {
                $employee->update(['user_id' => $user->id]);
            }

            return $user;
        }

        // Si l'utilisateur n'existe pas et que la création est demandée
        if (! empty($credentials['create_user'])) {
            $roleId = ! empty($credentials['role_id']) ? (int) $credentials['role_id'] : $this->resolveDefaultRoleId($employee);

            $user = User::create([
                'name' => $employee->full_name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'password' => Hash::make($credentials['password'] ?? 'Ivosphere2026@'),
                'is_active' => ($employee->status === 'actif'),
                'all_domains' => false,
            ]);

            if ($roleId) {
                $user->roles()->sync([$roleId]);
            }

            if ($employee->domain_id) {
                $user->domains()->sync([$employee->domain_id]);
            }

            $employee->update(['user_id' => $user->id]);

            return $user;
        }

        return null;
    }

    /**
     * Génère un matricule employé unique basé sur le rôle ou l'ID.
     */
    protected function generateUniqueEmployeeCode(User $user, string $roleSlug): string
    {
        $prefix = match (true) {
            $roleSlug === 'administrateur' => 'DIR',
            str_starts_with($roleSlug, 'responsable') => 'MGR',
            $roleSlug === 'agent_commercial_terrain' => 'COM',
            $roleSlug === 'agent_caissier_vendeur' => 'POS',
            $roleSlug === 'agent_technique' => 'TEC',
            $roleSlug === 'agent_monetique' => 'MON',
            $roleSlug === 'agent_cyber' => 'CYB',
            str_starts_with($roleSlug, 'agent_') => 'AGT',
            default => 'EMP',
        };

        $code = 'EMP-'.$prefix.'-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT);
        $counter = 1;
        while (Employee::where('employee_code', $code)->exists()) {
            $code = 'EMP-'.$prefix.'-'.str_pad((string) $user->id, 3, '0', STR_PAD_LEFT).'-'.$counter;
            $counter++;
        }

        return $code;
    }

    /**
     * Résout l'intitulé de poste par défaut selon le rôle RBAC.
     */
    protected function resolvePosition(string $roleSlug, string $roleName): string
    {
        return match ($roleSlug) {
            'administrateur' => 'Directeur Général',
            'responsable_rh' => 'Responsable Ressources Humaines',
            'responsable_commercial' => 'Responsable Commercial',
            'responsable_financier' => 'Responsable Financier & Comptable',
            'responsable_communication' => 'Responsable Communication & Marketing',
            'responsable' => 'Responsable d\'Exploitation',
            'agent_commercial_terrain' => 'Agent Commercial Terrain',
            'agent_caissier_vendeur' => 'Agent Caissier & Vendeur',
            'agent_technique' => 'Agent Technique & Impression',
            'agent_monetique' => 'Agent Monétique & Services',
            'agent_cyber' => 'Agent Cybercafé & Reprographie',
            default => $roleName,
        };
    }

    /**
     * Résout le département par défaut selon le rôle.
     */
    protected function resolveDepartment(string $roleSlug): string
    {
        return match ($roleSlug) {
            'administrateur' => 'Direction Générale',
            'responsable_rh' => 'Ressources Humaines',
            'responsable_commercial', 'agent_commercial_terrain', 'agent_caissier_vendeur' => 'Pôle Commercial',
            'responsable_financier' => 'Direction Financière',
            'responsable_communication' => 'Communication & Marketing',
            'agent_technique' => 'Pôle Technique & Ateliers',
            'agent_monetique' => 'Pôle Monétique & Flux',
            'agent_cyber' => 'Pôle Cybercafé & Services',
            default => 'Exploitation Générale',
        };
    }

    /**
     * Résout le rôle par défaut selon l'intitulé du poste ou département de l'employé.
     */
    protected function resolveDefaultRoleId(Employee $employee): ?int
    {
        $text = strtolower($employee->position.' '.$employee->department);

        $slug = match (true) {
            str_contains($text, 'direct') || str_contains($text, 'admin') => 'administrateur',
            str_contains($text, 'rh') || str_contains($text, 'ressource') => 'responsable_rh',
            str_contains($text, 'commercial') && str_contains($text, 'responsable') => 'responsable_commercial',
            str_contains($text, 'finance') || str_contains($text, 'comptab') => 'responsable_financier',
            str_contains($text, 'communication') => 'responsable_communication',
            str_contains($text, 'responsable') || str_contains($text, 'manager') => 'responsable',
            str_contains($text, 'caisse') || str_contains($text, 'vendeur') => 'agent_caissier_vendeur',
            str_contains($text, 'commercial') || str_contains($text, 'terrain') => 'agent_commercial_terrain',
            str_contains($text, 'techn') || str_contains($text, 'print') || str_contains($text, 'atelier') => 'agent_technique',
            str_contains($text, 'monet') || str_contains($text, 'tpe') => 'agent_monetique',
            str_contains($text, 'cyber') || str_contains($text, 'repro') => 'agent_cyber',
            default => 'agent_commercial_terrain',
        };

        return Role::where('slug', $slug)->first()?->id ?? Role::first()?->id;
    }
}
