<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Centros_Medicos_Medico;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Centros_Medicos_Medico>
 */
class CentrosMedicosMedicoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'medico_id' => $this->faker->numberBetween(1, 10),
            'centro_medico_id'=>$this->faker->numberBetween(1,10),
            'horario'=>$this->faker->text()
        ];
    }
}
