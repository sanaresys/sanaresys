<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Citas;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Citas>
 */
class CitasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'medico_id'     => \App\Models\Medico::inRandomOrder()->first()?->id,
        'paciente_id'   => \App\Models\Pacientes::inRandomOrder()->first()?->id,
        'fecha'         => $this->faker->dateTimeBetween('now', '+1 year'),
        'hora'          => $this->faker->time('H:i'),
        'motivo'        => $this->faker->sentence(6),
        'estado'        => $this->faker->randomElement(['Pendiente', 'Confirmado', 'Cancelado', 'Realizado']),
    ];
    }
}
