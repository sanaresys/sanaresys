<?php

require_once 'vendor/autoload.php';

use App\Models\FacturaDetalle;
use App\Models\Consulta;
use Illuminate\Support\Facades\DB;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DIAGNÓSTICO DEL PROBLEMA ===\n\n";

// Verificar consulta ID 3
echo "1. Verificando consulta ID 3:\n";
$consulta = Consulta::find(3);
if ($consulta) {
    echo "   ✓ Consulta existe - Centro ID: {$consulta->centro_id}\n";
} else {
    echo "   ✗ Consulta NO existe\n";
    exit;
}

// Verificar servicios en consulta 3
echo "\n2. Servicios en consulta 3:\n";
$servicios = FacturaDetalle::where('consulta_id', 3)->get();
echo "   Total registros: " . $servicios->count() . "\n";

if ($servicios->count() > 0) {
    foreach ($servicios as $servicio) {
        echo "   - ID: {$servicio->id}, Servicio: {$servicio->servicio_id}, Factura: " . 
             ($servicio->factura_id ? $servicio->factura_id : 'NULL') . "\n";
    }
} else {
    echo "   No hay servicios registrados para esta consulta\n";
}

// Verificar servicios temporales (sin factura)
echo "\n3. Servicios temporales (sin factura) en consulta 3:\n";
$temporales = FacturaDetalle::where('consulta_id', 3)->whereNull('factura_id')->get();
echo "   Total temporales: " . $temporales->count() . "\n";

// Verificar todos los registros en factura_detalles
echo "\n4. Todos los registros en factura_detalles:\n";
$todos = FacturaDetalle::all();
echo "   Total registros en tabla: " . $todos->count() . "\n";

if ($todos->count() > 0) {
    foreach ($todos as $detalle) {
        echo "   - ID: {$detalle->id}, Consulta: {$detalle->consulta_id}, " .
             "Servicio: {$detalle->servicio_id}, Factura: " . 
             ($detalle->factura_id ? $detalle->factura_id : 'NULL') . "\n";
    }
}

echo "\n=== FIN DIAGNÓSTICO ===\n";
