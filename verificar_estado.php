<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Centros_Medico;
use App\Models\Tenant;
use App\Models\User;

echo "=== ESTADO ACTUAL DEL SISTEMA ===\n\n";

try {
    // 1. Bases de datos de tenants que existen
    echo "1. BASES DE DATOS DE TENANTS:\n";
    $databases = DB::select("SHOW DATABASES LIKE 'centro_%'");
    if (empty($databases)) {
        echo "   No hay bases de datos de tenants\n";
    } else {
        foreach ($databases as $db) {
            $dbName = array_values((array)$db)[0];
            echo "   ✓ {$dbName}\n";
        }
    }
    
    echo "\n2. CENTROS MÉDICOS EN LA BD CENTRAL:\n";
    $centros = Centros_Medico::all();
    if ($centros->isEmpty()) {
        echo "   No hay centros médicos\n";
    } else {
        foreach ($centros as $centro) {
            echo "   ✓ ID: {$centro->id} - {$centro->nombre_centro}\n";
        }
    }
    
    echo "\n3. REGISTROS EN TABLA TENANTS:\n";
    $tenants = Tenant::all();
    if ($tenants->isEmpty()) {
        echo "   No hay registros de tenants\n";
    } else {
        foreach ($tenants as $tenant) {
            echo "   ✓ ID: {$tenant->id} - Centro ID: {$tenant->centro_id}\n";
        }
    }
    
    echo "\n4. USUARIOS:\n";
    $users = User::all();
    if ($users->isEmpty()) {
        echo "   No hay usuarios\n";
    } else {
        foreach ($users as $user) {
            echo "   ✓ {$user->name} ({$user->email}) - Centro: " . ($user->centro_id ?? 'N/A') . "\n";
        }
    }
    
    // 5. Verificar estructura de la primera BD de tenant
    if (!empty($databases)) {
        $firstDb = array_values((array)$databases[0])[0];
        echo "\n5. TABLAS EN LA BD DEL TENANT ({$firstDb}):\n";
        $tables = DB::select("SHOW TABLES FROM `{$firstDb}`");
        if (empty($tables)) {
            echo "   La base de datos está vacía (sin tablas)\n";
        } else {
            echo "   Tiene " . count($tables) . " tablas:\n";
            foreach (array_slice($tables, 0, 10) as $table) {
                $tableName = array_values((array)$table)[0];
                echo "   - {$tableName}\n";
            }
            if (count($tables) > 10) {
                echo "   ... y " . (count($tables) - 10) . " tablas más\n";
            }
        }
    }
    
    echo "\n6. CONEXIONES DE BD CONFIGURADAS:\n";
    echo "   - BD Central: " . config('database.default') . " (" . config('database.connections.mysql.database') . ")\n";
    echo "   - BD Tenant: " . config('tenancy.tenant_connection') . "\n";
    
    echo "\n✓ Verificación completada\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
