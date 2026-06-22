<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== BUSCANDO TABLAS DE MIGRACIONES ===\n\n";

// Buscar en BD central
echo "1. En db_clinica:\n";
$tables = DB::connection('mysql')->select("SHOW TABLES LIKE '%migrations%'");
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    echo "   - {$tableName}\n";
    
    // Ver contenido
    $rows = DB::connection('mysql')->table($tableName)->count();
    echo "     Registros: {$rows}\n";
    
    // Si tiene registros, mostrar algunos
    if ($rows > 0) {
        $migrations = DB::connection('mysql')->table($tableName)->get();
        foreach ($migrations as $migration) {
            if (isset($migration->migration)) {
                echo "     * {$migration->migration}\n";
            }
        }
    }
}

// Buscar en centro_1
echo "\n2. En centro_1:\n";
$tables = DB::connection('mysql')->select("
    SELECT TABLE_NAME 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = 'centro_1' 
    AND TABLE_NAME LIKE '%migrations%'
");
if (count($tables) > 0) {
    foreach ($tables as $table) {
        echo "   - {$table->TABLE_NAME}\n";
    }
} else {
    echo "   No hay tablas de migraciones\n";
}

echo "\n=== FIN BUSQUEDA ===\n";
