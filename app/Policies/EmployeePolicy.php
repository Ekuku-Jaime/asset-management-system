<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the employee.
     */
    public function delete(User $user, Employee $employee)
    {
        // Apenas administradores podem eliminar
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the employee.
     */
    public function restore(User $user, Employee $employee)
    {
        // Apenas administradores podem restaurar
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the employee.
     */
    public function forceDelete(User $user, Employee $employee)
    {
        // Apenas administradores podem eliminar permanentemente
        return $user->isAdmin();
    }
}