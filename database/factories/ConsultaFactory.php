<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Consulta;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Consulta>
 */
class ConsultaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cita_id' => $this->faker->numberBetween(1, 10),
            'paciente_id' => $this->faker->numberBetween(1, 10),
            'medico_id' => $this->faker->numberBetween(1, 10),
            'diagnostico' => $this->faker->word(),
            'tratamiento' => $this->faker->word(),
            'observaciones' => $this->faker->word(),
            
            
        ];
    }
}
