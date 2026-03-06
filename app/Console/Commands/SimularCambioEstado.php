<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ContabilidadMedica\Nomina;

class SimularCambioEstado extends Command
{
    protected $signature = 'nomina:simular-cambio-estado';
    protected $description = 'Simular el cambio de estado desde la interfaz';

    public function handle()
    {
        $this->info('=== SIMULACIÓN CAMBIO DE ESTADO ===');
        
        // Obtener la nómina
        $nomina = Nomina::withoutGlobalScopes()->first();
        
        if (!$nomina) {
            $this->error('No se encontró ninguna nómina');
            return;
        }
        
        $this->info("Nómina ID: {$nomina->id}");
        $this->info("Estado actual: {$nomina->estado}");
        
        // Simular los pasos que hace Filament
        $this->info("\n=== PASO 1: Estado PENDIENTE ===");
        $nomina->update(['estado' => 'pendiente']);
        
        // Simular query de Filament
        $nominaPendiente = Nomina::where('centro_id', $nomina->centro_id)
                    ->withCount('detalleNominas')
                    ->with(['detalleNominas', 'centro'])
                    ->find($nomina->id);
                    
        $this->info("Count detalles: {$nominaPendiente->detalle_nominas_count}");
        $this->info("Suma detalles: L. " . number_format($nominaPendiente->detalleNominas->sum('liquido_a_pagar'), 2));
        
        $this->info("\n=== PASO 2: Cambiar a CERRADA ===");
        $nomina->update(['estado' => 'cerrada']);
        
        // Simular query de Filament después del cambio
        $nominaCerrada = Nomina::where('centro_id', $nomina->centro_id)
                    ->withCount('detalleNominas')
                    ->with(['detalleNominas', 'centro'])
                    ->find($nomina->id);
                    
        $this->info("Count detalles después: {$nominaCerrada->detalle_nominas_count}");
        $this->info("Suma detalles después: L. " . number_format($nominaCerrada->detalleNominas->sum('liquido_a_pagar'), 2));
        
        $this->info("\n=== PASO 3: Query SIN with ===");
        $nominaSinWith = Nomina::where('centro_id', $nomina->centro_id)
                    ->withCount('detalleNominas')
                    ->find($nomina->id);
        $nominaSinWith->load('detalleNominas');
        
        $this->info("Count sin with: {$nominaSinWith->detalle_nominas_count}");
        $this->info("Suma sin with: L. " . number_format($nominaSinWith->detalleNominas->sum('liquido_a_pagar'), 2));
        
        // Restaurar estado
        $nomina->update(['estado' => 'pendiente']);
        $this->info("\n--- Estado restaurado a pendiente ---");
    }
}
