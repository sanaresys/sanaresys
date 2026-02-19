<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

echo "=== RECREANDO TENANT Y EJECUTANDO MIGRACIONES ===\n\n";

try {
    // 1. Crear la BD
    echo "1. Creando base de datos centro_1...\n";
    DB::statement("CREATE DATABASE `centro_1` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ BD creada\n\n";
    
    // 2. Obtener el tenant
    echo "2. Obteniendo tenant...\n";
    $tenant = Tenant::find('centro_1');
    if (!$tenant) {
        echo "✗ Tenant no encontrado\n";
        exit(1);
    }
    echo "✓ Tenant encontrado\n\n";
    
    // 3. Inicializar el tenant
    echo "3. Inicializando tenant...\n";
    tenancy()->initialize($tenant);
    echo "✓ Tenant inicializado\n\n";
    
    // 4. Ejecutar migraciones directamente
    echo "4. Ejecutando migraciones...\n";
    Artisan::call('migrate', [
        '--database' => 'tenant',
        '--path' => 'database/migrations/tenant',
        '--force' => true,
    ]);
    
    echo Artisan::output();
    
    // 5. Verificar tablas creadas
    echo "\n5. Verificando tablas creadas...\n";
    $tables = DB::connection('tenant')->select("SHOW TABLES");
    echo "✓ Tablas creadas: " . count($tables) . "\n";
    
    echo "\n✓ Proceso completado exitosamente\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
