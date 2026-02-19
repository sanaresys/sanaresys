<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Centros_Medico;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

echo "=== CREANDO CENTROS ADICIONALES ===" . PHP_EOL . PHP_EOL;

// Crear dos centros más
$centros = [
    [
        'nombre_centro' => 'Clínica Santa María',
        'direccion' => 'Av. Principal 456, Tegucigalpa',
        'telefono' => '2222-3333',
        'email' => 'contacto@santamaria.hn',
        'rtn' => '08019876543212',
    ],
    [
        'nombre_centro' => 'Centro Médico Valle',
        'direccion' => 'Residencial El Valle, San Pedro Sula',
        'telefono' => '2550-4444',
        'email' => 'info@clinicavalle.hn',
        'rtn' => '08019876543213',
    ],
];

foreach ($centros as $centroData) {
    echo "Creando centro: {$centroData['nombre_centro']}..." . PHP_EOL;
    
    $centro = Centros_Medico::create($centroData);
    
    echo "   ✓ Centro creado con ID: {$centro->id}" . PHP_EOL;
    
    // Esperar un momento para que el observer procese
    sleep(1);
    
    // Verificar que el tenant fue creado
    $tenant = Tenant::find('centro_' . $centro->id);
    if ($tenant) {
        echo "   ✓ Tenant creado: {$tenant->id}" . PHP_EOL;
        
        // Verificar que la BD existe
        $dbName = 'centro_' . $centro->id;
        $dbExists = DB::select("SHOW DATABASES LIKE '$dbName'");
        if (!empty($dbExists)) {
            echo "   ✓ Base de datos creada: $dbName" . PHP_EOL;
        } else {
            echo "   ❌ Base de datos NO creada: $dbName" . PHP_EOL;
        }
    } else {
        echo "   ❌ Tenant NO creado" . PHP_EOL;
    }
    
    echo PHP_EOL;
}

// Resumen final
echo "═══════════════════════════════════════" . PHP_EOL;
echo "RESUMEN DE CENTROS Y TENANTS" . PHP_EOL;
echo "═══════════════════════════════════════" . PHP_EOL;

$allCentros = Centros_Medico::all();
$allTenants = Tenant::all();

echo "Total centros médicos: " . $allCentros->count() . PHP_EOL;
echo "Total tenants: " . $allTenants->count() . PHP_EOL;
echo PHP_EOL;

foreach ($allCentros as $centro) {
    echo "   - {$centro->nombre_centro} (ID: {$centro->id})" . PHP_EOL;
    $tenant = Tenant::find('centro_' . $centro->id);
    if ($tenant) {
        echo "     └─ Tenant: {$tenant->id} ✓" . PHP_EOL;
    } else {
        echo "     └─ Tenant: NO CREADO ❌" . PHP_EOL;
    }
}

echo PHP_EOL . "✓ Proceso completado" . PHP_EOL;
