<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

echo "=== DIAGNOSTICO DE CONEXIÓN TENANT ===\n\n";

try {
    $tenant = Tenant::find('centro_1');
    
    echo "1. CONFIGURACIÓN DEL TENANT:\n";
    echo "   - ID: " . $tenant->id . "\n";
    echo "   - Centro ID: " . $tenant->centro_id . "\n";
    echo "   - Database: " . $tenant->database()->getName() . "\n\n";
    
    echo "2. INICIALIZANDO TENANT...\n";
    tenancy()->initialize($tenant);
    echo "   ✓ Tenant inicializado\n\n";
    
    echo "3. CONFIGURACIÓN DE CONEXIONES:\n";
    echo "   - Conexión default: " . config('database.default') . "\n";
    echo "   - BD de conexión 'mysql': " . config('database.connections.mysql.database') . "\n";
    echo "   - BD de conexión 'tenant': " . config('database.connections.tenant.database') . "\n\n";
    
    echo "4. PROBANDO CONEXIÓN TENANT:\n";
    
    // Probar crear tabla directamente
    DB::connection('tenant')->statement("
        CREATE TABLE IF NOT EXISTS test_tabla (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255)
        )
    ");
    
    echo "   ✓ Tabla test_tabla creada\n";
    
    // Verificar en qué BD se creó
    $result = DB::select("SELECT TABLE_SCHEMA FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'test_tabla'");
    
    if (!empty($result)) {
        echo "   ✓ Tabla encontrada en BD: " . $result[0]->TABLE_SCHEMA . "\n";
    } else {
        echo "   ✗ Tabla NO encontrada\n";
    }
    
    // Limpiar
    DB::connection('tenant')->statement("DROP TABLE IF EXISTS test_tabla");
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
