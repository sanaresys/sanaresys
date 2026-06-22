<?php
/**
 * Agregar columnas de onboarding faltantes
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  AGREGAR COLUMNAS DE ONBOARDING FALTANTES                       ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

// Verificar columnas actuales
echo "📊 ESTADO ACTUAL:\n";
echo "────────────────────────────────────────────────────────────────\n";

try {
    $columns = DB::connection('mysql')
        ->select("SHOW COLUMNS FROM centros_medicos WHERE Field LIKE 'onboarding%'");
    
    $columnasActuales = array_column($columns, 'Field');
    
    echo "Columnas existentes:\n";
    foreach ($columnasActuales as $col) {
        echo "   ✅ $col\n";
    }
    
    $necesarias = [
        'onboarding_current_step' => 'INT DEFAULT 0',
        'onboarding_skipped_cai' => 'TINYINT(1) DEFAULT 0',
        'onboarding_completed_at' => 'TIMESTAMP NULL',
    ];
    
    $faltan = [];
    echo "\nVerificando columnas necesarias:\n";
    foreach ($necesarias as $col => $tipo) {
        if (in_array($col, $columnasActuales)) {
            echo "   ✅ $col - Existe\n";
        } else {
            echo "   ❌ $col - FALTA\n";
            $faltan[$col] = $tipo;
        }
    }
    
    if (empty($faltan)) {
        echo "\n✅ Todas las columnas existen\n\n";
        exit(0);
    }
    
    echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
    echo "║  AGREGANDO COLUMNAS FALTANTES                                    ║\n";
    echo "╚══════════════════════════════════════════════════════════════════╝\n\n";
    
    foreach ($faltan as $columna => $tipo) {
        echo "Agregando columna: $columna ($tipo)... ";
        
        try {
            $sql = "ALTER TABLE centros_medicos ADD COLUMN `$columna` $tipo";
            
            // Para onboarding_current_step, agregar AFTER onboarding_completed_at si existe
            if ($columna === 'onboarding_current_step' && in_array('onboarding_completed_at', $columnasActuales)) {
                $sql .= " AFTER `onboarding_completed_at`";
            } elseif ($columna === 'onboarding_skipped_cai') {
                $sql .= " AFTER `onboarding_current_step`";
            }
            
            DB::connection('mysql')->statement($sql);
            echo "✅\n";
            
        } catch (\Exception $e) {
            echo "❌\n";
            echo "Error: {$e->getMessage()}\n";
        }
    }
    
    echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
    echo "║  VERIFICACIÓN FINAL                                             ║\n";
    echo "╚══════════════════════════════════════════════════════════════════╝\n\n";
    
    $columnsNuevas = DB::connection('mysql')
        ->select("SHOW COLUMNS FROM centros_medicos WHERE Field LIKE 'onboarding%'");
    
    $columnasFinales = array_column($columnsNuevas, 'Field');
    
    echo "Columnas después de la corrección:\n";
    foreach ($columnasFinales as $col) {
        echo "   ✅ $col\n";
    }
    
    $todasExisten = true;
    foreach (array_keys($necesarias) as $col) {
        if (!in_array($col, $columnasFinales)) {
            $todasExisten = false;
            echo "   ❌ FALTA: $col\n";
        }
    }
    
    echo "\n";
    
    if ($todasExisten) {
        echo "╔══════════════════════════════════════════════════════════════════╗\n";
        echo "║  ✅ CORRECCIÓN COMPLETADA EXITOSAMENTE                          ║\n";
        echo "╚══════════════════════════════════════════════════════════════════╝\n\n";
        
        echo "Ahora inicializa los valores para usuarios existentes:\n\n";
        
        echo "UPDATE centros_medicos SET\n";
        echo "  onboarding_current_step = 0,\n";
        echo "  onboarding_skipped_cai = 0\n";
        echo "WHERE onboarding_completed_at IS NULL;\n\n";
        
        echo "¿Ejecutar esta actualización ahora? (S/N): ";
        $respuesta = trim(fgets(STDIN));
        
        if (strtoupper($respuesta) === 'S') {
            $affected = DB::connection('mysql')
                ->table('centros_medicos')
                ->whereNull('onboarding_completed_at')
                ->update([
                    'onboarding_current_step' => 0,
                    'onboarding_skipped_cai' => 0,
                ]);
            
            echo "\n✅ Se actualizaron $affected registros\n\n";
            
            echo "SIGUIENTE PASO:\n";
            echo "1. php artisan config:clear\n";
            echo "2. Cierra sesión en el navegador\n";
            echo "3. Vuelve a iniciar sesión\n";
            echo "4. Deberías ver el wizard de onboarding\n\n";
        } else {
            echo "\nPuedes ejecutar el UPDATE manualmente después.\n\n";
        }
        
    } else {
        echo "╔══════════════════════════════════════════════════════════════════╗\n";
        echo "║  ⚠️  ADVERTENCIA                                                ║\n";
        echo "║  Algunas columnas no se pudieron agregar                        ║\n";
        echo "╚══════════════════════════════════════════════════════════════════╝\n\n";
    }
    
} catch (\Exception $e) {
    echo "\n❌ ERROR FATAL:\n";
    echo "────────────────────────────────────────────────────────────────\n";
    echo $e->getMessage() . "\n\n";
    echo $e->getTraceAsString() . "\n\n";
}
