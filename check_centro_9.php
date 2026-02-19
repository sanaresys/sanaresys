<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

echo "=== VERIFICANDO BASE DE DATOS centro_9 ===\n\n";

// Conectarse directamente a la base de datos centro_9
DB::connection('mysql')->statement("USE centro_9");
$tables = DB::connection('mysql')->select('SHOW TABLES');

echo "Total de tablas en centro_9: " . count($tables) . "\n\n";

if (count($tables) > 0) {
    echo "Tablas:\n";
    foreach ($tables as $table) {
        $tableName = array_values((array) $table)[0];
        echo "  - {$tableName}\n";
    }
} else {
    echo "✗ La base de datos está vacía. Las migraciones no se ejecutaron.\n";
}
