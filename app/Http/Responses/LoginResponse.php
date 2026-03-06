<?php

namespace App\Http\Responses;

use App\Models\Centros_Medico;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = Filament::auth()->user();

        // Si es usuario root, ir directamente al panel
        if ($user && $user->hasRole('root')) {
            return redirect()->intended(Filament::getUrl());
        }

        // Verificar si estamos en contexto de tenant
        $tenant = tenancy()->tenant;
        
        if ($tenant && $tenant->centro_id) {
            // Buscar el centro asociado al tenant actual
            $centro = Centros_Medico::on('mysql')
                ->select(['id', 'onboarding_completed_at'])
                ->find($tenant->centro_id);

            if ($centro && !$centro->onboarding_completed_at) {
                // Redirigir al wizard de onboarding con mensaje informativo
                session()->flash('info', 'Por favor completa la configuración inicial de tu clínica.');
                return redirect()->route('onboarding.welcome');
            }
        }

        // Login normal - ir al panel de Filament
        return redirect()->intended(Filament::getUrl());
    }
}
