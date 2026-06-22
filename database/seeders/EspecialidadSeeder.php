<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use Illuminate\Database\Seeder;

class EspecialidadSeeder extends Seeder
{
    public function run()
    {
        $especialidades = [
            ['especialidad' => 'Cardiología'],
            ['especialidad' => 'Dermatología'],
            ['especialidad' => 'Pediatría'],
            ['especialidad' => 'Neurología'],
            ['especialidad' => 'Ortopedia'],
            ['especialidad' => 'Ginecología'],
            ['especialidad' => 'Oftalmología'],
            ['especialidad' => 'Psiquiatría'],
            ['especialidad' => 'Pediatría'],
            ['especialidad' => 'Endocrinología'],
            ['especialidad' => 'Gastroenterología'],
            ['especialidad' => 'Oncología'],
            ['especialidad' => 'Urología'],
            ['especialidad' => 'Otorrinolaringología'],
            ['especialidad' => 'Traumatología'],
            ['especialidad' => 'Reumatología'],
            ['especialidad' => 'Infectología'],
            ['especialidad' => 'Medicina Interna'],
            ['especialidad' => 'Medicina Familiar'],
        ];

        foreach ($especialidades as $especialidad) {
            Especialidad::create($especialidad);
        }
    }
}