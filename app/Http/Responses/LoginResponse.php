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

        if ($user && $user->hasRole('root')) {
            return redirect()->intended(Filament::getUrl());
        }

        $tenant = tenancy()->tenant;
        if ($tenant && $tenant->centro_id) {
            $centro = Centros_Medico::on('mysql')
                ->select(['id', 'billing_status', 'onboarding_completed_at'])
                ->find($tenant->centro_id);

            if ($centro && $centro->billing_status !== 'active') {
                session()->flash('warning', 'Tu suscripcion esta inactiva. Reactiva el pago para continuar.');
                return redirect()->route('tenant.billing.inactive');
            }

            if ($centro && ! $centro->onboarding_completed_at) {
                session()->flash('info', 'Por favor completa la configuracion inicial de tu clinica.');
                return redirect()->route('onboarding.welcome');
            }
        }

        return redirect()->intended(Filament::getUrl());
    }
}
