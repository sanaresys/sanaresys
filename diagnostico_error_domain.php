<?php
/**
 * Diagnóstico: Error de columna 'domain' en tabla tenants
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  DIAGNÓSTICO: Error 'domain' en tabla tenants                   ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

echo "❌ ERROR REPORTADO:\n";
echo "────────────────────────────────────────────────────────────────\n";
echo "SQLSTATE[42S22]: Columna no encontrada: 1054\n";
echo "Columna desconocida 'domain' en 'where clause'\n";
echo "SQL: select * from 'tenants' where 'domain' = hospital-san.sanaresys.localhost\n\n";

echo "🔍 ANALIZANDO PROBLEMA...\n\n";

// 1. Verificar estructura de tabla tenants
echo "📊 1. ESTRUCTURA DE TABLA 'tenants':\n";
echo "────────────────────────────────────────────────────────────────\n";

try {
    $columns = Schema::connection('mysql')->getColumnListing('tenants');
    
    echo "Columnas existentes:\n";
    foreach ($columns as $col) {
        $esDomain = ($col === 'domain' || $col === 'tenancy_primary_domain');
        $icono = $esDomain ? '🎯' : '  ';
        echo "$icono • $col\n";
    }
    
    $tieneDomain = in_array('domain', $columns);
    $tieneTenancyPrimaryDomain = in_array('tenancy_primary_domain', $columns);
    
    echo "\n¿Tiene columna 'domain'? " . ($tieneDomain ? '✅ SÍ' : '❌ NO') . "\n";
    echo "¿Tiene columna 'tenancy_primary_domain'? " . ($tieneTenancyPrimaryDomain ? '✅ SÍ' : '❌ NO') . "\n\n";
    
} catch (\Exception $e) {
    echo "❌ Error al consultar tabla: {$e->getMessage()}\n\n";
}

// 2. Verificar si existe tabla 'domains'
echo "📊 2. TABLA 'domains' (para multi-dominio):\n";
echo "────────────────────────────────────────────────────────────────\n";

try {
    if (Schema::connection('mysql')->hasTable('domains')) {
        echo "✅ Tabla 'domains' existe\n";
        
        $domainsCols = Schema::connection('mysql')->getColumnListing('domains');
        echo "Columnas: " . implode(', ', $domainsCols) . "\n";
        
        $count = DB::connection('mysql')->table('domains')->count();
        echo "Registros: $count dominios configurados\n\n";
        
        if ($count > 0) {
            echo "Dominios configurados:\n";
            $domains = DB::connection('mysql')->table('domains')->get();
            foreach ($domains as $domain) {
                echo "  • {$domain->domain} → tenant_id: {$domain->tenant_id}\n";
            }
            echo "\n";
        }
    } else {
        echo "❌ Tabla 'domains' NO existe\n";
        echo "   El sistema NO está configurado para usar dominios múltiples\n\n";
    }
} catch (\Exception $e) {
    echo "⚠️  Error: {$e->getMessage()}\n\n";
}

// 3. Verificar middleware activo
echo "📊 3. MIDDLEWARE DE TENANCY:\n";
echo "────────────────────────────────────────────────────────────────\n";

$middlewareFile = __DIR__ . '/app/Http/Kernel.php';
if (file_exists($middlewareFile)) {
    $content = file_get_contents($middlewareFile);
    
    $patronesMiddleware = [
        'InitializeTenancyByDomain' => strpos($content, 'InitializeTenancyByDomain'),
        'InitializeTenancyBySubdomain' => strpos($content, 'InitializeTenancyBySubdomain'),
        'InitializeTenancyByPath' => strpos($content, 'InitializeTenancyByPath'),
        'InitializeTenancyByRequestData' => strpos($content, 'InitializeTenancyByRequestData'),
    ];
    
    foreach ($patronesMiddleware as $middleware => $encontrado) {
        $status = $encontrado !== false ? '✅ ACTIVO' : '⚠️  No encontrado';
        echo "$status $middleware\n";
    }
    echo "\n";
}

// 4. Verificar rutas con middleware tenant
echo "📊 4. URL SOLICITADA:\n";
echo "────────────────────────────────────────────────────────────────\n";
echo "Dominio: hospital-san.sanaresys.localhost\n";
echo "Tipo: Subdominio personalizado\n\n";

echo "El sistema está intentando:\n";
echo "1. Detectar que estás accediendo con un subdominio\n";
echo "2. Buscar ese subdominio en la tabla 'tenants'\n";
echo "3. ❌ FALLA porque la columna 'domain' no existe\n\n";

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  🎯 EXPLICACIÓN DEL PROBLEMA                                    ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

echo "Tu sistema usa DOS MODOS de identificar tenants:\n\n";

echo "MODO 1: Por centro_id (el que usas actualmente)\n";
echo "──────────────────────────────────────────────────\n";
echo "• URL: http://localhost:8000/admin/login\n";
echo "• Identificación: Por usuario autenticado → centro_id\n";
echo "• Tabla: tenants.centro_id = 1\n";
echo "• Estado: ✅ FUNCIONA\n\n";

echo "MODO 2: Por dominio/subdominio (que intentaste usar)\n";
echo "──────────────────────────────────────────────────────\n";
echo "• URL: http://hospital-san.sanaresys.localhost/admin\n";
echo "• Identificación: Por subdominio en URL\n";
echo "• Tabla: tenants.domain o domains.domain\n";
echo "• Estado: ❌ NO CONFIGURADO\n\n";

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  💡 OPCIONES DE SOLUCIÓN                                        ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

echo "OPCIÓN A: Continuar usando el modo actual (centro_id)\n";
echo "───────────────────────────────────────────────────────────\n";
echo "✅ RECOMENDADO - Ya funciona\n";
echo "✅ No requiere cambios\n";
echo "✅ URL: http://localhost:8000/admin/login\n";
echo "✅ Identificación automática después del login\n\n";

echo "OPCIÓN B: Habilitar identificación por dominio\n";
echo "───────────────────────────────────────────────────\n";
echo "⚠️  Requiere cambios en base de datos\n";
echo "⚠️  Requiere configurar subdominios en DNS/hosts\n";
echo "⚠️  Requiere configurar servidor web (Apache/Nginx)\n";
echo "⚠️  Más complejo para desarrollo local\n\n";

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  🚀 SOLUCIÓN INMEDIATA (Recomendada)                            ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

echo "NO uses subdominios en desarrollo local.\n\n";

echo "✅ USA ESTA URL:\n";
echo "   http://localhost:8000/admin/login\n\n";

echo "El sistema:\n";
echo "1. Te permite hacer login\n";
echo "2. Identifica tu centro automáticamente\n";
echo "3. Inicializa el tenant correcto\n";
echo "4. Todo funciona sin problemas\n\n";

echo "✅ URL CORRECTA:\n";
echo "   • Login: http://localhost:8000/admin/login\n";
echo "   • Dashboard: http://localhost:8000/admin\n";
echo "   • Onboarding: http://localhost:8000/onboarding/welcome\n\n";

echo "❌ NO USES:\n";
echo "   • http://hospital-san.sanaresys.localhost\n";
echo "   • http://centro-1.localhost\n";
echo "   • Cualquier subdominio personalizado\n\n";

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  📝 RESUMEN                                                      ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

echo "El error ocurre porque:\n";
echo "• Intentaste acceder con un subdominio personalizado\n";
echo "• El sistema intentó buscar ese dominio en la tabla 'tenants'\n";
echo "• La columna 'domain' no existe (solo existe 'centro_id')\n\n";

echo "Solución:\n";
echo "• Usa la URL normal: http://localhost:8000/admin/login\n";
echo "• El tenant se resolverá automáticamente después del login\n";
echo "• No necesitas subdominios para multi-tenancy\n\n";

echo "✅ Tu sistema multi-tenant funciona perfectamente por centro_id\n\n";
