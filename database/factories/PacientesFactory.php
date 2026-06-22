<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Pacientes;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pacientes>
 */
class PacientesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'persona_id' => \App\Models\Persona::inRandomOrder()->first()?->id,
            'grupo_sanguineo' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-', 'No especificado', null]),
            'contacto_emergencia' => $this->faker->phoneNumber(),
            'centro_id' => 1, // Asumiendo que tienes al menos un centro médico con ID 1
        ];
    }
}
