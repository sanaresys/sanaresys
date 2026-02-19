<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICANDO BASES DE DATOS ===\n\n";

// 1. Ver todas las BDs
$dbs = DB::select("SHOW DATABASES");
echo "Bases de datos que contienen 'centro':\n";
foreach ($dbs as $db) {
    if (stripos($db->Database, 'centro') !== false) {
        echo "  - {$db->Database}\n";
    }
}

// 2. Ver tablas en centro_1 si existe
echo "\nVerificando existencia de centro_1...\n";
$exists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'centro_1'");

if (!empty($exists)) {
    echo "✓ La BD centro_1 existe\n\n";
    
    // Contar tablas
    $count = DB::select("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'centro_1'")[0]->count;
    echo "Número de tablas: {$count}\n\n";
    
    if ($count > 0) {
        echo "Tablas en centro_1:\n";
        $tables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'centro_1' ORDER BY TABLE_NAME");
        foreach ($tables as $table) {
            echo "  - " . $table->TABLE_NAME . "\n";
        }
    }
} else {
    echo "✗ La BD centro_1 NO existe\n";
}
