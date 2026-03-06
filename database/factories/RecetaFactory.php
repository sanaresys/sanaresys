<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Receta;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Receta>
 */
class RecetaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'paciente_id' => $this->faker->numberBetween(1, 10),
            'medico_id' => $this->faker->numberBetween(1, 10),
            'consulta_id' => $this->faker->numberBetween(1, 10),
            'medicamentos' => $this->faker->word(),
            'indicaciones' => $this->faker->word(),
            
        ];
    }
}
