<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== LIMPIANDO BASES DE DATOS DE PRUEBA ===\n\n";

// Obtener todas las bases de datos que empiecen con 'centro_' y sean de prueba (ID > 21)
$databases = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME LIKE 'centro_%'");

foreach ($databases as $db) {
    $dbName = $db->SCHEMA_NAME;
    
    // Extraer el ID del nombre de la base de datos
    $id = intval(str_replace('centro_', '', $dbName));
    
    // Si el ID es mayor a 21 (los 21 originales), eliminarlo
    if ($id > 21) {
        echo "Eliminando: {$dbName}\n";
        DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
    }
}

echo "\n✓ Limpieza completada\n";
