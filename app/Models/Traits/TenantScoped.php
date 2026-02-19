<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;

trait TenantScoped
{
    protected static function addDynamicCentroScope($centroId)
    {
        static::addGlobalScope('dynamic_centro', function (Builder $builder) use ($centroId) {
            $builder->where('centro_id', $centroId);
        });
    }

    /**
     * El método "boot" de un trait se ejecuta automáticamente
     * cuando un modelo que lo usa es inicializado.
     */
    protected static function bootTenantScoped()
    {
        // Asignar centro_id automáticamente al crear
        static::creating(function ($model) {
            if (!isset($model->centro_id)) {
                $model->centro_id = static::getCurrentTenantId();
            }
        });

        // Aplicar scope global para filtrar por centro
        static::addGlobalScope('centros_medicos', function (Builder $builder) {
            $centroId = static::getCurrentTenantId();
            if ($centroId) {
                $builder->where('centro_id', $centroId);
            }
        });
    }

    /**
     * Determina si se debe omitir el scope del tenant
     */
    protected static function shouldBypassTenantScope(): bool
    {
        // Solo bypass en comandos de consola (para seeders, etc)
        return app()->runningInConsole() && !app()->runningUnitTests();
    }

    /**
     * Obtiene el ID del tenant actual
     */
    protected static function getCurrentTenantId(): ?int
    {
        // Durante seeders/comandos de consola, permitir sin tenant
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return null;
        }

        if (Auth::check()) {
            $user = Auth::user();
            
            // Para usuarios root, usar el centro seleccionado de la sesión
            if (method_exists($user, 'hasRole') && $user->hasRole('root')) {
                return session('current_centro_id');
            }
            
            // Para usuarios normales, usar su centro asignado
            return $user->centro_id;
        }

        // Como respaldo, verificar el tenant actual
        if (function_exists('tenancy') && tenancy()->initialized) {
            $tenant = tenancy()->tenant;
            if ($tenant && isset($tenant->centro_id)) {
                return $tenant->centro_id;
            }
        }

        return null; // Devolver null en lugar de lanzar excepción
    }

    /**
     * Scope para filtrar por un centro específico (útil para root)
     */
    public function scopeForCentro($query, $centroId)
    {
        return $query->where('centro_id', $centroId);
    }

    /**
     * Scope para obtener datos de todos los centros (solo root)
     */
    public function scopeAllCentros($query)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (method_exists($user, 'hasRole') && $user->hasRole('root')) {
                return $query->withoutGlobalScope('centros_medicos');
            }
        }
        return $query;
    }
}