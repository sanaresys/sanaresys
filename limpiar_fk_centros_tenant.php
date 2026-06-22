<?php

echo "=== REMOVIENDO TODAS LAS FK A CENTROS_MEDICOS ===\n\n";

$archivosModificar = [
    'database/migrations/tenant/2025_06_02_234551_create_especialidads_table.php',
    'database/migrations/tenant/2025_06_05_23455_create_medicos_table.php',
    'database/migrations/tenant/2025_06_05_24000_create_centros_medicos_medicos_table.php',
    'database/migrations/tenant/2025_06_20_170359_create_especialidad_medicos_table.php',
];

foreach ($archivosModificar as $archivo) {
    if (!file_exists($archivo)) {
        echo "  ⚠ No existe: {$archivo}\n";
        continue;
    }
    
    $contenido = file_get_contents($archivo);
    $original = $contenido;
    
    // Patrón más específico
    $contenido = preg_replace(
        '/\$table->foreign\("centro_id"\)->references\("id"\)->on\("centros_medicos"\);\\s*/', 
        '', 
        $contenido
    );
    
    if ($contenido !== $original) {
        file_put_contents($archivo, $contenido);
        echo "  ✓ " . basename($archivo) . "\n";
    } else {
        echo "  - Sin cambios: " . basename($archivo) . "\n";
    }
}

echo "\n✓ Limpieza completada\n";
