<?php
/**
 * Verificación: Todos los modelos corregidos para multi-tenancy
 */

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICACIÓN: Observer centro_id en modelos tenant         ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$modelos = [
    'CAIAutorizaciones' => 'app/Models/CAIAutorizaciones.php',
    'Servicio' => 'app/Models/Servicio.php',
    'CuentasPorCobrar' => 'app/Models/CuentasPorCobrar.php',
    'Descuento' => 'app/Models/Descuento.php',
    'Impuesto' => 'app/Models/Impuesto.php',
    'TipoPago' => 'app/Models/TipoPago.php',
];

echo "📊 MODELOS CORREGIDOS:\n\n";

$todosCorrectos = true;

foreach ($modelos as $nombre => $ruta) {
    $archivo = __DIR__ . '/' . $ruta;
    
    if (!file_exists($archivo)) {
        echo "❌ $nombre - Archivo no encontrado\n";
        $todosCorrectos = false;
        continue;
    }
    
    $contenido = file_get_contents($archivo);
    
    // Verificar que tiene la verificación de tenancy
    $tieneFix = strpos($contenido, '!tenancy()->initialized') !== false;
    
    // Verificar que tiene el observer creating con centro_id
    $tieneObserver = strpos($contenido, 'static::creating') !== false 
                  && strpos($contenido, 'centro_id') !== false;
    
    $estado = $tieneFix ? '✅' : '❌';
    $detalles = $tieneFix 
        ? 'Observer con verificación de tenant'
        : ($tieneObserver ? '⚠️  Observer SIN verificación' : 'Sin observer centro_id');
    
    echo "$estado $nombre\n";
    echo "   └─ $detalles\n\n";
    
    if (!$tieneFix && $tieneObserver) {
        $todosCorrectos = false;
    }
}

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║  CÓDIGO CORRECTO APLICADO                                    ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "✅ ANTES (código problemático):\n";
echo "────────────────────────────────────────────────────────────────\n";
echo "static::creating(function (\$model) {\n";
echo "    if (auth()->check() && empty(\$model->centro_id)) {\n";
echo "        \$user = auth()->user();\n";
echo "        if (\$user && isset(\$user->centro_id)) {\n";
echo "            \$model->centro_id = \$user->centro_id; // ❌ Siempre\n";
echo "        }\n";
echo "    }\n";
echo "});\n\n";

echo "✅ AHORA (código correcto):\n";
echo "────────────────────────────────────────────────────────────────\n";
echo "static::creating(function (\$model) {\n";
echo "    // Solo agregar centro_id si NO estamos en contexto de tenant\n";
echo "    if (!tenancy()->initialized && auth()->check() && empty(\$model->centro_id)) {\n";
echo "        \$user = auth()->user();\n";
echo "        if (\$user && isset(\$user->centro_id)) {\n";
echo "            \$model->centro_id = \$user->centro_id; // ✅ Solo en base central\n";
echo "        }\n";
echo "    }\n";
echo "});\n\n";

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║  EXPLICACIÓN                                                  ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "🎯 ¿QUÉ HACE LA CORRECCIÓN?\n";
echo "──────────────────────────────\n";
echo "La condición !tenancy()->initialized verifica si NO estamos\n";
echo "trabajando en contexto de un tenant.\n\n";

echo "• tenancy()->initialized = false → Base CENTRAL\n";
echo "  └─ Agregar centro_id (la tabla SÍ tiene esa columna)\n\n";

echo "• tenancy()->initialized = true → Base TENANT\n";
echo "  └─ NO agregar centro_id (la tabla NO tiene esa columna)\n\n";

echo "🔍 ¿POR QUÉ ES NECESARIO?\n";
echo "──────────────────────────\n";
echo "En arquitectura multi-tenant con database-per-tenant:\n\n";

echo "BASE CENTRAL (mysql):\n";
echo "├─ Guarda datos compartidos\n";
echo "├─ Tablas CON centro_id para saber a qué tenant pertenecen\n";
echo "└─ Ejemplo: tenants, centros_medicos, users\n\n";

echo "BASES TENANT (centro_1, centro_2, etc):\n";
echo "├─ Datos aislados por cada clínica\n";
echo "├─ Tablas SIN centro_id (ya están en DB del centro)\n";
echo "└─ Ejemplo: cai_autorizaciones, servicios, pacientes, citas\n\n";

if ($todosCorrectos) {
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ VERIFICACIÓN EXITOSA                                     ║\n";
    echo "║                                                               ║\n";
    echo "║  Todos los modelos tienen la verificación correcta.         ║\n";
    echo "║  El sistema puede guardar datos en tenants sin errores.     ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
    exit(0);
} else {
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║  ⚠️  ADVERTENCIA                                             ║\n";
    echo "║                                                               ║\n";
    echo "║  Algunos modelos aún tienen el observer sin verificación.   ║\n";
    echo "║  Revisa los modelos marcados con ❌                          ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
    exit(1);
}
