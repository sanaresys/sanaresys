<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== BUSCANDO DONDE SE CREARON LAS TABLAS ===\n\n";

$tablasABuscar = ['enfermedades', 'pacientes', 'citas', 'consultas', 'recetas'];

// Obtener todas las BDs
$databases = DB::connection('mysql')->select('SHOW DATABASES');

foreach ($databases as $dbObj) {
    $dbName = array_values((array)$dbObj)[0];
    
    if (in_array($dbName, ['information_schema', 'mysql', 'performance_schema', 'phpmyadmin'])) {
        continue;
    }
    
    echo "BD: {$dbName}\n";
    
    $tables = DB::connection('mysql')->select("
        SELECT TABLE_NAME 
        FROM INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_SCHEMA = '{$dbName}'
        ORDER BY TABLE_NAME
    ");
    
    $encontradas = [];
    foreach ($tables as $table) {
        if (in_array($table->TABLE_NAME, $tablasABuscar)) {
            $encontradas[] = $table->TABLE_NAME;
        }
    }
    
    if (count($encontradas) > 0) {
        echo "   ✓ ENCONTRADAS: " . implode(', ', $encontradas) . "\n";
    } else {
        echo "   - Sin tablas de tenant\n";
    }
    
    echo "   Total tablas: " . count($tables) . "\n\n";
}

echo "=== FIN ===\n";
