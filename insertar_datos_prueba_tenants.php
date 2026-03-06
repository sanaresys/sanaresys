<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== INSERTANDO DATOS DE PRUEBA EN TENANTS ===" . PHP_EOL . PHP_EOL;

$tenants = Tenant::all();

foreach ($tenants as $index => $tenant) {
    echo "═══════════════════════════════════════" . PHP_EOL;
    echo "TENANT: {$tenant->id}" . PHP_EOL;
    echo "═══════════════════════════════════════" . PHP_EOL;
    
    // Inicializar el tenant
    tenancy()->initialize($tenant);
    
    // Limpiar datos existentes (desactivar FK checks)
    DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0');
    DB::connection('tenant')->table('citas')->truncate();
    DB::connection('tenant')->table('consultas')->truncate();
    DB::connection('tenant')->table('recetas')->truncate();
    DB::connection('tenant')->table('pacientes')->truncate();
    DB::connection('tenant')->table('medicos')->truncate();
    DB::connection('tenant')->table('users')->truncate();
    DB::connection('tenant')->table('personas')->truncate();
    DB::connection('tenant')->table('nacionalidades')->truncate();
    DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1');
    
    echo "   ✓ Datos existentes limpiados" . PHP_EOL;
    
    // Insertar nacionalidad
    DB::connection('tenant')->table('nacionalidades')->insert([
        'id' => 1,
        'nacionalidad' => 'Hondureña',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Crear usuarios para este tenant
    $usuario = DB::connection('tenant')->table('users')->insert([
        'name' => "Admin " . ($index + 1),
        'email' => "admin{$tenant->centro_id}@ejemplo.com",
        'password' => Hash::make('password'),
        'centro_id' => $tenant->centro_id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "   ✓ Usuario creado" . PHP_EOL;
    
    // Crear personas para médicos
    $personasIds = [];
    for ($i = 1; $i <= 3; $i++) {
        $personaId = DB::connection('tenant')->table('personas')->insertGetId([
            'primer_nombre' => "Doctor" . $i,
            'primer_apellido' => "García",
            'dni' => "0801" . str_pad($tenant->centro_id * 100 + $i, 10, '0', STR_PAD_LEFT),
            'telefono' => "9999-" . str_pad($i, 4, '0', STR_PAD_LEFT),
            'sexo' => 'M',
            'fecha_nacimiento' => '1980-01-01',
            'nacionalidad_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $personasIds[] = $personaId;
        
        // Crear médico
        DB::connection('tenant')->table('medicos')->insert([
            'numero_colegiacion' => "COL-" . $tenant->centro_id . "-" . str_pad($i, 3, '0', STR_PAD_LEFT),
            'persona_id' => $personaId,
            'centro_id' => $tenant->centro_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    echo "   ✓ 3 médicos creados" . PHP_EOL;
    
    // Crear personas para pacientes
    for ($i = 1; $i <= 5; $i++) {
        $personaId = DB::connection('tenant')->table('personas')->insertGetId([
            'primer_nombre' => "Paciente" . $i,
            'primer_apellido' => "López",
            'dni' => "0801" . str_pad($tenant->centro_id * 100 + 10 + $i, 10, '0', STR_PAD_LEFT),
            'telefono' => "8888-" . str_pad($i, 4, '0', STR_PAD_LEFT),
            'sexo' => $i % 2 == 0 ? 'F' : 'M',
            'fecha_nacimiento' => '1990-01-01',
            'nacionalidad_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Crear paciente
        DB::connection('tenant')->table('pacientes')->insert([
            'persona_id' => $personaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    echo "   ✓ 5 pacientes creados" . PHP_EOL;
    
    // Crear citas
    for ($i = 1; $i <= 10; $i++) {
        DB::connection('tenant')->table('citas')->insert([
            'paciente_id' => rand(1, 5),
            'medico_id' => rand(1, 3),
            'fecha' => now()->addDays(rand(1, 30))->format('Y-m-d'),
            'hora' => '09:00:00',
            'motivo' => 'Consulta general',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    echo "   ✓ 10 citas creadas" . PHP_EOL;
    
    echo PHP_EOL;
}

// Finalizar tenancy
tenancy()->end();

echo "✓ Datos de prueba insertados en todos los tenants" . PHP_EOL;
