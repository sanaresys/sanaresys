<?php

namespace App\Policies;

use App\Models\Consulta;
use App\Models\User;

class TenantConsultaPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }

        if ($user->hasRole('administrador')) {
            return $user->can('ver consultas');
        }

        if ($user->hasRole('medico')) {
            return $user->can('ver consultas');
        }

        return false;
    }

    public function view(User $user, Consulta $consulta): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }

        if ($user->hasRole('administrador')) {
            return $user->can('ver consultas');
        }

        if ($user->hasRole('medico')) {
            return $user->medico && $user->medico->id === $consulta->medico_id && $user->can('ver consultas');
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }

        if ($user->hasRole('medico')) {
            return $user->can('crear consultas');
        }

        return false;
    }

    public function update(User $user, Consulta $consulta): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }

        if ($user->hasRole('medico')) {
            return $user->medico && $user->medico->id === $consulta->medico_id && $user->can('actualizar consultas');
        }

        return false;
    }

    public function delete(User $user, Consulta $consulta): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }

        if ($user->hasRole('medico')) {
            return $user->medico && $user->medico->id === $consulta->medico_id && $user->can('borrar consultas');
        }

        return false;
    }
}

