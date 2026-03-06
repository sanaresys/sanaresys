<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Persona;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Persona>
 */
class PersonaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'primer_nombre' => $this->faker->firstName(),
            'primer_apellido' => $this->faker->lastName(),
            'telefono'=>$this->faker->phoneNumber(),
            'direccion'=>$this->faker->address(),
            'dni'=>$this->faker->unique()->numerify('########'),
            'sexo'=>$this->faker->randomElement(["F","M"]),
            'fecha_Nacimiento'=>$this->faker->date('Y-m-d'),
            'nacionalidad_id'=>$this->faker->numberBetween(1,10),
            
            
        ];
    }
}
