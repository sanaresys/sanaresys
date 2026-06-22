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
        // Registrar respuesta de login personalizada para Filament
        $this->app->singleton(
            \Filament\Http\Responses\Auth\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );
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
