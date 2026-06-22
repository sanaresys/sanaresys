<?php

/*
 * Script para actualizar modelos tenant
 * Elimina TenantScoped y centro_id de modelos que estarán en la BD del tenant
 */

$modelos_tenant = [
    'Citas',
    'Consulta',
    'Receta',
    'Examenes',
    'Enfermedades__Paciente',
    'Factura',
    'FacturaDetalle',
    'PagosFactura',
    'CuentasPorCobrar',
    'Recetario',
    'TipoPago',
    'Servicio',
    'Impuesto',
    'Descuento',
    'CAIAutorizacion',
    'CAICorrelativo',
    'ContratoMedico',
    'Nomina',
    'DetalleNomina',
];

foreach ($modelos_tenant as $modelo) {
    $file = __DIR__ . "/app/Models/{$modelo}.php";
    
    if (!file_exists($file)) {
        echo "⚠️  No existe: {$modelo}.php\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $original = $content;
    
    // 1. Eliminar la línea "use TenantScoped;"
    $content = preg_replace('/use TenantScoped;.*?\n/', "// TenantScoped NO se usa - el contexto del tenant define el centro\n", $content);
    
    // 2. Eliminar import del trait
    $content = preg_replace('/use App\\\\Models\\\\Traits\\\\TenantScoped;\s*\n/', '', $content);
    
    // 3. Eliminar 'centro_id' de $fillable
    $content = preg_replace('/\s*\'centro_id\',.*?\n/', "\n", $content);
    
    // 4. Eliminar protected string $tenantKeyName
    $content = preg_replace('/\s*protected string \$tenantKeyName.*?\n/', '', $content);
    
    // 5. Eliminar método bootTenantScoped si existe
    $content = preg_replace('/\n\s*protected static function bootTenantScoped\(\).*?\n\s*\{.*?\}\n/s', '', $content);
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "✓ Actualizado: {$modelo}.php\n";
    } else {
        echo "  Sin cambios: {$modelo}.php\n";
    }
}

echo "\n✓ Proceso completado\n";
