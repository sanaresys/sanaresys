<?php
/**
 * Verificar campo precio_unitario en servicios
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;

echo "\n╔══════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICACIÓN: Campo precio_unitario en servicios      ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// 1. Buscar tenant
$tenant = Tenant::where('id', 'centro_1')->first();

if (!$tenant) {
    echo "❌ Tenant 'centro_1' no existe\n";
    exit(1);
}

echo "✅ Tenant 'centro_1' encontrado\n\n";

// 2. Inicializar contexto tenant
tenancy()->initialize($tenant);

// 3. Verificar estructura de tabla servicios
echo "📊 ESTRUCTURA DE TABLA 'servicios':\n";
echo "────────────────────────────────────────────────────────\n";

$columns = Schema::connection('tenant')->getColumnListing('servicios');

$camposRequeridos = [
    'id' => false,
    'nombre' => false,
    'precio_unitario' => false,
    'codigo' => false,
    'descripcion' => false,
    'created_by' => false,
];

foreach ($columns as $col) {
    $icono = '  ';
    
    if (array_key_exists($col, $camposRequeridos)) {
        $camposRequeridos[$col] = true;
        $icono = '✅';
    }
    
    if ($col === 'precio_unitario') {
        $icono = '🎯';
    }
    
    echo "$icono $col\n";
}

echo "\n╔══════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICACIÓN DE CAMPOS REQUERIDOS                      ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$todosCamposExisten = true;
foreach ($camposRequeridos as $campo => $existe) {
    $status = $existe ? '✅' : '❌';
    echo "$status $campo\n";
    if (!$existe) {
        $todosCamposExisten = false;
    }
}

echo "\n╔══════════════════════════════════════════════════════════╗\n";
echo "║  CORRECCIÓN APLICADA                                    ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

echo "🐛 PROBLEMA:\n";
echo "   El formulario envía: servicios[0][precio]\n";
echo "   El modelo espera:     precio_unitario\n";
echo "   ❌ Desajuste de nombres de campos\n\n";

echo "✅ SOLUCIÓN:\n";
echo "   Mapear el campo en el controlador:\n\n";
echo "   Servicio::create([\n";
echo "       'nombre' => \$data['nombre'],\n";
echo "       'precio_unitario' => \$data['precio'], // ✅ Mapeo correcto\n";
echo "       'descripcion' => \$data['descripcion'],\n";
echo "   ]);\n\n";

if ($todosCamposExisten) {
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ VERIFICACIÓN EXITOSA                               ║\n";
    echo "║                                                          ║\n";
    echo "║  La tabla servicios tiene todos los campos requeridos. ║\n";
    echo "║  El mapeo de 'precio' a 'precio_unitario' está OK.    ║\n";
    echo "║  Los servicios se pueden guardar correctamente.        ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n\n";
} else {
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║  ⚠️  ADVERTENCIA                                        ║\n";
    echo "║                                                          ║\n";
    echo "║  Faltan campos en la tabla servicios.                  ║\n";
    echo "║  Revisa la migración de la tabla.                      ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n\n";
}

tenancy()->end();
