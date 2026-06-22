<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

echo "=== SIMULACION DE MIGRACIONES TENANT ===\n\n";

// Simular lo que hace tenants:migrate
$tenantId = 'centro_1';

echo "1. Ejecutando tenants:migrate para tenant: {$tenantId}\n";
$exitCode = Artisan::call('tenants:migrate', [
    '--tenants' => [$tenantId],
]);

echo "   Exit code: {$exitCode}\n";
echo "   Output:\n" . Artisan::output() . "\n";

// Verificar tablas en centro_1
echo "\n2. Verificando tablas en centro_1:\n";
try {
    $tables = DB::connection('mysql')->select("
        SELECT TABLE_NAME 
        FROM INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_SCHEMA = 'centro_1'
        ORDER BY TABLE_NAME
    ");
    
    echo "   Encontradas " . count($tables) . " tablas:\n";
    foreach ($tables as $table) {
        echo "   - {$table->TABLE_NAME}\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN SIMULACION ===\n";
