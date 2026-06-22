<?php

namespace App\Policies;

use App\Models\Enfermedades__Paciente;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EnfermedadesPacientePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver enfermedades_pacientes');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Enfermedades__Paciente $enfermedadesPaciente): bool
    {
        return $user->can('ver enfermedades_pacientes');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('crear enfermedades_pacientes');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Enfermedades__Paciente $enfermedadesPaciente): bool
    {
        return $user->can('actualizar enfermedades_pacientes');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Enfermedades__Paciente $enfermedadesPaciente): bool
    {
        return $user->can('borrar enfermedades_pacientes');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Enfermedades__Paciente $enfermedadesPaciente): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Enfermedades__Paciente $enfermedadesPaciente): bool
    {
        return false;
    }
}
