<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireOnboarding
{
    /**
     * Verifica si el usuario ha completado el onboarding.
     * Si no lo ha completado, redirige al wizard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // No aplicar en rutas de onboarding, auth o peticiones de Livewire
        if ($request->routeIs('onboarding.*') || 
            $request->routeIs('filament.admin.auth.*')) {
            return $next($request);
        }

        $user = auth()->user();

        // Si no hay usuario autenticado, continuar (el middleware auth lo manejará)
        if (!$user) {
            return $next($request);
        }

        // Root puede acceder a todo sin onboarding
        if ($user->hasRole('root')) {
            return $next($request);
        }

        // Verificar si estamos en contexto de tenant
        $tenant = tenancy()->tenant;
        
        if (!$tenant || !$tenant->centro_id) {
            return $next($request);
        }

        // Obtener el centro médico del tenant actual
        $centro = \App\Models\Centros_Medico::on('mysql')
            ->select(['id', 'onboarding_completed_at'])
            ->find($tenant->centro_id);

        // Si no existe el centro o ya completó onboarding, continuar
        if (!$centro || $centro->onboarding_completed_at) {
            return $next($request);
        }

        // Onboarding pendiente - redirigir al wizard
        if ($request->is('admin') || $request->is('admin/*')) {
            session()->flash('warning', 'Debes completar la configuración inicial antes de usar el sistema.');
            return redirect()->route('onboarding.welcome');
        }

        return $next($request);
    }
}
