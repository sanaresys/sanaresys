<?php

namespace App\Policies;

use App\Models\Especialidad;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EspecialidadPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
         return $user->can('ver especialidad');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Especialidad $especialidad): bool
    {
         return $user->can('ver especialidad');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
         return $user->can('crear especialidad');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Especialidad $especialidad): bool
    {
         return $user->can('actualizar especialidad');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Especialidad $especialidad): bool
    {
         return $user->can('borrar especialidad');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Especialidad $especialidad): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Especialidad $especialidad): bool
    {
        return false;
    }
}
