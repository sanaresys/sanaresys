<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Medico;
use App\Models\ContabilidadMedica\ContratoMedico;

class VerificarDatos extends Command
{
    protected $signature = 'verificar:datos';
    protected $description = 'Verificar datos del sistema sin scope';

    public function handle()
    {
        $this->info('=== VERIFICACIÓN DEL SISTEMA ===');
        
        // Verificar médicos
        $totalMedicos = Medico::withoutGlobalScopes()->count();
        $this->line("Total médicos: {$totalMedicos}");
        
        // Verificar contratos
        $totalContratos = ContratoMedico::withoutGlobalScopes()->count();
        $this->line("Total contratos: {$totalContratos}");
        
        // Verificar contratos activos
        $contratosActivos = ContratoMedico::withoutGlobalScopes()->where('activo', true)->count();
        $this->line("Contratos activos: {$contratosActivos}");
        
        // Verificar médicos con contratos activos
        $medicosConContrato = Medico::withoutGlobalScopes()
            ->whereHas('contratosActivos', function($query) {
                $query->where('activo', true);
            })->count();
        $this->line("Médicos con contrato activo: {$medicosConContrato}");
        
        // Mostrar detalles de médicos con contratos
        $this->info("\n=== MÉDICOS CON CONTRATOS ACTIVOS ===");
        $medicos = Medico::withoutGlobalScopes()
            ->with(['persona', 'contratosActivos'])
            ->whereHas('contratosActivos', function($query) {
                $query->where('activo', true);
            })->get();
            
        foreach ($medicos as $medico) {
            $contrato = $medico->contratosActivos->first();
            $this->line("- {$medico->persona->nombre_completo} (Salario: L. " . number_format($contrato->salario_mensual, 2) . ")");
        }
        
        if ($medicosConContrato == 0) {
            $this->warn("\n¡ATENCIÓN! No hay médicos con contratos activos.");
            $this->info("Necesitas crear contratos para los médicos primero.");
        } else {
            $this->info("\n✅ Sistema listo para operaciones médicas");
        }
    }
}
