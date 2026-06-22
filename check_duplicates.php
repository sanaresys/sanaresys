<?php

require_once 'vendor/autoload.php';

use App\Models\FacturaDetalle;
use Illuminate\Support\Facades\DB;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Verificando duplicados en factura_detalles...\n";

// Verificar registros duplicados
$duplicados = DB::select("
    SELECT consulta_id, servicio_id, COUNT(*) as total 
    FROM factura_detalles 
    WHERE factura_id IS NULL 
    GROUP BY consulta_id, servicio_id 
    HAVING COUNT(*) > 1
");

if (count($duplicados) > 0) {
    echo "¡ENCONTRADOS REGISTROS DUPLICADOS!\n";
    foreach ($duplicados as $dup) {
        echo "Consulta ID: {$dup->consulta_id}, Servicio ID: {$dup->servicio_id}, Total: {$dup->total}\n";
        
        // Mostrar detalles de los registros duplicados
        $detalles = DB::select("
            SELECT id, created_at 
            FROM factura_detalles 
            WHERE consulta_id = ? AND servicio_id = ? AND factura_id IS NULL
            ORDER BY created_at
        ", [$dup->consulta_id, $dup->servicio_id]);
        
        echo "  Registros:\n";
        foreach ($detalles as $detalle) {
            echo "    ID: {$detalle->id}, Creado: {$detalle->created_at}\n";
        }
        echo "\n";
    }
} else {
    echo "No se encontraron registros duplicados.\n";
}

// También verificar el registro específico del error
echo "\nVerificando consulta_id = 3, servicio_id = 5:\n";
$registros = DB::select("
    SELECT id, factura_id, created_at 
    FROM factura_detalles 
    WHERE consulta_id = 3 AND servicio_id = 5
");

foreach ($registros as $reg) {
    echo "ID: {$reg->id}, Factura ID: {$reg->factura_id}, Creado: {$reg->created_at}\n";
}

echo "\nFin de verificación.\n";
