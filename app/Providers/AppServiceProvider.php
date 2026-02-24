<?php

namespace App\Providers;

use App\Models\Centros_Medico;
use App\Observers\CentroMedicoTenancyObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observador para centros medicos.
        Centros_Medico::observe(CentroMedicoTenancyObserver::class);

        // Asegura que los requests Livewire usen contexto tenant correcto
        // (incluido el submit del login de Filament).
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)->middleware([
                'tenant.resolve',
                'tenant.canonical',
                'tenant.maintenance',
                'web',
            ]);
        });
    }
}
