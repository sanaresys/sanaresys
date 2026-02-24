<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // ...otras policies...
        \Spatie\Permission\Models\Role::class => \App\Policies\RolePolicy::class,
        \App\Models\Centros_Medico::class => \App\Policies\CentrosMedicoPolicy::class,
        \App\Models\Enfermedades__Paciente::class => \App\Policies\EnfermedadesPacientePolicy::class,
        \App\Filament\Pages\PerfilMedico::class => \App\Policies\PerfilMedicoPolicy::class,
        \App\Models\ContabilidadMedica\ContratoMedico::class => \App\Policies\ContratoMedicoPolicy::class,
        \App\Models\ContabilidadMedica\Nomina::class => \App\Policies\NominaPolicy::class,
        \App\Models\ContabilidadMedica\DetalleNomina::class => \App\Policies\DetalleNominaPolicy::class,
        \App\Models\Consulta::class => \App\Policies\TenantConsultaPolicy::class,
        \App\Models\Citas::class => \App\Policies\TenantCitasPolicy::class,
        \App\Models\Examenes::class => \App\Policies\TenantExamenesPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
