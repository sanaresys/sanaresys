<?php

namespace Database\Seeders;

use App\Models\Centros_Medicos_Medico;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class CentrosMedicosMedicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Centros_Medicos_Medico::factory()->count(10)->create();
    }
}
