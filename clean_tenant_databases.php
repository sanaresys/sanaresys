<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== LIMPIANDO BASES DE DATOS DE TENANTS ===\n\n";

try {
    // Obtener todas las bases de datos que comienzan con "centro_"
    $databases = DB::select("SHOW DATABASES LIKE 'centro_%'");
    
    if (empty($databases)) {
        echo "No hay bases de datos de tenants para eliminar.\n";
    } else {
        foreach ($databases as $db) {
            $dbName = array_values((array)$db)[0];
            echo "Eliminando base de datos: {$dbName}\n";
            DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
            echo "✓ {$dbName} eliminada\n";
        }
    }
    
    echo "\n✓ Limpieza completada exitosamente\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
