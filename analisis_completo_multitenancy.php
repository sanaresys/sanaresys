<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "╔═══════════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║   ANÁLISIS COMPLETO SISTEMA MULTI-TENANT                       ║" . PHP_EOL;
echo "╚═══════════════════════════════════════════════════════════════════╝" . PHP_EOL . PHP_EOL;

// 1. Verificar estructura de tablas en BD central
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;
echo "1. TABLAS CON 'centro_id' EN BD CENTRAL" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;

tenancy()->end();

$tablasConCentroId = [];
$todasLasTablasCentral = DB::connection('mysql')->select("SHOW TABLES");

foreach ($todasLasTablasCentral as $tabla) {
    $tableName = array_values((array)$tabla)[0];
    
    $columnas = DB::connection('mysql')->select("SHOW COLUMNS FROM `{$tableName}` LIKE 'centro_id'");
    
    if (!empty($columnas)) {
        $tablasConCentroId[] = $tableName;
    }
}

echo "   Total tablas con 'centro_id': " . count($tablasConCentroId) . PHP_EOL;
foreach ($tablasConCentroId as $tabla) {
    echo "   ✓ {$tabla}" . PHP_EOL;
}

echo PHP_EOL;

// 2. Verificar estructura de tablas en BD tenant
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;
echo "2. TABLAS CON 'centro_id' EN BD TENANT" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;

$tenant = Tenant::first();

if ($tenant) {
    tenancy()->initialize($tenant);
    
    echo "   Analizando: {$tenant->id}" . PHP_EOL . PHP_EOL;
    
    $tablasConCentroIdTenant = [];
    $todasLasTablastenant = DB::connection('tenant')->select("SHOW TABLES");
    
    foreach ($todasLasTablastenant as $tabla) {
        $tableName = array_values((array)$tabla)[0];
        
        $columnas = DB::connection('tenant')->select("SHOW COLUMNS FROM `{$tableName}` LIKE 'centro_id'");
        
        if (!empty($columnas)) {
            $tablasConCentroIdTenant[] = $tableName;
        }
    }
    
    echo "   Total tablas con 'centro_id': " . count($tablasConCentroIdTenant) . PHP_EOL;
    
    if (empty($tablasConCentroIdTenant)) {
        echo "   ✅ CORRECTO: Ninguna tabla tenant tiene 'centro_id'" . PHP_EOL;
        echo "   (El contexto del tenant ya define el centro)" . PHP_EOL;
    } else {
        echo "   ⚠️ ADVERTENCIA: Estas tablas tienen 'centro_id':" . PHP_EOL;
        foreach ($tablasConCentroIdTenant as $tabla) {
            echo "   - {$tabla}" . PHP_EOL;
        }
        echo PHP_EOL;
        echo "   💡 RECOMENDACIÓN: Eliminar columna 'centro_id' de estas tablas" . PHP_EOL;
    }
    
    tenancy()->end();
} else {
    echo "   ⚠️ No hay tenants para analizar" . PHP_EOL;
}

echo PHP_EOL;

// 3. Buscar archivos con filtros por centro_id
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;
echo "3. PROBLEMAS COMUNES A CORREGIR" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL . PHP_EOL;

echo "❌ PROBLEMA: Código que filtra por 'centro_id' en contexto tenant" . PHP_EOL;
echo "   Ejemplo: \$query->where('centro_id', session('current_centro_id'));" . PHP_EOL;
echo PHP_EOL;

echo "✅ SOLUCIÓN: Eliminar estos filtros" . PHP_EOL;
echo "   - El contexto tenant ya filtra por centro automáticamente" . PHP_EOL;
echo "   - No se necesita 'centro_id' en tablas tenant" . PHP_EOL;
echo "   - Solo Root/Admin acceden a múltiples tenants" . PHP_EOL;
echo PHP_EOL;

echo "📋 ARCHIVOS QUE NECESITAN REVISIÓN:" . PHP_EOL;
echo "   • app/Filament/Resources/" . PHP_EOL;
echo "   • app/Filament/Widgets/" . PHP_EOL;
echo "   • app/Filament/Pages/" . PHP_EOL;
echo "   • app/Models/ (relaciones belongsTo)" . PHP_EOL;
echo PHP_EOL;

// 4. Verificar middleware tenancy
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;
echo "4. COMPONENTES DEL SISTEMA MULTI-TENANT" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL . PHP_EOL;

$componentesExisten = [
    'config/tenancy.php' => file_exists(__DIR__ . '/config/tenancy.php'),
    'app/Providers/TenancyServiceProvider.php' => file_exists(__DIR__ . '/app/Providers/TenancyServiceProvider.php'),
    'app/Observers/CentroMedicoObserver.php' => file_exists(__DIR__ . '/app/Observers/CentroMedicoObserver.php'),
    'database/migrations/tenant/' => is_dir(__DIR__ . '/database/migrations/tenant'),
];

foreach ($componentesExisten as $componente => $existe) {
    echo ($existe ? "   ✅" : "   ❌") . " {$componente}" . PHP_EOL;
}

echo PHP_EOL;

// 5. Recomendaciones
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;
echo "5. RECOMENDACIONES PARA COMPLETAR EL SISTEMA" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL . PHP_EOL;

echo "🔧 TAREAS PENDIENTES:" . PHP_EOL . PHP_EOL;

echo "1. ELIMINAR FILTROS POR centro_id:" . PHP_EOL;
echo "   - Buscar: ->where('centro_id', ...)" . PHP_EOL;
echo "   - Buscar: session('current_centro_id')" . PHP_EOL;
echo "   - Eliminar estos filtros en recursos/widgets/páginas" . PHP_EOL;
echo PHP_EOL;

echo "2. MIDDLEWARE/AUTENTICACIÓN:" . PHP_EOL;
echo "   - Usuario Root: Autenticación contra BD central" . PHP_EOL;
echo "   - Usuarios centro: Autenticación contra BD tenant" . PHP_EOL;
echo "   - Implementar selector de centro para Root" . PHP_EOL;
echo PHP_EOL;

echo "3. RELACIONES DE MODELOS:" . PHP_EOL;
echo "   - belongsTo(Centros_Medico::class) debe usar conexión 'mysql'" . PHP_EOL;
echo "   - Otras relaciones usan conexión del contexto actual" . PHP_EOL;
echo PHP_EOL;

echo "4. CAMPOS DE FORMULARIO:" . PHP_EOL;
echo "   - Eliminar campo 'centro_id' de formularios tenant" . PHP_EOL;
echo "   - Asignar centro_id automáticamente desde tenant actual" . PHP_EOL;
echo PHP_EOL;

echo "5. SEEDERS Y FACTORIES:" . PHP_EOL;
echo "   - Seeders deben ejecutarse en contexto correcto" . PHP_EOL;
echo "   - Factories no deben generar centro_id aleatorio" . PHP_EOL;
echo PHP_EOL;

echo "╔═══════════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║   ANÁLISIS COMPLETADO                                           ║" . PHP_EOL;
echo "╚═══════════════════════════════════════════════════════════════════╝" . PHP_EOL;
