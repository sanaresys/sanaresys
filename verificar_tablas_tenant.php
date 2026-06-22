<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICANDO TABLAS EN LA BD DEL TENANT ===\n\n";

try {
    // Consultar tablas directamente en la BD centro_1
    $tables = DB::select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'centro_1' ORDER BY TABLE_NAME");
    
    if (empty($tables)) {
        echo "✗ La base de datos está vacía\n";
    } else {
        echo "✓ Base de datos centro_1 tiene " . count($tables) . " tablas:\n\n";
        
        foreach ($tables as $table) {
            echo "  - " . $table->TABLE_NAME . "\n";
        }
    }
    
    echo "\n✓ Verificación completada\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
