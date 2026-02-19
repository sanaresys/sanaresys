<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICANDO TABLA MIGRATIONS EN centro_1 ===\n\n";

try {
    // Ver si existe tabla migrations
    $exists = DB::select("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'centro_1' AND TABLE_NAME = 'migrations'");
    
    if ($exists[0]->count > 0) {
        echo "✓ La tabla migrations existe en centro_1\n\n";
        
        // Ver qué migraciones están registradas
        $migrations = DB::select("SELECT * FROM `centro_1`.`migrations` ORDER BY batch, id");
        
        if (empty($migrations)) {
            echo "La tabla migrations está vacía\n";
        } else {
            echo "Migraciones registradas: " . count($migrations) . "\n\n";
            foreach ($migrations as $mig) {
                echo "  {$mig->batch} - {$mig->migration}\n";
            }
        }
    } else {
        echo "✗ La tabla migrations NO existe en centro_1\n";
        echo "Esto explica por qué la BD está vacía.\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
