<?php

namespace App\Policies;

use App\Models\Citas;
use App\Models\User;

class TenantCitasPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }

        if ($user->hasRole('administrador')) {
            return $user->can('ver citas');
        }

        if ($user->hasRole('medico')) {
            return $user->can('ver citas');
        }

        return false;
    }

    public function view(User $user, Citas $cita): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }

        if ($user->hasRole('administrador')) {
            return $user->can('ver citas');
        }

        if ($user->hasRole('medico')) {
            return $user->medico && $user->medico->id === $cita->medico_id && $user->can('ver citas');
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }

        if ($user->hasRole('administrador')) {
            return $user->can('crear citas');
        }

        if ($user->hasRole('medico')) {
            return $user->can('crear citas') && $user->medico;
        }

        return false;
    }

    public function update(User $user, Citas $cita): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }

        if ($user->hasRole('administrador')) {
            return $user->can('actualizar citas');
        }

        if ($user->hasRole('medico')) {
            return $user->medico && $user->medico->id === $cita->medico_id && $user->can('actualizar citas');
        }

        return false;
    }

    public function delete(User $user, Citas $cita): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }

        if ($user->hasRole('administrador')) {
            return $user->can('borrar citas');
        }

        return false;
    }

    public function confirm(User $user, Citas $cita): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }

        if ($user->hasRole('administrador')) {
            return $user->can('actualizar citas');
        }

        if ($user->hasRole('medico')) {
            return $user->medico && $user->medico->id === $cita->medico_id;
        }

        return false;
    }

    public function cancel(User $user, Citas $cita): bool
    {
        return $this->confirm($user, $cita);
    }
}

