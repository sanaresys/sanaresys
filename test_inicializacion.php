<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

echo "=== TEST DE INICIALIZACION TENANT ===\n\n";

$tenantId = 'centro_1';
$tenant = Tenant::find($tenantId);

echo "1. ANTES DE INICIALIZAR:\n";
echo "   - Default connection: " . DB::getDefaultConnection() . "\n";
echo "   - Database (mysql): " . Config::get('database.connections.mysql.database') . "\n";
echo "   - Database (tenant): " . (Config::get('database.connections.tenant.database') ?? 'null') . "\n\n";

echo "2. INICIALIZANDO TENANT...\n";
tenancy()->initialize($tenant);
echo "   ✓ Inicializado\n\n";

echo "3. DESPUES DE INICIALIZAR:\n";
echo "   - Default connection: " . DB::getDefaultConnection() . "\n";
echo "   - Database (mysql): " . Config::get('database.connections.mysql.database') . "\n";  
echo "   - Database (tenant): " . Config::get('database.connections.tenant.database') . "\n\n";

echo "4. PROBANDO CONSULTA SELECT DATABASE():\n";
$db_mysql = DB::connection('mysql')->select('SELECT DATABASE() as db')[0]->db;
$db_tenant = DB::connection('tenant')->select('SELECT DATABASE() as db')[0]->db;
echo "   - Connection 'mysql': {$db_mysql}\n";
echo "   - Connection 'tenant': {$db_tenant}\n\n";

echo "5. PROBANDO CREAR TABLA EN CONEXION DEFAULT:\n";
try {
    DB::statement('CREATE TABLE IF NOT EXISTS test_default_conn (id INT)');
    $dbUsed = DB::select('SELECT DATABASE() as db')[0]->db;
    echo "   ✓ Tabla creada en BD: {$dbUsed}\n";
    
    // Eliminar tabla
    DB::statement('DROP TABLE test_default_conn');
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n6. PROBANDO CREAR TABLA ESPECIFICANDO 'tenant':\n";
try {
    DB::connection('tenant')->statement('CREATE TABLE IF NOT EXISTS test_tenant_conn (id INT)');
    $dbUsed = DB::connection('tenant')->select('SELECT DATABASE() as db')[0]->db;
    echo "   ✓ Tabla creada en BD: {$dbUsed}\n";
    
    // Eliminar tabla
    DB::connection('tenant')->statement('DROP TABLE test_tenant_conn');
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN TEST ===\n";
