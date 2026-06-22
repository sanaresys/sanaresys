<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Centros_Medico;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

echo "=== PRUEBA DE CREACIÓN AUTOMÁTICA DE TENANT ===\n\n";

// Crear un centro de prueba
$centro = Centros_Medico::create([
    'nombre_centro' => 'Centro Médico Test ' . time(),
    'direccion' => 'Calle Test 123',
    'telefono' => '1234-5678',
    'rtn' => 'RTN-TEST-' . time(),
]);

echo "✓ Centro médico creado:\n";
echo "  - ID: {$centro->id}\n";
echo "  - Nombre: {$centro->nombre_centro}\n";
echo "  - RTN: {$centro->rtn}\n\n";

// Verificar si se creó el tenant
sleep(1); // Esperar un momento para que el observer se ejecute

$tenant = Tenant::where('centro_id', $centro->id)->first();

if ($tenant) {
    echo "✓ Tenant creado automáticamente:\n";
    echo "  - ID: {$tenant->id}\n";
    echo "  - Centro ID: {$tenant->centro_id}\n";
    echo "  - Database: {$tenant->database()->getName()}\n\n";
    
    // Verificar si la base de datos existe
    $dbName = $tenant->database()->getName();
    $exists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$dbName}'");
    
    if (!empty($exists)) {
        echo "✓ Base de datos creada: {$dbName}\n\n";
        
        // Verificar tablas en la BD del tenant
        tenancy()->initialize($tenant);
        $tables = DB::connection('tenant')->select('SHOW TABLES');
        echo "✓ Total de tablas en el tenant: " . count($tables) . "\n";
        
        if (count($tables) > 0) {
            echo "  Primeras 10 tablas:\n";
            foreach (array_slice($tables, 0, 10) as $table) {
                $tableName = array_values((array) $table)[0];
                echo "    - {$tableName}\n";
            }
        }
    } else {
        echo "✗ Base de datos NO existe: {$dbName}\n";
    }
} else {
    echo "✗ Tenant NO se creó automáticamente\n";
    echo "  Verifica que el Observer esté registrado en AppServiceProvider\n";
}

echo "\n=== LIMPIEZA ===\n";
// Eliminar el centro de prueba
if ($tenant) {
    $tenant->delete(); // Esto eliminará la BD automáticamente
    echo "✓ Tenant eliminado\n";
}
$centro->forceDelete();
echo "✓ Centro eliminado\n";

echo "\n✓ Prueba completada\n";
