<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;

echo "=== CREANDO BASES DE DATOS PARA TENANTS ===\n\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    $databaseName = $tenant->database()->getName();
    
    echo "Tenant: {$tenant->id}\n";
    echo "  Centro ID: {$tenant->centro_id}\n";
    echo "  Base de datos: {$databaseName}\n";
    
    try {
        // Verificar si la BD ya existe
        $exists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$databaseName}'");
        
        if (!empty($exists)) {
            echo "  ✓ Base de datos ya existe\n";
        } else {
            // Crear base de datos
            DB::statement("CREATE DATABASE `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "  ✓ Base de datos creada\n";
        }
    } catch (\Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== PROCESO COMPLETADO ===\n";
