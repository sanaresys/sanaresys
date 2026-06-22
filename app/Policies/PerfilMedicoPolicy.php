<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class PerfilMedicoPolicy
{
    /**
     * Determina si el usuario puede acceder al perfil médico
     */
    public function view(User $user): Response
    {
        // Root siempre puede acceder (para administración)
        if ($user->hasRole('root') || $user->hasRole('administrador')) {
            return Response::allow();
        }

        // Usuarios con rol médico pueden acceder
        if ($user->hasRole('medico') || $user->hasRole('administrador')) {
            return Response::allow();
        }

        // Cualquier otro rol no tiene acceso
        return Response::deny('No tiene permisos para acceder al perfil médico.');
    }

    /**
     * Determina si el usuario puede actualizar la configuración del recetario
     */
    public function updateRecetario(User $user): Response
    {
        // Root sin médico asociado no puede guardar configuraciones
        if ($user->hasRole('root') && !$user->medico) {
            return Response::deny('Usuario root: Las configuraciones no se guardarán sin un registro de médico asociado.');
        }

        // Root con médico asociado puede actualizar
        if ($user->hasRole('root') && $user->medico) {
            return Response::allow();
        }

        // Médicos pueden actualizar su propia configuración
        if ($user->hasRole('medico') && $user->medico) {
            return Response::allow();
        }

        // Médicos sin registro de médico no pueden guardar
        if ($user->hasRole('medico') && !$user->medico) {
            return Response::deny('Para guardar configuraciones, contacte al administrador para completar su registro médico.');
        }

        if ($user->hasRole('administrador')) {
            return Response::allow();
        }

        if ($user->hasRole('administrador') && !$user->medico) {
            // Administradores sin médico asociado no pueden guardar configuraciones
            return Response::deny('Para guardar configuraciones, contacte al administrador para completar su registro médico.');
        }

        return Response::deny('No tiene permisos para actualizar la configuración del recetario.');
    }

    /**
     * Determina si el usuario puede ver la vista previa del recetario
     */
    public function viewPreview(User $user): Response
    {
        // Tanto root como médicos pueden ver la vista previa
        if ($user->hasRole(['root', 'medico'])) {
            return Response::allow();
        }

        return Response::deny('No tiene permisos para ver la vista previa del recetario.');
    }

    /**
     * Determina si el usuario puede subir logos
     */
    public function uploadLogo(User $user): Response
    {
        // Solo usuarios con médico asociado pueden subir logos
        if (($user->hasRole('root') || $user->hasRole('medico')) && $user->medico) {
            return Response::allow();
        }

        return Response::deny('Necesita un registro de médico asociado para subir logos.');
    }
}
