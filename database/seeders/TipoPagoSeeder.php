<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoPago;
use App\Models\Centros_Medico;
use Carbon\Carbon;

class TipoPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los centros médicos
        $centros = Centros_Medico::all();
        
        // Tipos de pago específicos solicitados
        $tiposPago = [
            [
                'nombre' => 'Efectivo',
                'descripcion' => 'Pago en efectivo, billetes y monedas',
            ],
            [
                'nombre' => 'Tarjeta',
                'descripcion' => 'Pago con tarjeta de crédito o débito',
            ],
            [
                'nombre' => 'POS',
                'descripcion' => 'Pago a través de terminal punto de venta (POS)',
            ]
        ];

        // Crear tipos de pago para cada centro médico, evitando duplicados
        foreach ($centros as $centro) {
            foreach ($tiposPago as $tipoPago) {
                // Verificar si ya existe este tipo de pago para este centro
                $existeTipoPago = tipopago::where('nombre', $tipoPago['nombre'])
                    ->where('centro_id', $centro->id)
                    ->exists();
                
                if (!$existeTipoPago) {
                    tipopago::create([
                        'nombre' => $tipoPago['nombre'],
                        'descripcion' => $tipoPago['descripcion'],
                        'centro_id' => $centro->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }
    }
}
