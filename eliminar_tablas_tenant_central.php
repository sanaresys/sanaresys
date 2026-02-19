<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== LISTANDO Y ELIMINANDO TABLAS DE TENANT DE BD CENTRAL ===\n\n";

$tablasCentrales = [
    'centros_medicos', 'nacionalidades', 'personas', 'users', 'cache', 'cache_locks',
    'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens', 'sessions',
    'migrations', 'permissions', 'roles', 'model_has_permissions', 'model_has_roles',
    'role_has_permissions', 'especialidads', 'medicos', 'centros_medicos_medicos',
    'especialidad_medicos', 'tenants', 'domains'
];

// Obtener todas las tablas
$allTables = DB::connection('mysql')->select('SHOW TABLES');
$dbName = DB::connection('mysql')->getDatabaseName();

echo "Tablas en {$dbName}:\n";

$tablasAEliminar = [];
foreach ($allTables as $tableObj) {
    $tableName = array_values((array)$tableObj)[0];
    
    if (!in_array($tableName, $tablasCentrales)) {
        $tablasAEliminar[] = $tableName;
        echo "   [TENANT] {$tableName}\n";
    } else {
        echo "   [CENTRAL] {$tableName}\n";
    }
}

if (count($tablasAEliminar) > 0) {
    echo "\n¿Eliminar " . count($tablasAEliminar) . " tablas de tenant? (ejecutando...)\n";
    
    // Desactivar checks de FK
    DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS = 0');
    
    foreach ($tablasAEliminar as $tabla) {
        DB::connection('mysql')->statement("DROP TABLE IF EXISTS `{$tabla}`");
        echo "   ✓ Eliminada: {$tabla}\n";
    }
    
    // Reactivar checks de FK
    DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS = 1');
    
    echo "\n✓ Limpieza completada\n";
} else {
    echo "\n✓ No hay tablas de tenant en BD central\n";
}

echo "\n=== FIN ===\n";
