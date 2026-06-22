<?php

namespace App\Policies;
use App\Models\Consulta;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConsultaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Root puede ver todas las consultas
        if ($user->hasRole('root')) {
            return true;
        }

        // Administradores pueden ver consultas de su centro
        if ($user->hasRole('administrador')) {
            return $user->can('ver consultas');
        }

        // Médicos pueden ver sus propias consultas
        if ($user->hasRole('medico')) {
            return $user->can('ver consultas');
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Consulta $consulta): bool
    {
        // Root puede ver cualquier consulta
        if ($user->hasRole('root')) {
            return true;
        }

        // Administradores pueden ver consultas de su centro
        if ($user->hasRole('administrador')) {
            // Verificar si la consulta pertenece al centro del administrador
            $consultaCentro = $consulta->medico?->centro_id ?? $consulta->paciente?->centro_id;
            return $user->centro_id === $consultaCentro && $user->can('ver consultas');
        }

        // Médicos solo pueden ver sus propias consultas
        if ($user->hasRole('medico')) {
            return $user->medico && $user->medico->id === $consulta->medico_id && $user->can('ver consultas');
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Root puede crear consultas
        if ($user->hasRole('root')) {
            return true;
        }

        // Médicos pueden crear consultas (para sus propias citas)
        if ($user->hasRole('medico')) {
            return $user->can('crear consultas');
        }

        // Administradores NO pueden crear consultas directamente
        // Solo los médicos crean consultas
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Consulta $consulta): bool
    {
        // Root puede actualizar cualquier consulta
        if ($user->hasRole('root')) {
            return true;
        }

        // Médicos pueden actualizar sus propias consultas
        if ($user->hasRole('medico')) {
            return $user->medico && $user->medico->id === $consulta->medico_id && $user->can('actualizar consultas');
        }

        // Administradores NO pueden actualizar consultas directamente
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Consulta $consulta): bool
    {
        // Root puede eliminar cualquier consulta
        if ($user->hasRole('root')) {
            return true;
        }

        // Médicos pueden eliminar sus propias consultas
        if ($user->hasRole('medico')) {
            return $user->medico && $user->medico->id === $consulta->medico_id && $user->can('borrar consultas');
        }

        // Administradores NO pueden eliminar consultas directamente
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Consulta $consulta): bool
    {
        if ($user->hasRole('root')) return true;
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Consulta $consulta): bool
    {
        if ($user->hasRole('root')) return true;
        return false;
    }
}
