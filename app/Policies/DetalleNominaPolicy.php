<?php

namespace App\Policies;

use App\Models\ContabilidadMedica\DetalleNomina;
use App\Models\User;
use App\Services\Billing\TenantModuleAccessService;
use Illuminate\Auth\Access\Response;

class DetalleNominaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver detallenomina');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DetalleNomina $detalleNomina): bool
    {
        return $user->can('ver detallenomina');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('crear detallenomina') && $this->moduleAllowsMutations($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DetalleNomina $detalleNomina): bool
    {
        return $user->can('actualizar detallenomina') && $this->moduleAllowsMutations($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DetalleNomina $detalleNomina): bool
    {
        return $user->can('borrar detallenomina') && $this->moduleAllowsMutations($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DetalleNomina $detalleNomina): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DetalleNomina $detalleNomina): bool
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
