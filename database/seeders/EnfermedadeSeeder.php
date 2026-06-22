<?php

namespace Database\Seeders;

use App\Models\Enfermedade;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnfermedadeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enfermedades = [
                'Diabetes',
                'Hipertensión',
                'Asma',
                'Epilepsia',
                'Gastritis',
                'Artritis',
                'COVID-19',
                'Migraña',
                'Anemia',
                'Colesterol alto',
        ];        

        // Insertar cada nacionalidad en la tabla, una por una
        foreach ($enfermedades as $enfermedad) {
            DB::table('enfermedades')->insert([
                'enfermedades' => $enfermedad,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'created_by' => 1, // ID del usuario que creó esto, puedes cambiarlo dinámicamente si lo necesitas
                'updated_by' => 1,
            ]);
        }    
        
                 
    }
}