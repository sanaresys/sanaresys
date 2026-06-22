<?php

echo "=== REMOVIENDO FK A CENTROS_MEDICOS DE MIGRACIONES TENANT ===\n\n";

$archivos = [
    'database/migrations/tenant/0001_01_02_000000_create_users_table.php',
    'database/migrations/tenant/2025_06_01_192356_create_permission_tables.php',
    'database/migrations/tenant/2025_06_02_234551_create_especialidads_table.php',
    'database/migrations/tenant/2025_06_05_23455_create_medicos_table.php',
    'database/migrations/tenant/2025_06_05_24000_create_centros_medicos_medicos_table.php',
    'database/migrations/tenant/2025_06_20_170359_create_especialidad_medicos_table.php',
];

$patrones = [
    "/\s+\\\$table->foreign\(\"?centro_id\"?\)->references\('?id'?\)->on\('?centros_medicos'?\);/",
    "/\s+\\\$table->foreign\('?centro_medico_id'?\)->references\('?id'?\)->on\('?centros_medicos'?\);/",
    "/\s+\\\$table->foreignId\('?centro_id'?\)->constrained\('?centros_medicos'?\);/",
];

$totalModificados = 0;

foreach ($archivos as $archivo) {
    if (!file_exists($archivo)) {
        echo "  ⚠ No existe: " . basename($archivo) . "\n";
        continue;
    }
    
    $contenido = file_get_contents($archivo);
    $contenidoOriginal = $contenido;
    
    foreach ($patrones as $patron) {
        $contenido = preg_replace($patron, '', $contenido);
    }
    
    if ($contenido !== $contenidoOriginal) {
        file_put_contents($archivo, $contenido);
        echo "  ✓ Modificado: " . basename($archivo) . "\n";
        $totalModificados++;
    } else {
        echo "  - Sin cambios: " . basename($archivo) . "\n";
    }
}

echo "\n✓ Total de archivos modificados: {$totalModificados}\n";
echo "\n=== FK REMOVIDAS - CENTROS_MEDICOS ESTARÁ EN BD CENTRAL ===\n";
