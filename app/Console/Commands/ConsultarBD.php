<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConsultarBD extends Command
{
    protected $signature = 'bd:consultar';
    protected $description = 'Consultar directamente la base de datos';

    public function handle()
    {
        $this->info('=== CONSULTA DIRECTA A LA BASE DE DATOS ===');
        
        // Verificar la tabla de contratos
        $this->info("\n=== CONTRATOS ===");
        $contratos = DB::table('contratos_medicos')
            ->select('id', 'medico_id', 'salario_mensual', 'salario_quincenal', 'activo')
            ->where('activo', 1)
            ->get();
            
        $this->info("Contratos activos encontrados: {$contratos->count()}");
            
        foreach ($contratos as $contrato) {
            $this->info("Contrato ID: {$contrato->id} - Medico ID: {$contrato->medico_id}");
            $this->info("  Salario mensual: {$contrato->salario_mensual}");
            $this->info("  Salario quincenal: {$contrato->salario_quincenal}");
            $this->info("  ---");
        }
        
        // Verificar médicos
        $this->info("\n=== MÉDICOS ===");
        $medicos = DB::table('medicos')
            ->select('id', 'persona_id', 'numero_colegiacion', 'centro_id')
            ->get();
            
        $this->info("Médicos encontrados: {$medicos->count()}");
        
        foreach ($medicos as $medico) {
            $this->info("Médico ID: {$medico->id} - Persona ID: {$medico->persona_id}");
            $this->info("  Colegiación: {$medico->numero_colegiacion}");
            $this->info("  Centro ID: {$medico->centro_id}");
            $this->info("  ---");
        }
    }
}
