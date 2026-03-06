<?php

namespace App\Policies;

use App\Models\Enfermedade;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EnfermedadePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver enfermedades');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Enfermedade $enfermedade): bool
    {
        return $user->can('ver enfermedades');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('crear enfermedades');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Enfermedade $enfermedade): bool
    {
        return $user->can('actualizar enfermedades');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Enfermedade $enfermedade): bool
    {
        return $user->can('borrar enfermedades');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Enfermedade $enfermedade): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Enfermedade $enfermedade): bool
    {
        return false;
    }
}
