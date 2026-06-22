<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Descuento;
use App\Models\Centros_Medico;
use Carbon\Carbon;

class DescuentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los centros médicos
        $centros = Centros_Medico::all();
        
        // Descuentos comunes
        $descuentos = [
            [
                'nombre' => 'Descuento Tercera Edad',
                'tipo' => 'PORCENTAJE',
                'valor' => 10.00,
                'aplica_desde' => '2024-01-01',
                'aplica_hasta' => null,
                'activo' => 'SI',
            ],
            [
                'nombre' => 'Descuento Empleados',
                'tipo' => 'PORCENTAJE',
                'valor' => 15.00,
                'aplica_desde' => '2024-01-01',
                'aplica_hasta' => null,
                'activo' => 'SI',
            ],
            [
                'nombre' => 'Descuento Estudiantes',
                'tipo' => 'PORCENTAJE',
                'valor' => 5.00,
                'aplica_desde' => '2024-01-01',
                'aplica_hasta' => null,
                'activo' => 'SI',
            ],
            [
                'nombre' => 'Descuento por Pronto Pago',
                'tipo' => 'PORCENTAJE',
                'valor' => 3.00,
                'aplica_desde' => '2024-01-01',
                'aplica_hasta' => null,
                'activo' => 'SI',
            ],
            [
                'nombre' => 'Descuento Familiar',
                'tipo' => 'MONTO',
                'valor' => 100.00,
                'aplica_desde' => '2024-01-01',
                'aplica_hasta' => null,
                'activo' => 'SI',
            ]
        ];

        // Crear descuentos para cada centro médico, evitando duplicados
        foreach ($centros as $centro) {
            foreach ($descuentos as $descuento) {
                // Verificar si ya existe este descuento para este centro
                $existeDescuento = descuento::where('nombre', $descuento['nombre'])
                    ->where('centro_id', $centro->id)
                    ->exists();
                
                if (!$existeDescuento) {
                    descuento::create([
                        'nombre' => $descuento['nombre'],
                        'tipo' => $descuento['tipo'],
                        'valor' => $descuento['valor'],
                        'aplica_desde' => $descuento['aplica_desde'],
                        'aplica_hasta' => $descuento['aplica_hasta'],
                        'activo' => $descuento['activo'],
                        'centro_id' => $centro->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }
    }
}
