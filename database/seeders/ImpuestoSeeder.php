<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Impuesto;
use App\Models\Centros_Medico;
use Carbon\Carbon;

class ImpuestoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los centros médicos
        $centros = Centros_Medico::all();
        
        // Impuestos comunes en Honduras
        $impuestos = [
            [
                'nombre' => 'ISV (Impuesto Sobre Ventas)',
                'porcentaje' => 15.00,
                'vigente_desde' => '2024-01-01',
                'vigente_hasta' => null,
            ],
            [
                'nombre' => 'Impuesto Municipal',
                'porcentaje' => 2.00,
                'vigente_desde' => '2024-01-01',
                'vigente_hasta' => null,
            ],
            [
                'nombre' => 'Exonerado ISV',
                'porcentaje' => 0.00,
                'vigente_desde' => '2024-01-01',
                'vigente_hasta' => null,
            ]
        ];

        // Crear impuestos para cada centro médico, evitando duplicados
        foreach ($centros as $centro) {
            foreach ($impuestos as $impuesto) {
                // Verificar si ya existe este impuesto para este centro
                $existeImpuesto = Impuesto::where('nombre', $impuesto['nombre'])
                    ->where('centro_id', $centro->id)
                    ->exists();
                
                if (!$existeImpuesto) {
                    Impuesto::create([
                        'nombre' => $impuesto['nombre'],
                        'porcentaje' => $impuesto['porcentaje'],
                        'vigente_desde' => $impuesto['vigente_desde'],
                        'vigente_hasta' => $impuesto['vigente_hasta'],
                        'centro_id' => $centro->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }
    }
}
