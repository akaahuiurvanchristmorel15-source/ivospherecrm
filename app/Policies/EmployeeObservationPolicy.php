<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\EmployeeObservation;
use App\Models\User;

class EmployeeObservationPolicy
{
    /**
     * Accès à la liste des observations (permission « observations.manage »).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('observations.manage');
    }

    /**
     * Saisie d'une observation sur un collaborateur que l'on suit.
     */
    public function create(User $user, Employee $employee): bool
    {
        return $this->viewAny($user) && $employee->isObservableBy($user);
    }

    /**
     * Seuls l'auteur et l'administrateur modifient une observation.
     */
    public function update(User $user, EmployeeObservation $observation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->viewAny($user)
            && $observation->author_id === $user->id
            && $observation->employee->isObservableBy($user);
    }

    public function delete(User $user, EmployeeObservation $observation): bool
    {
        return $this->update($user, $observation);
    }
}
