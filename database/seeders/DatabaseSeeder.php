<?php

namespace Database\Seeders;

use App\Models\Citas;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Crear roles y permisos primero
        $this->call([
            RolesAndPermissionsSeeder::class,
            BillingModulesSeeder::class,
            CentrosMedicoSeeder::class, // Crear centros antes de asignar usuarios
            EspecialidadSeeder::class,
            NacionalidadSeeder::class,
            //PersonaSeeder::class,
            
            EnfermedadeSeeder::class,
            
            //MedicoSeeder::class,
            //EspecialidadMedicoSeeder::class,
            //PacientesSeeder::class,
            //CitasSeeder::class,
            
            // NOTA: Los seeders de Impuesto, Descuento y TipoPago se deben ejecutar
            // en cada BD tenant, NO en la BD central. Se ejecutarán manualmente después.
            // ImpuestoSeeder::class,
            // DescuentoSeeder::class,
            // TipoPagoSeeder::class,
        ]);

        // Crear usuario root después de tener los centros
        User::factory()->create([
            'name' => 'root',
            'email' => 'root@example.com',
            'password' => '12345678'
        ]);
        
        $user = User::find(1);
        $user->assignRole('root');
        

        // Asignar el primer centro médico (Hospital San Lucas) al usuario root
        $primerCentro = \App\Models\Centros_Medico::where('nombre_centro', 'Hospital San Lucas')->first();
        if ($primerCentro) {
            $user->centro_id = $primerCentro->id;
            $user->save();
        }

        // Crear persona para root si no existe
        $personaRoot = \App\Models\Persona::firstOrCreate(
            ['dni' => 'ROOT0001'],
            [
                'primer_nombre' => 'Root',
                'primer_apellido' => 'Admin',
                'telefono' => '9999-9999',
                'direccion' => 'Oficina Central',
                'sexo' => 'M',
                'fecha_nacimiento' => '1980-01-01',
                'nacionalidad_id' => 1,
            ]
        );

        // Asociar persona al usuario root
        $user->persona_id = $personaRoot->id;
        $user->save();

        // Crear médico para root si no existe
        $medicoRoot = \App\Models\Medico::firstOrCreate(
            ['persona_id' => $personaRoot->id],
            [
                'numero_colegiacion' => 'ROOT-0001',
                'horario_entrada' => '08:00',
                'horario_salida' => '16:00',
                'centro_id' => $primerCentro ? $primerCentro->id : 1,
            ]
        );

        // NOTA: Los contratos médicos están en la BD del tenant
        // Se deben crear desde la aplicación cuando se esté en el contexto del tenant
        // \App\Models\ContabilidadMedica\ContratoMedico::firstOrCreate(...);
    }
}
