<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContabilidadMedica\Nomina;
use App\Models\ContabilidadMedica\DetalleNomina;
use App\Models\Medico;

class NominaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el primer centro médico para usar como ejemplo
        $centro = \App\Models\Centros_Medico::first();
        
        if (!$centro) {
            echo "No se encontraron centros médicos en la base de datos\n";
            return;
        }
        
        // Crear una nómina de ejemplo
        $nomina = Nomina::create([
            'empresa' => $centro->nombre_centro,
            'año' => 2025,
            'mes' => 1, // Enero
            'tipo_pago' => 'mensual',
            'descripcion' => 'Nómina de enero 2025 - ejemplo',
            'cerrada' => false,
            'estado' => 'abierta',
            'centro_id' => $centro->id,
            'created_by' => 1, // Usuario administrador por defecto
        ]);

        // Obtener algunos médicos para la nómina del mismo centro
        $medicos = Medico::where('centro_id', $centro->id)
                         ->with('persona')
                         ->take(3)
                         ->get();

        if ($medicos->isEmpty()) {
            echo "No se encontraron médicos para el centro {$centro->nombre_centro}\n";
            return;
        }

        foreach ($medicos as $medico) {
            $salarioBase = 56997.84; // Salario base de ejemplo
            $deducciones = 5000.00;
            $percepciones = 2000.00;
            $total = $salarioBase + $percepciones - $deducciones;

            DetalleNomina::create([
                'nomina_id' => $nomina->id,
                'medico_id' => $medico->id,
                'medico_nombre' => $medico->persona->nombre_completo ?? 'Médico ' . $medico->id,
                'salario_base' => $salarioBase,
                'deducciones' => $deducciones,
                'percepciones' => $percepciones,
                'total_pagar' => $total,
                'deducciones_detalle' => "IHSS: L. 2,500.00\nISR: L. 2,500.00",
                'percepciones_detalle' => "Bono por desempeño: L. 2,000.00",
                'centro_id' => $centro->id,
            ]);
        }
        
        echo "Nómina creada exitosamente con " . $medicos->count() . " médicos\n";
    }
}
