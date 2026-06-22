<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== LIMPIANDO MIGRACIONES INCORRECTAS ===\n\n";

// Migraciones que son de TENANT y no deberían estar en la BD central
$tenantMigrations = [
    '2025_06_04_194309_create_enfermedades_table',
    '2025_06_06_151211_create_pacientes_table',
    '2025_06_06_213734_create_enfermedades_pacientes_table',
    '2025_06_09_150257_create_citas_table',
    '2025_06_09_203101_create_consultas_table',
    '2025_06_09_203303_create_recetas_table',
    '2025_06_10_144710_create_examenes_table',
    '2025_07_06_000001_make_medico_id_nullable_in_recetas_table',
    '2025_07_06_000002_make_consulta_id_nullable_in_recetas_table',
    '2025_07_06_000003_make_cita_id_nullable_in_consultas_table',
    '2025_07_24_202828_create_descuentos_table',
    '2025_07_24_202848_create_impuestos_table',
    '2025_07_24_203034_create_cai_autorizaciones_table',
    '2025_07_24_203116_create_servicios_table',
    '2025_07_24_203143_create_cai_correlativos_table',
    '2025_07_24_203144_create_tipo_pagos_table',
    '2025_07_24_203145_create_facturas_table',
    '2025_07_25_023327_create_recetarios_table',
    '2025_07_25_023845_create_pagos_facturas_table',
    '2025_07_25_023846_create_cuentas_por_cobrars_table',
    '2025_07_25_023926_create_factura_detalles_table',
    '2025_07_28_043831_create_contratos_medicos_table',
    '2025_08_01_000001_create_nominas_table',
    '2025_08_01_000002_create_detalle_nominas_table',
];

echo "1. Migraciones de tenant a eliminar de BD central: " . count($tenantMigrations) . "\n\n"; 

foreach ($tenantMigrations as $migration) {
    $result = DB::connection('mysql')->table('migrations')
        ->where('migration', $migration)
        ->delete();
    
    if ($result) {
        echo "   - Eliminado: {$migration}\n";
    }
}

// Verificar cuántas quedan
echo "\n2. Migraciones restantes en BD central:\n";
$remaining = DB::connection('mysql')->table('migrations')->get();
echo "   Total: " . count($remaining) . "\n";
foreach ($remaining as $mig) {
    echo "   - {$mig->migration}\n";
}

// Eliminar tablas de tenant de BD central (si existen)
echo "\n3. Verificando tablas de tenant en BD central...\n";
$tenantTables = [
    'enfermedades', 'pacientes', 'enfermedades__pacientes', 'citas', 'consultas',
    'recetas', 'examenes', 'descuentos', 'impuestos', 'cai_autorizaciones',
    'servicios', 'cai_correlativos', 'tipo_pagos', 'facturas', 'recetarios',
    'pagos_facturas', 'cuentas_por_cobrars', 'factura_detalles', 'contratos_medicos',
    'nominas', 'detalle_nominas'
];

foreach ($tenantTables as $table) {
    try {
        DB::connection('mysql')->statement("DROP TABLE IF EXISTS `{$table}`");
        echo "   - Eliminada tabla: {$table}\n";
    } catch (\Exception $e) {
        // La tabla no existe, está bien
    }
}

echo "\n=== LIMPIEZA COMPLETA ===\n";
echo "Ahora ejecuta: php artisan tenants:migrate --tenants=centro_1\n";
