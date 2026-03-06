<?php

namespace App\Policies;

use App\Models\Citas;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CitasPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Root puede ver todas las citas
        if ($user->hasRole('root')) {
            return true;
        }

        // Administradores pueden ver citas de su centro
        if ($user->hasRole('administrador')) {
            return $user->can('ver citas');
        }

        // Médicos pueden ver sus propias citas
        if ($user->hasRole('medico')) {
            return $user->can('ver citas');
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Citas $cita): bool
    {
        // Root puede ver cualquier cita
        if ($user->hasRole('root')) {
            return true;
        }

        // Administradores pueden ver citas de su centro
        if ($user->hasRole('administrador')) {
            return $user->centro_id === $cita->centro_id && $user->can('ver citas');
        }

        // Médicos solo pueden ver sus propias citas
        if ($user->hasRole('medico')) {
            return $user->medico && $user->medico->id === $cita->medico_id && $user->can('ver citas');
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Root puede crear citas
        if ($user->hasRole('root')) {
            return true;
        }

        // Administradores pueden crear citas en su centro
        if ($user->hasRole('administrador')) {
            return $user->can('crear citas');
        }

        // Médicos SÍ pueden crear citas (para sí mismos)
        if ($user->hasRole('medico')) {
            return $user->can('crear citas') && $user->medico; // Debe tener registro de médico
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Citas $cita): bool
    {
        // Root puede actualizar cualquier cita
        if ($user->hasRole('root')) {
            return true;
        }

        // Administradores pueden actualizar citas de su centro
        if ($user->hasRole('administrador')) {
            return $user->centro_id === $cita->centro_id && $user->can('actualizar citas');
        }

        // Médicos pueden actualizar sus propias citas (cambiar estado, etc.)
        if ($user->hasRole('medico')) {
            return $user->medico && $user->medico->id === $cita->medico_id && $user->can('actualizar citas');
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Citas $cita): bool
    {
        // Root puede eliminar cualquier cita
        if ($user->hasRole('root')) {
            return true;
        }

        // Administradores pueden eliminar citas de su centro
        if ($user->hasRole('administrador')) {
            return $user->centro_id === $cita->centro_id && $user->can('borrar citas');
        }

        // Médicos NO pueden eliminar citas
        if ($user->hasRole('medico')) {
            return false;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Citas $cita): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Citas $cita): bool
    {
        return false;
    }

    /**
     * Determine whether the user can confirm a cita (from calendar).
     */
    public function confirm(User $user, Citas $cita): bool
    {
        // Root puede confirmar cualquier cita
        if ($user->hasRole('root')) {
            return true;
        }

        // Administradores pueden confirmar citas de su centro
        if ($user->hasRole('administrador')) {
            return $user->centro_id === $cita->centro_id;
        }

        // Médicos pueden confirmar sus propias citas desde el calendario
        if ($user->hasRole('medico')) {
            return $user->medico && $user->medico->id === $cita->medico_id;
        }

        return false;
    }

    /**
     * Determine whether the user can cancel a cita (from calendar).
     */
    public function cancel(User $user, Citas $cita): bool
    {
        // Root puede cancelar cualquier cita
        if ($user->hasRole('root')) {
            return true;
        }

        // Administradores pueden cancelar citas de su centro
        if ($user->hasRole('administrador')) {
            return $user->centro_id === $cita->centro_id;
        }

        // Médicos pueden cancelar sus propias citas desde el calendario
        if ($user->hasRole('medico')) {
            return $user->medico && $user->medico->id === $cita->medico_id;
        }

        return false;
    }
}
