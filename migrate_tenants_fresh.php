<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;

echo "=== LIMPIANDO Y MIGRANDO TENANTS ===\n\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    $dbName = $tenant->database()->getName();
    
    echo "Tenant: {$tenant->id} (DB: {$dbName})\n";
    
    try {
        // Inicializar tenant
        tenancy()->initialize($tenant);
        
        // Limpiar tabla de migraciones
        DB::statement("DROP TABLE IF EXISTS migrations");
        echo "  ✓ Tabla migrations eliminada\n";
        
        // Ejecutar migraciones de tenant
        \Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);
        
        echo "  ✓ Migraciones ejecutadas\n";
        
        // Verificar tablas creadas
        $tables = DB::select('SHOW TABLES');
        echo "  ✓ Total de tablas: " . count($tables) . "\n";
        
        // Terminar contexto del tenant
        tenancy()->end();
        
    } catch (\Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== PROCESO COMPLETADO ===\n";
