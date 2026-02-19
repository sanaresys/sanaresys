<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

echo "=== EJECUTANDO MIGRACIONES DEL TENANT ===\n\n";

try {
    $tenant = Tenant::find('centro_1');
    
    if (!$tenant) {
        echo "✗ No se encontró el tenant centro_1\n";
        exit(1);
    }
    
    echo "Tenant encontrado: {$tenant->id} (Centro ID: {$tenant->centro_id})\n";
    echo "Base de datos: " . $tenant->database()->getName() . "\n\n";
    
    echo "Inicializando tenant...\n";
    tenancy()->initialize($tenant);
    
    echo "Ejecutando migraciones...\n\n";
    
    Artisan::call('migrate', [
        '--database' => 'tenant',
        '--path' => 'database/migrations/tenant',
        '--force' => true,
    ]);
    
    echo Artisan::output();
    
    echo "\n✓ Migraciones ejecutadas exitosamente\n";
    
    // Verificar tablas creadas
    $tables = DB::connection('tenant')->select("SHOW TABLES");
    echo "\nTablas creadas: " . count($tables) . "\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
