<?php

namespace App\Observers;

use App\Models\Centros_Medico;
use App\Models\Tenant;
use App\Services\TenantIdentityRenameService;
use Illuminate\Support\Facades\Log;

class CentroMedicoTenancyObserver
{
    /**
     * Legacy centers still get automatic tenant creation.
     * Domain centers are provisioned via onboarding flow.
     */
    public function created(Centros_Medico $centro): void
    {
        try {
            Log::info("=== CREANDO TENANT PARA CENTRO: {$centro->nombre_centro} ===");

            if (($centro->tenancy_mode ?? 'legacy') === 'domain') {
                Log::info("Centro {$centro->id} en modo domain, se omite flujo legacy en observer.");
                return;
            }

            $tenant = Tenant::where('centro_id', $centro->id)->first();

            if (! $tenant) {
                $tenant = Tenant::create([
                    'id' => 'centro_' . $centro->id,
                    'centro_id' => $centro->id,
                    'tenancy_mode' => 'legacy',
                ]);
            }

            Log::info('Registro de tenant creado.', [
                'tenant_id' => $tenant->id,
                'database' => $tenant->database()->getName(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error creando tenant para centro.', [
                'centro_id' => $centro->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Rename identity before center update is persisted.
     * If rename fails, center name update is aborted as well.
     */
    public function updating(Centros_Medico $centro): void
    {
        if (($centro->tenancy_mode ?? 'legacy') !== 'domain') {
            return;
        }

        if (! $centro->isDirty('nombre_centro')) {
            return;
        }

        app(TenantIdentityRenameService::class)->rename(
            centro: $centro,
            nuevoNombre: $centro->nombre_centro,
            persistCentro: false,
        );
    }

    /**
     * Delete tenant on center deletion.
     */
    public function deleting(Centros_Medico $centro): void
    {
        try {
            $tenant = Tenant::where('centro_id', $centro->id)->first();

            if ($tenant) {
                Log::info("Eliminando tenant para centro: {$centro->nombre_centro}");
                $tenant->delete();
                Log::info('Tenant eliminado exitosamente.');
            }
        } catch (\Throwable $e) {
            Log::error('Error eliminando tenant.', [
                'centro_id' => $centro->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
