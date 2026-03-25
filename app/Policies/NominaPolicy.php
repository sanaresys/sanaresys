<?php

namespace App\Policies;

use App\Models\ContabilidadMedica\Nomina;
use App\Models\User;
use App\Services\Billing\TenantModuleAccessService;
use Illuminate\Auth\Access\Response;

class NominaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver nomina');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Nomina $nomina): bool
    {
        return $user->can('ver nomina');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('crear nomina') && $this->moduleAllowsMutations($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Nomina $nomina): bool
    {
        return $user->can('actualizar nomina') && $this->moduleAllowsMutations($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Nomina $nomina): bool
    {
        return $user->can('borrar nomina') && $this->moduleAllowsMutations($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Nomina $nomina): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Nomina $nomina): bool
    {
        return false;
    }

    protected function moduleAllowsMutations(User $user): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }

        if (! tenancy()->initialized) {
            return true;
        }

        return app(TenantModuleAccessService::class)->isModuleActive('nomina');
    }
}
