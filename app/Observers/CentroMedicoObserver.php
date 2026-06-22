<?php

namespace App\Observers;

use App\Models\Centros_Medico;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class CentroMedicoObserver
{
    /**
     * Al crear un centro médico
     * Sistema multi-tenant: cada centro tiene su propia BD completa
     */
    public function created(Centros_Medico $centro)
    {
        try {
            Log::info("=== CREANDO TENANT PARA CENTRO: {$centro->nombre_centro} ===");

            // Crear tenant
            $tenant = Tenant::create([
                'id' => 'centro_' . $centro->id,
                'centro_id' => $centro->id,
            ]);

            Log::info("✓ Registro de tenant creado: {$tenant->id}");
            Log::info("✓ BD del tenant: {$tenant->database()->getName()}");
            Log::info("✓ Cada centro tendrá su copia completa de todas las tablas");

        } catch (\Exception $e) {
            Log::error("✗ Error creando tenant: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Al eliminar un centro médico
     */
    public function deleting(Centros_Medico $centro)
    {
        try {
            $tenant = Tenant::where('centro_id', $centro->id)->first();
            
            if ($tenant) {
                Log::info("Eliminando tenant para centro: {$centro->nombre_centro}");
                
                // Esto eliminará la BD automáticamente
                $tenant->delete();
                
                Log::info("✓ Tenant eliminado exitosamente");
            }
        } catch (\Exception $e) {
            Log::error("Error eliminando tenant: " . $e->getMessage());
        }
    }
}
