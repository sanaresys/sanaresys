<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;

echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;
echo "APLICANDO CORRECCIONES AL SISTEMA MULTI-TENANT" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL . PHP_EOL;

// 1. Ejecutar migración en todos los tenants
echo "1. Eliminando columna 'centro_id' de tablas tenant..." . PHP_EOL . PHP_EOL;

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "   Procesando: {$tenant->id}..." . PHP_EOL;
    
    tenancy()->initialize($tenant);
    
    try {
        // Ejecutar la migración específica
        Artisan::call('migrate', [
            '--path' => 'database/migrations/tenant/2026_02_19_000001_remove_centro_id_from_tenant_tables.php',
            '--force' => true
        ]);
        
        echo "   ✓ Migración aplicada exitosamente" . PHP_EOL;
    } catch (\Exception $e) {
        echo "   ⚠️ Error: " . $e->getMessage() . PHP_EOL;
    }
    
    tenancy()->end();
    echo PHP_EOL;
}

// 2. Verificar resultado
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL;
echo "2. VERIFICANDO RESULTADO" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════════════" . PHP_EOL . PHP_EOL;

foreach ($tenants as $tenant) {
    tenancy()->initialize($tenant);
    
    echo "   Tenant: {$tenant->id}" . PHP_EOL;
    
    $tablesWithCentroId = 0;
    $tables = ['users', 'medicos', 'roles', 'especialidads', 'especialidad_medicos', 'centros_medicos_medicos'];
    
    foreach ($tables as $table) {
        $hasColumn = \Illuminate\Support\Facades\Schema::hasColumn($table, 'centro_id');
        if ($hasColumn) {
            $tablesWithCentroId++;
            echo "      ⚠️ {$table} todavía tiene 'centro_id'" . PHP_EOL;
        }
    }
    
    if ($tablesWithCentroId === 0) {
        echo "      ✅ Todas las columnas 'centro_id' eliminadas correctamente" . PHP_EOL;
    }
    
    tenancy()->end();
    echo PHP_EOL;
}

echo "╔═══════════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║   CORRECCIONES APLICADAS                                        ║" . PHP_EOL;
echo "╚═══════════════════════════════════════════════════════════════════╝" . PHP_EOL . PHP_EOL;

echo "📋 SIGUIENTES PASOS MANUALES:" . PHP_EOL . PHP_EOL;

echo "1. Eliminar referencias a 'centro_id' en código:" . PHP_EOL;
echo "   - Buscar: session('current_centro_id')" . PHP_EOL;
echo "   - Buscar: ->where('centro_id'" . PHP_EOL;
echo "   - Buscar: ['centro_id' =>" . PHP_EOL;
echo "   - Reemplazar con lógica basada en contexto tenant" . PHP_EOL;
echo PHP_EOL;

echo "2. Actualizar recursos Filament:" . PHP_EOL;
echo "   - Eliminar campos 'centro_id' de formularios" . PHP_EOL;
echo "   - Eliminar filtros por centro_id en queries" . PHP_EOL;
echo "   - Confiar en el contexto tenant para filtrado" . PHP_EOL;
echo PHP_EOL;

echo "3. Actualizar modelos:" . PHP_EOL;
echo "   - Eliminar 'centro_id' de \$fillable" . PHP_EOL;
echo "   - Actualizar relaciones si es necesario" . PHP_EOL;
echo PHP_EOL;
