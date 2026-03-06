<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Persona;

class PersonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el primer centro médico creado
        $centro = \App\Models\Centros_Medico::first();
        if ($centro) {
            Persona::factory()->count(20)->create([
                'centro_id' => $centro->id,
            ]);
        } else {
            // Si no hay centro, crea sin centro_id (no recomendado)
            Persona::factory()->count(20)->create();
        }
    }
}
