<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Centros_Medico;
use App\Observers\CentroMedicoObserver;

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
        // Observador para centros médicos
        Centros_Medico::observe(CentroMedicoObserver::class);
    }
}
