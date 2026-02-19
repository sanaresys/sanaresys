<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICANDO TABLAS EN centro_1 ===\n\n";

try {
    // Cambiar conexión a la BD del tenant
    $tables = DB::connection('mysql')->select("SHOW TABLES FROM `centro_1`");
    
    echo "Tablas encontradas: " . count($tables) . "\n\n";
    
    if (count($tables) > 0) {
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            echo "- {$tableName}\n";
        }
    } else {
        echo "La base de datos está vacía.\n";
        echo "\nIntentando verificar si existe la tabla migrations...\n";
        
        $result = DB::connection('mysql')->select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'centro_1' AND table_name = 'migrations'");
        if ($result[0]->count > 0) {
            echo "✓ La tabla migrations existe\n";
            
            $migrations = DB::connection('mysql')->select("SELECT * FROM `centro_1`.`migrations`");
            echo "Migraciones registradas: " . count($migrations) . "\n";
            
        } else {
            echo "✗ La tabla migrations NO existe\n";
            echo "\nEsto significa que nunca se ejecutaron las migraciones del tenant.\n";
        }
    }
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
