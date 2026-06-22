<?php

namespace Database\Seeders;

use App\Models\Centros_Medico;
use Illuminate\Database\Seeder;

class CentrosMedicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $centros = [
            [
                'nombre_centro' => 'Hospital San Lucas',
                'direccion' => 'Tegucigalpa',
                'telefono' => '2233-4455',
                'rtn' => '0801199901234',
            ],
        ];

        foreach ($centros as $centroData) {
            // Idempotente: no recrea el centro si ya existe.
            Centros_Medico::query()->firstOrCreate(
                ['rtn' => $centroData['rtn']],
                $centroData
            );
        }
    }
}
