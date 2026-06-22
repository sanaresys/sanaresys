<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

echo "=== DIAGNOSTICO DETALLADO DE TENANT ===\n\n";

// 1. Obtener el tenant
$tenant = Tenant::where('centro_id', 1)->first();

if (!$tenant) {
    echo "✗ No se encontró el tenant\n";
    exit(1);
}

echo "1. TENANT BASICO:\n";
echo "   - ID: " . $tenant->id . "\n";
echo "   - Centro ID: " . $tenant->centro_id . "\n";
echo "   - getTenantKey(): " . $tenant->getTenantKey() . "\n";
echo "   - getTenantKeyName(): " . $tenant->getTenantKeyName() . "\n\n";

// 2. Verificar DatabaseConfig
echo "2. DATABASE CONFIG:\n";
$dbConfig = $tenant->database();
echo "   - Tipo: " . get_class($dbConfig) . "\n";
echo "   - getName(): " . $dbConfig->getName() . "\n";
echo "   - getTemplateConnectionName(): " . $dbConfig->getTemplateConnectionName() . "\n\n";

// 3. Verificar connection config
echo "3. CONNECTION CONFIG:\n";
$connectionConfig = $dbConfig->connection();
echo "   - driver: " . ($connectionConfig['driver'] ?? 'N/A') . "\n";
echo "   - database: " . ($connectionConfig['database'] ?? 'N/A') . "\n";
echo "   - host: " . ($connectionConfig['host'] ?? 'N/A') . "\n\n";

// 4. Inicializar tenant
echo "4. INICIALIZANDO TENANT...\n";
try {
    tenancy()->initialize($tenant);
    echo "   ✓ Tenant inicializado\n\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// 5. Verificar conexiones después de inicializar
echo "5. CONEXIONES DESPUES DE INICIALIZAR:\n";
echo "   - Conexión default: " . DB::getDefaultConnection() . "\n";
echo "   - BD de 'mysql': " . config('database.connections.mysql.database') . "\n";
echo "   - BD de 'tenant': " . config('database.connections.tenant.database') . "\n\n";

// 6. Probar consulta simple
echo "6. PROBANDO CONSULTA EN CONEXIÓN TENANT:\n";
try {
    $result = DB::connection('tenant')->select('SELECT DATABASE() as db');
    echo "   ✓ Base de datos actual: " . $result[0]->db . "\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DIAGNOSTICO ===\n";
