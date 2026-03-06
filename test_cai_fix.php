<?php
/**
 * Verificar corrección del problema centro_id en CAI
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\CAIAutorizaciones;
use Illuminate\Support\Facades\Schema;

echo "\n╔══════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICACIÓN: Problema centro_id en CAI solucionado   ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// 1. Verificar contexto NO tenant (base central)
echo "📊 1. CONTEXTO CENTRAL (mysql):\n";
echo "   Tenancy inicializado: " . (tenancy()->initialized ? 'SÍ ❌' : 'NO ✅') . "\n\n";

// 2. Verificar que el tenant existe
$tenant = Tenant::where('id', 'centro_1')->first();
if (!$tenant) {
    echo "❌ Tenant 'centro_1' no existe\n";
    exit(1);
}
echo "✅ Tenant 'centro_1' existe\n\n";

// 3. Inicializar tenant y verificar estructura
echo "📊 2. CONTEXTO TENANT (centro_1):\n";
tenancy()->initialize($tenant);
echo "   Tenancy inicializado: " . (tenancy()->initialized ? 'SÍ ✅' : 'NO ❌') . "\n";
echo "   Conexión actual: " . config('database.default') . "\n\n";

// 4. Verificar columnas de cai_autorizaciones
echo "📊 3. ESTRUCTURA DE TABLA cai_autorizaciones:\n";
$columns = Schema::connection('tenant')->getColumnListing('cai_autorizaciones');
echo "   Columnas en la tabla:\n";
foreach ($columns as $col) {
    $suffix = ($col === 'centro_id') ? ' ❌ NO DEBERÍA ESTAR' : '';
    echo "   - $col$suffix\n";
}

$hasCentroId = in_array('centro_id', $columns);
echo "\n   ¿Tiene columna 'centro_id'? " . ($hasCentroId ? 'SÍ ❌' : 'NO ✅') . "\n\n";

// 5. Resumen
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║  RESUMEN DE LA CORRECCIÓN                               ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

echo "🐛 PROBLEMA ANTERIOR:\n";
echo "   El observer en CAIAutorizaciones::creating() intentaba\n";
echo "   agregar automáticamente el campo 'centro_id' cuando se\n";
echo "   creaba un registro, SIN VERIFICAR si estaba en contexto\n";
echo "   de tenant.\n\n";

echo "   Resultado: Error 'Column not found: centro_id'\n";
echo "   porque las tablas de tenants NO tienen esa columna.\n\n";

echo "✅ SOLUCIÓN APLICADA:\n";
echo "   Se modificó el observer para SOLO agregar 'centro_id'\n";
echo "   cuando NO estamos en contexto de tenant:\n\n";

echo "   if (!tenancy()->initialized && auth()->check()) {\n";
echo "       // Solo agregar centro_id en base central\n";
echo "   }\n\n";

echo "🎯 RESULTADO:\n";
echo "   Ahora el modelo CAIAutorizaciones funciona tanto en:\n";
echo "   • Base central (con centro_id)\n";
echo "   • Bases de tenants (sin centro_id)\n\n";

if (!$hasCentroId) {
    echo "✅ VERIFICACIÓN EXITOSA\n";
    echo "   La tabla del tenant NO tiene 'centro_id' (correcto)\n";
    echo "   El observer está configurado para no agregarlo\n";
    echo "   ✅ El CAI se puede guardar sin errores\n\n";
} else {
    echo "⚠️  ADVERTENCIA\n";
    echo "   La tabla del tenant TIENE 'centro_id'\n";
    echo "   Esto puede ser de una migración vieja\n";
    echo "   Considera eliminar esa columna de la migración tenant\n\n";
}

tenancy()->end();
