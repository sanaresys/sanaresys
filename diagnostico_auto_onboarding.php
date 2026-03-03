<?php
/**
 * Diagnóstico automático: Últimos usuarios y estado de onboarding
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Centros_Medico;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  DIAGNÓSTICO AUTOMÁTICO: Últimos usuarios registrados           ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

// Obtener los últimos 5 usuarios
echo "📊 ÚLTIMOS USUARIOS REGISTRADOS:\n";
echo "────────────────────────────────────────────────────────────────\n";

$usuarios = User::on('mysql')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

foreach ($usuarios as $usuario) {
    $tiempo = $usuario->created_at->diffForHumans();
    echo "\n👤 Usuario #{$usuario->id}:\n";
    echo "   • Nombre: {$usuario->name}\n";
    echo "   • Email: {$usuario->email}\n";
    echo "   • Centro ID: " . ($usuario->centro_id ?? 'NULL ❌') . "\n";
    echo "   • Creado: $tiempo\n";
    
    if ($usuario->centro_id) {
        $centro = Centros_Medico::on('mysql')->find($usuario->centro_id);
        
        if ($centro) {
            echo "\n   🏥 Centro: {$centro->nombre}\n";
            
            // Estado onboarding
            $step = $centro->onboarding_current_step;
            $completed = $centro->onboarding_completed_at;
            
            echo "   📋 Onboarding:\n";
            echo "      • Step actual: ";
            
            if (is_null($step)) {
                echo "NULL ❌ (columna no existe o no configurada)\n";
            } else {
                echo "$step ";
                if ($step == 0) {
                    echo "✅ (debe mostrar wizard)\n";
                } elseif ($step > 0 && $step < 4) {
                    echo "⚠️  (en progreso)\n";
                } else {
                    echo "✓ (completado o valor inválido)\n";
                }
            }
            
            echo "      • Completado: ";
            if (is_null($completed)) {
                echo "NO ✅ (pendiente)\n";
            } else {
                echo "SÍ el $completed ❌\n";
            }
            
            // Verificar tenant
            $tenant = Tenant::where('centro_id', $centro->id)->first();
            echo "      • Tenant: ";
            if ($tenant) {
                echo "✅ Existe ({$tenant->id})\n";
            } else {
                echo "⏳ No creado (se crea en onboarding)\n";
            }
            
            // Determinar si debería ver el wizard
            echo "\n   🎯 ¿Debería ver el wizard? ";
            if (is_null($step) || $step == 0) {
                if (is_null($completed)) {
                    echo "SÍ ✅\n";
                } else {
                    echo "NO ❌ (ya completado)\n";
                }
            } else {
                echo "NO ❌ (step > 0 o completado)\n";
            }
        }
    }
    
    echo "\n" . str_repeat("─", 68) . "\n";
}

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICACIÓN DE COMPONENTES DE ONBOARDING                      ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

// 1. Verificar LoginResponse
echo "1️⃣  LoginResponse personalizado:\n";
$loginResponseFile = __DIR__ . '/app/Http/Responses/LoginResponse.php';
$existe = file_exists($loginResponseFile);
echo "   " . ($existe ? "✅" : "❌") . " Archivo existe\n";

if ($existe) {
    $content = file_get_contents($loginResponseFile);
    $tieneOnboarding = strpos($content, 'onboarding') !== false;
    echo "   " . ($tieneOnboarding ? "✅" : "❌") . " Contiene lógica de onboarding\n";
}

// 2. Verificar registro en AppServiceProvider
echo "\n2️⃣  Registro en AppServiceProvider:\n";
$providerFile = __DIR__ . '/app/Providers/AppServiceProvider.php';
if (file_exists($providerFile)) {
    $content = file_get_contents($providerFile);
    $registrado = strpos($content, 'LoginResponse::class') !== false;
    echo "   " . ($registrado ? "✅" : "❌") . " LoginResponse registrado\n";
} else {
    echo "   ❌ AppServiceProvider no encontrado\n";
}

// 3. Verificar middleware
echo "\n3️⃣  Middleware RequireOnboarding:\n";
$middlewareFile = __DIR__ . '/app/Http/Middleware/RequireOnboarding.php';
$existe = file_exists($middlewareFile);
echo "   " . ($existe ? "✅" : "❌") . " Archivo existe\n";

// 4. Verificar rutas
echo "\n4️⃣  Rutas de onboarding:\n";
$routesFile = __DIR__ . '/routes/web.php';
if (file_exists($routesFile)) {
    $content = file_get_contents($routesFile);
    $tieneRutas = strpos($content, 'onboarding') !== false;
    echo "   " . ($tieneRutas ? "✅" : "❌") . " Rutas definidas en web.php\n";
}

// 5. Verificar columnas en base de datos
echo "\n5️⃣  Columnas de onboarding en centros_medicos:\n";
try {
    $columns = DB::connection('mysql')
        ->select("SHOW COLUMNS FROM centros_medicos WHERE Field LIKE 'onboarding%'");
    
    $esperadas = [
        'onboarding_current_step',
        'onboarding_completed_at',
        'onboarding_skipped_cai',
    ];
    
    $encontradas = array_column($columns, 'Field');
    
    foreach ($esperadas as $columna) {
        $existe = in_array($columna, $encontradas);
        echo "   " . ($existe ? "✅" : "❌") . " $columna\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error consultando columnas: {$e->getMessage()}\n";
}

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  POSIBLES CAUSAS DEL PROBLEMA                                   ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

$causas = [];

// Verificar si hay usuarios sin onboarding configurado
$usuariosSinOnboarding = 0;
foreach ($usuarios as $usuario) {
    if ($usuario->centro_id) {
        $centro = Centros_Medico::on('mysql')->find($usuario->centro_id);
        if ($centro && is_null($centro->onboarding_current_step)) {
            $usuariosSinOnboarding++;
        }
    }
}

if ($usuariosSinOnboarding > 0) {
    $causas[] = "❌ Hay usuarios con onboarding_current_step = NULL";
}

if (!file_exists($loginResponseFile)) {
    $causas[] = "❌ LoginResponse no existe";
}

if (file_exists($providerFile)) {
    $content = file_get_contents($providerFile);
    if (strpos($content, 'LoginResponse::class') === false) {
        $causas[] = "❌ LoginResponse no está registrado en AppServiceProvider";
    }
}

if (empty($causas)) {
    echo "✅ NO se detectaron problemas obvios\n\n";
    echo "El problema puede ser:\n";
    echo "• Caché de Laravel activa (ejecuta: php artisan config:clear)\n";
    echo "• Sesión antigua activa (cierra sesión y vuelve a entrar)\n";
    echo "• El usuario ya completó el onboarding anteriormente\n\n";
} else {
    echo "PROBLEMAS DETECTADOS:\n\n";
    foreach ($causas as $causa) {
        echo "$causa\n";
    }
    echo "\n";
}

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  SOLUCIÓN RÁPIDA                                                 ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

// Buscar el usuario más reciente con problemas
$usuarioProblematico = null;
foreach ($usuarios as $usuario) {
    if ($usuario->centro_id) {
        $centro = Centros_Medico::on('mysql')->find($usuario->centro_id);
        if ($centro && (is_null($centro->onboarding_current_step) || $centro->onboarding_current_step > 0)) {
            if (is_null($centro->onboarding_completed_at)) {
                $usuarioProblematico = $usuario;
                break;
            }
        }
    }
}

if ($usuarioProblematico) {
    $centro = Centros_Medico::on('mysql')->find($usuarioProblematico->centro_id);
    
    echo "Se detectó que el usuario más reciente tiene problemas:\n";
    echo "• Email: {$usuarioProblematico->email}\n";
    echo "• Centro: {$centro->nombre}\n\n";
    
    echo "Para resetear su onboarding, ejecuta:\n\n";
    echo "UPDATE centros_medicos SET\n";
    echo "  onboarding_current_step = 0,\n";
    echo "  onboarding_completed_at = NULL,\n";
    echo "  onboarding_skipped_cai = 0\n";
    echo "WHERE id = {$centro->id};\n\n";
    
    echo "Luego:\n";
    echo "1. php artisan config:clear\n";
    echo "2. Cierra sesión completamente\n";
    echo "3. Vuelve a iniciar sesión\n\n";
}

echo "✅ Diagnóstico completado\n\n";
