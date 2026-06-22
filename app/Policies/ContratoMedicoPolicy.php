<?php

namespace App\Policies;

use App\Models\ContabilidadMedica\ContratoMedico;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ContratoMedicoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Si es médico, necesita permiso específico
        if ($user->hasRole('medico')) {
            return $user->can('ver contratomedico');
        }
        
        // Otros roles como admin o root
        return $user->can('ver contratomedico');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ContratoMedico $contratoMedico): bool
    {
        // Si es médico, solo puede ver sus propios contratos
        if ($user->hasRole('medico')) {
            // Verificar si el contrato pertenece al médico asociado al usuario
            return $user->medico && $contratoMedico->medico_id === $user->medico->id;
        }
        
        // Otros roles como admin o root
        return $user->can('ver contratomedico');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('crear contratomedico');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ContratoMedico $contratoMedico): bool
    {
        // Los médicos no pueden actualizar contratos, incluso los suyos
        if ($user->roles()->where('name', 'medico')->exists()) {
            return false;
        }
        
        return $user->can('actualizar contratomedico');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ContratoMedico $contratoMedico): bool
    {
        // Los médicos no pueden eliminar contratos, incluso los suyos
        if ($user->roles()->where('name', 'medico')->exists()) {
            return false;
        }
        
        return $user->can('borrar contratomedico');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ContratoMedico $contratoMedico): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ContratoMedico $contratoMedico): bool
    {
        return false;
    }
}
