<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

echo "=== CONSULTA MULTI-TENANT DESDE PANEL ADMIN ===" . PHP_EOL . PHP_EOL;

// 1. Obtener todos los tenants desde la BD central
$tenants = Tenant::all();

echo "Total de tenants: " . $tenants->count() . PHP_EOL . PHP_EOL;

// 2. Iterar sobre cada tenant y obtener sus datos
foreach ($tenants as $tenant) {
    echo "═══════════════════════════════════════" . PHP_EOL;
    echo "TENANT: {$tenant->id}" . PHP_EOL;
    echo "═══════════════════════════════════════" . PHP_EOL;
    
    // Inicializar el tenant (cambia la conexión a su BD)
    tenancy()->initialize($tenant);
    
    // Ahora podemos consultar datos específicos de este tenant
    $usuariosCount = DB::connection('tenant')->table('users')->count();
    $medicosCount = DB::connection('tenant')->table('medicos')->count();
    $pacientesCount = DB::connection('tenant')->table('pacientes')->count();
    $citasCount = DB::connection('tenant')->table('citas')->count();
    $consultasCount = DB::connection('tenant')->table('consultas')->count();
    
    echo "   - Usuarios: $usuariosCount" . PHP_EOL;
    echo "   - Médicos: $medicosCount" . PHP_EOL;
    echo "   - Pacientes: $pacientesCount" . PHP_EOL;
    echo "   - Citas: $citasCount" . PHP_EOL;
    echo "   - Consultas: $consultasCount" . PHP_EOL;
    echo PHP_EOL;
}

// 3. Ejemplo de agregación: Total de citas en todos los tenants
echo "═══════════════════════════════════════" . PHP_EOL;
echo "RESUMEN GLOBAL (TODOS LOS CENTROS)" . PHP_EOL;
echo "═══════════════════════════════════════" . PHP_EOL;

$totalCitas = 0;
$totalConsultas = 0;
$totalPacientes = 0;

foreach ($tenants as $tenant) {
    tenancy()->initialize($tenant);
    
    $totalCitas += DB::connection('tenant')->table('citas')->count();
    $totalConsultas += DB::connection('tenant')->table('consultas')->count();
    $totalPacientes += DB::connection('tenant')->table('pacientes')->count();
}

echo "   - Total de pacientes: $totalPacientes" . PHP_EOL;
echo "   - Total de citas: $totalCitas" . PHP_EOL;
echo "   - Total de consultas: $totalConsultas" . PHP_EOL;

echo PHP_EOL . "✓ Consulta multi-tenant completada" . PHP_EOL;

// 4. Finalizar tenancy (volver a la BD central)
tenancy()->end();
