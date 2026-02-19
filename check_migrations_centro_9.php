<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICANDO TABLA migrations EN centro_9 ===\n\n";

// Conectarse directamente a la base de datos centro_9
DB::connection('mysql')->statement("USE centro_9");

// Verificar si existe la tabla migrations
$tables = DB::connection('mysql')->select("SHOW TABLES LIKE 'migrations'");

if (empty($tables)) {
    echo "✗ La tabla 'migrations' NO existe en centro_9\n";
} else {
    echo "✓ La tabla 'migrations' existe\n\n";
    
    $migrations = DB::connection('mysql')->select("SELECT * FROM migrations");
    echo "Total de migraciones registradas: " . count($migrations) . "\n";
    
    if (count($migrations) > 0) {
        echo "\nMigraciones registradas:\n";
        foreach ($migrations as $migration) {
            echo "  - {$migration->migration}\n";
        }
    }
}
