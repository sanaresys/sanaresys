<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;

echo "=== ELIMINANDO Y RECREANDO BASES DE DATOS CON NOMBRE CORRECTO ===\n\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    // Nombre incorrecto (con doble prefijo)
    $oldDbName = 'centro_' . $tenant->id;
    // Nombre correcto
    $newDbName = $tenant->database()->getName();
    
    echo "Tenant: {$tenant->id}\n";
    
    try {
        // Eliminar BD antigua si existe
        DB::statement("DROP DATABASE IF EXISTS `{$oldDbName}`");
        echo "  ✓ Eliminada BD incorrecta: {$oldDbName}\n";
        
        // Verificar si la nueva ya existe
        $exists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$newDbName}'");
        
        if (!empty($exists)) {
            echo "  ✓ BD correcta ya existe: {$newDbName}\n";
        } else {
            // Crear base de datos con nombre correcto
            DB::statement("CREATE DATABASE `{$newDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "  ✓ Creada BD correcta: {$newDbName}\n";
        }
    } catch (\Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== PROCESO COMPLETADO ===\n";
