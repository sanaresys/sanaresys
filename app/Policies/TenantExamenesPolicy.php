<?php

namespace App\Policies;

use App\Models\Examenes;
use App\Models\User;

class TenantExamenesPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Examenes $examen): bool
    {
        if ($user->roles->contains('name', 'root')) {
            return true;
        }

        if ($user->roles->contains('name', 'administrador')) {
            return true;
        }

        if ($user->roles->contains('name', 'medico')) {
            return $user->medico && $examen->medico_id === $user->medico->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->roles->contains('name', 'medico');
    }

    public function update(User $user, Examenes $examen): bool
    {
        if ($user->roles->contains('name', 'root')) {
            return true;
        }

        if ($user->roles->contains('name', 'administrador')) {
            return true;
        }

        if ($user->roles->contains('name', 'medico')) {
            return $user->medico && $examen->medico_id === $user->medico->id;
        }

        return false;
    }

    public function delete(User $user, Examenes $examen): bool
    {
        if ($user->roles->contains('name', 'root')) {
            return true;
        }

        if ($user->roles->contains('name', 'medico')) {
            return $user->medico
                && $examen->medico_id === $user->medico->id
                && $examen->estado === 'Solicitado';
        }

        return false;
    }

    public function uploadResult(User $user, Examenes $examen): bool
    {
        return $this->update($user, $examen);
    }
}

