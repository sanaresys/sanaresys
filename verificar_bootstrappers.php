<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;

echo "=== VERIFICANDO BOOTSTRAPPERS ===\n\n";

$tenant = Tenant::find('centro_1');

echo "1. Bootstrappers configurados:\n";
$bootstrappers = config('tenancy.bootstrappers');
foreach ($bootstrappers as $i => $bootstrapper) {
    echo "   " . ($i + 1) . ". " . class_basename($bootstrapper) . "\n";
    echo "      {$bootstrapper}\n";
}

echo "\n2. Inicializando tenant y verificando bootstrappers...\n";

try {
    // Agregar logs temporales
    $original_initialize = new ReflectionMethod('Stancl\Tenancy\Tenancy', 'initialize');
    
    tenancy()->initialize($tenant);
    echo "   ✓ Inicializado sin errores\n\n";
    
    // Verificar estado actual  
    echo "3. Estado actual de tenancy():\n";
    echo "   - Tenant activo: " . (tenancy()->tenant ? tenancy()->tenant->id : 'null') . "\n";
    echo "   - Tenant key: " . (tenancy()->tenant ? tenancy()->tenant->getTenantKey() : 'null') . "\n";
    
    // Intentar obtener los bootstrappers activos
    $reflection = new ReflectionClass('Stancl\Tenancy\Tenancy');
    $property = $reflection->getProperty('bootstrappers');
    $property->setAccessible(true);
    $activeBootstrappers = $property->getValue(tenancy());
    
    echo "\n4. Bootstrappers instanciados:\n";
    if ($activeBootstrappers) {
        foreach ($activeBootstrappers as $bootstrapper) {
            echo "   - " . get_class($bootstrapper) . "\n";
        }
    } else {
        echo "   - NULL o vacío\n";
    }
    
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== FIN ===\n";
