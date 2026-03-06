<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Especialidad_Medico>
 */
class EspecialidadMedicoFactory extends Factory
{
     protected $model = \App\Models\Especialidad_Medico::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'medico_id'        => \App\Models\Medico::inRandomOrder()->first()?->id,
            'especialidad_id'  => \App\Models\Especialidad::inRandomOrder()->first()?->id,
        ];
    }
}
