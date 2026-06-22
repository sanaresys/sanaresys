<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PacientesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Pacientes::factory()->count(20)->create();
        // You can adjust the count as needed
        // \App\Models\Pacientes::factory()->count(10)->create(); // Example for a different count
    }
}
