<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Centros_Medico;
use App\Models\Tenant;

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║          VERIFICAR Y CREAR TENANT PARA ONBOARDING               ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

// Obtener centro sin onboarding completado
$centro = Centros_Medico::on('mysql')
    ->whereNull('onboarding_completed_at')
    ->first();

if (!$centro) {
    echo "❌ No hay centros sin onboarding completado.\n\n";
    exit(1);
}

echo "🏥 Centro encontrado:\n";
echo "   ID: {$centro->id}\n";
echo "   Nombre: {$centro->nombre}\n\n";

// Verificar si existe tenant
$tenant = Tenant::where('centro_id', $centro->id)->first();

if (!$tenant) {
    echo "📦 Tenant NO existe. Creando...\n\n";
    
    try {
        // Crear tenant
        $tenant = Tenant::create([
            'id' => 'centro_' . $centro->id,
            'centro_id' => $centro->id,
        ]);
        
        echo "   ✅ Tenant creado: {$tenant->id}\n";
        
        // Crear base de datos
        echo "   ➤ Creando base de datos...\n";
        $tenant->createDatabase();
        echo "   ✅ Base de datos creada\n";
        
        // Ejecutar migraciones
        echo "   ➤ Ejecutando migraciones...\n";
        $tenant->run(function () {
            \Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force' => true,
            ]);
        });
        echo "   ✅ Migraciones ejecutadas\n\n";
        
    } catch (\Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n\n";
        exit(1);
    }
} else {
    echo "✅ Tenant ya existe: {$tenant->id}\n\n";
}

// Verificar estructuras de tablas
echo "📊 Verificando tablas en tenant DB:\n";

try {
    tenancy()->initialize($tenant);
    
    // Verificar tabla cai_autorizaciones
    $hasCaiTable = DB::connection('tenant')->getSchemaBuilder()->hasTable('cai_autorizaciones');
    echo "   cai_autorizaciones: " . ($hasCaiTable ? '✅ Existe' : '❌ No existe') . "\n";
    
    if ($hasCaiTable) {
        $columns = DB::connection('tenant')->select("SHOW COLUMNS FROM cai_autorizaciones");
        echo "   Columnas de cai_autorizaciones:\n";
        foreach ($columns as $col) {
            echo "      • {$col->Field} ({$col->Type})\n";
        }
    }
    
    echo "\n";
    
    // Verificar tabla servicios
    $hasServiciosTable = DB::connection('tenant')->getSchemaBuilder()->hasTable('servicios');
    echo "   servicios: " . ($hasServiciosTable ? '✅ Existe' : '❌ No existe') . "\n";
    
    tenancy()->end();
    
    echo "\n✅ Verificación completada\n";
    echo "\n🎯 El tenant está listo para el onboarding.\n";
    echo "   Puedes continuar probando el wizard.\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error verificando tablas: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n\n";
    
    if (tenancy()->initialized) {
        tenancy()->end();
    }
}
