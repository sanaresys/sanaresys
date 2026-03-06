<?php

namespace App\Policies;

use App\Models\Especialidad_Medico;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EspecialidadMedicoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
         return $user->can('ver especialidadmedicos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Especialidad_Medico $especialidadMedico): bool
    {
         return $user->can('ver especialidadmedicos');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
         return $user->can('crear especialidadmedicos');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Especialidad_Medico $especialidadMedico): bool
    {
         return $user->can('actualizar especialidadmedicos');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Especialidad_Medico $especialidadMedico): bool
    {
         return $user->can('borrar especialidadmedicos');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Especialidad_Medico $especialidadMedico): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Especialidad_Medico $especialidadMedico): bool
    {
        return false;
    }
}
