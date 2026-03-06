<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * Determina si el usuario puede ver la lista de roles.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('root') || $user->hasRole('administrador');
    }

    /**
     * Determina si el usuario puede ver un rol específico.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasRole('root') || $user->hasRole('administrador');
    }

    /**
     * Determina si el usuario puede crear roles.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('root') || $user->hasRole('administrador');
    }

    /**
     * Determina si el usuario puede actualizar roles.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->hasRole('root') || $user->hasRole('administrador');
    }

    /**
     * Determina si el usuario puede eliminar roles.
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->hasRole('root') || $user->hasRole('administrador');
    }
}
