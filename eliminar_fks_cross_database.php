<?php

require __DIR__ . '/vendor/autoload.php';

echo "=== ELIMINANDO FOREIGN KEYS ENTRE BD CENTRAL Y TENANT ===\n\n";

$archivosAModificar = [
    'database/migrations/tenant/2025_06_09_150257_create_citas_table.php',
    'database/migrations/tenant/2025_06_09_203101_create_consultas_table.php',
    'database/migrations/tenant/2025_06_09_203303_create_recetas_table.php',
    'database/migrations/tenant/2025_06_10_144710_create_examenes_table.php',
    'database/migrations/tenant/2025_07_24_203145_create_facturas_table.php',
    'database/migrations/tenant/2025_07_25_023327_create_recetarios_table.php',
    'database/migrations/tenant/2025_07_28_043831_create_contratos_medicos_table.php',
    'database/migrations/tenant/2025_08_01_000002_create_detalle_nominas_table.php',
];

$patrones = [
    // FK a medicos
    "/\s+\\\$table->foreign\('medico_id'\)->references\('id'\)->on\('medicos'\);/",
    "/\s+\\\$table->foreignId\('medico_id'\)->constrained\('medicos'\);/",
    "/\s+\\\$table->foreignId\('medico_id'\)->constrained\('medicos'\)->onDelete\('cascade'\);/",
    
    // FK a personas
    "/\s+\\\$table->foreign\('persona_id'\)->references\('id'\)->on\('personas'\);/",
    
    // FK a especialidades  
    "/\s+\\\$table->foreign\('especialidad_id'\)->references\('id'\)->on\('especialidads'\);/",
];

$totalModificados = 0;

foreach ($archivosAModificar as $archivo) {
    if (!file_exists($archivo)) {
        echo "  ⚠ No existe: {$archivo}\n";
        continue;
    }
    
    $contenidoOriginal = file_get_contents($archivo);
    $contenidoNuevo = $contenidoOriginal;
    
    $modificado = false;
    foreach ($patrones as $patron) {
        $contenidoNuevo = preg_replace($patron, '', $contenidoNuevo);
        if ($contenidoNuevo !== $contenidoOriginal) {
            $modificado = true;
            break;
        }
    }
    
    if ($modificado) {
        file_put_contents($archivo, $contenidoNuevo);
        echo "  ✓ Modificado: " . basename($archivo) . "\n";
        $totalModificados++;
    } else {
        echo "  - Sin cambios: " . basename($archivo) . "\n";
    }
}

echo "\n✓ Total de archivos modificados: {$totalModificados}\n";
echo "\n=== LIMPIEZA COMPLETADA ===\n";
