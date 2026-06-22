<?php

namespace App\Policies;

use App\Models\Medico;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MedicoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
         return $user->can('ver medicos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Medico $medico): bool
    {
         return $user->can('ver medicos');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
         return $user->can('crear medicos');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Medico $medico): bool
    {
         return $user->can('actualizar medicos');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Medico $medico): bool
    {
         return $user->can('borrar medicos');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Medico $medico): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Medico $medico): bool
    {
        return false;
    }
}
