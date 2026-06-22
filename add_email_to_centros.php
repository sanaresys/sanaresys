<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  AGREGAR CAMPO EMAIL A CENTROS_MEDICOS                          ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

try {
    // Verificar si el campo ya existe
    $columns = DB::select("SHOW COLUMNS FROM centros_medicos LIKE 'email'");
    
    if (count($columns) > 0) {
        echo "✅ El campo 'email' ya existe en la tabla centros_medicos\n";
        echo "   Tipo: " . $columns[0]->Type . "\n";
        echo "   Null: " . $columns[0]->Null . "\n";
    } else {
        echo "⚠️  El campo 'email' NO existe. Agregándolo...\n\n";
        
        // Agregar el campo después de 'telefono'
        DB::statement("ALTER TABLE centros_medicos ADD COLUMN email VARCHAR(255) NULL AFTER telefono");
        
        echo "✅ Campo 'email' agregado exitosamente\n\n";
        
        // Verificar
        $columns = DB::select("SHOW COLUMNS FROM centros_medicos LIKE 'email'");
        echo "Verificación:\n";
        echo "   Tipo: " . $columns[0]->Type . "\n";
        echo "   Null: " . $columns[0]->Null . "\n";
    }
    
    echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ OPERACIÓN COMPLETADA                                        ║\n";
    echo "╚══════════════════════════════════════════════════════════════════╝\n\n";
    
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n\n";
    exit(1);
}
