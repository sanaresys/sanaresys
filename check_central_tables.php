<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TABLAS EN BD CENTRAL ===\n\n";

$tables = DB::select('SHOW TABLES');

echo "Total de tablas: " . count($tables) . "\n\n";

$tableNames = [];
foreach ($tables as $table) {
    $tableName = array_values((array) $table)[0];
    $tableNames[] = $tableName;
    echo "  - {$tableName}\n";
}

// Verificar tablas importantes
$importantes = ['users', 'centros_medicos', 'tenants', 'domains', 'roles', 'permissions'];

echo "\n=== VERIFICACIÓN DE TABLAS IMPORTANTES ===\n";
foreach ($importantes as $tabla) {
    $existe = in_array($tabla, $tableNames);
    $status = $existe ? '✓' : '✗';
    echo "{$status} {$tabla}\n";
}
