<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MARCANDO MIGRACIONES COMO EJECUTADAS ===\n\n";

// Marcar users como ejecutada
try {
    DB::table('migrations')->insert([
        'migration' => '0001_01_02_000000_create_users_table',
        'batch' => 1
    ]);
    echo "✓ users marcada como ejecutada\n";
} catch (\Exception $e) {
    echo "✗ users: " . $e->getMessage() . "\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";
