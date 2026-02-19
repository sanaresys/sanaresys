<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

echo "=== DIAGNOSTICO MANUAL DE CONEXION ===\n\n";

// 1. Obtener el tenant
$tenant = Tenant::where('centro_id', 1)->first();
echo "1. Tenant: " . $tenant->id . "\n\n";

// 2. Obtener DatabaseConfig y connection
$dbConfig = $tenant->database();
$connectionConfig = $dbConfig->connection();

echo "2. Connection config generado:\n";
print_r($connectionConfig);
echo "\n";

// 3. Intentar configurar manualmente la conexión
echo "3. Configurando conexión 'tenant' manualmente...\n";
Config::set('database.connections.tenant', $connectionConfig);
echo "   ✓ Configuración establecida\n\n";

// 4. Verificar config
echo "4. Verificar config después de set:\n";
echo "   - database.connections.tenant.database: " . Config::get('database.connections.tenant.database') . "\n\n";

// 5. Purgar conexión existente y reconectar
echo "5. Purgando y reconnectando...\n";
DB::purge('tenant');
DB::reconnect('tenant');
echo "   ✓ Reconectado\n\n";

// 6. Probar consulta
echo "6. Probando consulta SELECT DATABASE():\n";
try {
    $result = DB::connection('tenant')->select('SELECT DATABASE() as db');
    echo "   ✓ Base de datos: " . $result[0]->db . "\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// 7. Probar crear tabla
echo "\n7. Probando crear tabla de prueba:\n";
try {
    DB::connection('tenant')->statement('CREATE TABLE IF NOT EXISTS test_manual (id INT)');
    echo "   ✓ Tabla creada\n";
    
    // Verificar que existe
    $tables = DB::connection('tenant')->select('SHOW TABLES');
    echo "   Tablas en BD: " . count($tables) . "\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DIAGNOSTICO ===\n";
