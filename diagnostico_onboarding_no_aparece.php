<?php
/**
 * Diagnóstico: ¿Por qué no aparece el wizard de onboarding?
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
echo "║  DIAGNÓSTICO: Wizard de Onboarding no aparece                   ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

echo "🔍 Ingresa el email del usuario con el que te registraste:\n";
$email = trim(fgets(STDIN));

if (empty($email)) {
    echo "❌ Email vacío. Saliendo...\n";
    exit(1);
}

echo "\n📊 BUSCANDO USUARIO...\n";
echo "────────────────────────────────────────────────────────────────\n";

$usuario = User::on('mysql')->where('email', $email)->first();

if (!$usuario) {
    echo "❌ Usuario no encontrado: $email\n\n";
    echo "Usuarios disponibles en el sistema:\n";
    $usuarios = User::on('mysql')->select('id', 'name', 'email', 'centro_id')->get();
    foreach ($usuarios as $u) {
        echo "  • {$u->email} (centro_id: {$u->centro_id})\n";
    }
    exit(1);
}

echo "✅ Usuario encontrado:\n";
echo "   • ID: {$usuario->id}\n";
echo "   • Nombre: {$usuario->name}\n";
echo "   • Email: {$usuario->email}\n";
echo "   • Centro ID: " . ($usuario->centro_id ?? 'NULL ❌') . "\n\n";

if (!$usuario->centro_id) {
    echo "❌ PROBLEMA CRÍTICO: El usuario NO tiene centro_id asignado\n";
    echo "   Por eso no puede acceder al onboarding\n\n";
    exit(1);
}

// Buscar centro
echo "🏥 VERIFICANDO CENTRO MÉDICO...\n";
echo "────────────────────────────────────────────────────────────────\n";

$centro = Centros_Medico::on('mysql')->find($usuario->centro_id);

if (!$centro) {
    echo "❌ Centro no encontrado: ID {$usuario->centro_id}\n\n";
    exit(1);
}

echo "✅ Centro encontrado:\n";
echo "   • ID: {$centro->id}\n";
echo "   • Nombre: {$centro->nombre}\n";
echo "   • RTN: {$centro->rtn}\n\n";

// Estado del onboarding
echo "📋 ESTADO DEL ONBOARDING:\n";
echo "────────────────────────────────────────────────────────────────\n";

$step = $centro->onboarding_current_step ?? null;
$completed = $centro->onboarding_completed_at ?? null;
$skippedCai = $centro->onboarding_skipped_cai ?? null;

echo "   • onboarding_current_step: ";
if (is_null($step)) {
    echo "NULL ❌ (debería ser 0)\n";
} else {
    echo "$step " . ($step == 0 ? "✅" : "⚠️") . "\n";
}

echo "   • onboarding_completed_at: ";
if (is_null($completed)) {
    echo "NULL ✅ (pendiente)\n";
} else {
    echo "$completed ❌ (ya completado)\n";
}

echo "   • onboarding_skipped_cai: ";
echo ($skippedCai ? "true" : "false") . "\n\n";

// Análisis
echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  ANÁLISIS DEL PROBLEMA                                          ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

$problemas = [];

if (is_null($step)) {
    $problemas[] = "❌ onboarding_current_step es NULL (debería ser 0)";
}

if (!is_null($completed)) {
    $problemas[] = "❌ onboarding_completed_at ya está marcado como completado";
}

if ($step > 0) {
    $problemas[] = "⚠️  onboarding_current_step = $step (debería ser 0 para mostrar wizard)";
}

if (empty($problemas)) {
    echo "✅ NO HAY PROBLEMAS DETECTADOS en los datos\n\n";
    echo "El problema puede estar en:\n";
    echo "• LoginResponse no está registrado correctamente\n";
    echo "• Middleware RequireOnboarding no está activo\n";
    echo "• Rutas de onboarding no están registradas\n\n";
} else {
    echo "PROBLEMAS DETECTADOS:\n\n";
    foreach ($problemas as $problema) {
        echo "$problema\n";
    }
    echo "\n";
}

// Verificar tenant
echo "🔧 VERIFICANDO TENANT...\n";
echo "────────────────────────────────────────────────────────────────\n";

$tenant = Tenant::where('centro_id', $centro->id)->first();

if ($tenant) {
    echo "✅ Tenant existe: {$tenant->id}\n";
    
    // Verificar si la base de datos existe
    try {
        $dbExists = DB::connection('mysql')->select("SHOW DATABASES LIKE '{$tenant->id}'");
        if (!empty($dbExists)) {
            echo "✅ Base de datos existe: {$tenant->id}\n";
        } else {
            echo "⚠️  Base de datos NO existe: {$tenant->id}\n";
        }
    } catch (\Exception $e) {
        echo "⚠️  Error verificando BD: {$e->getMessage()}\n";
    }
} else {
    echo "⚠️  Tenant NO existe (se creará durante el onboarding)\n";
}

echo "\n";

// Soluciones
echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  💡 SOLUCIONES                                                  ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

if (!empty($problemas)) {
    echo "🔧 SOLUCIÓN 1: Resetear estado del onboarding\n";
    echo "────────────────────────────────────────────────────────────────\n";
    echo "Ejecuta este comando SQL:\n\n";
    echo "UPDATE centros_medicos SET\n";
    echo "  onboarding_current_step = 0,\n";
    echo "  onboarding_completed_at = NULL,\n";
    echo "  onboarding_skipped_cai = 0\n";
    echo "WHERE id = {$centro->id};\n\n";
    
    echo "¿Deseas que lo ejecute automáticamente? (S/N): ";
    $respuesta = trim(fgets(STDIN));
    
    if (strtoupper($respuesta) === 'S') {
        try {
            DB::connection('mysql')->table('centros_medicos')
                ->where('id', $centro->id)
                ->update([
                    'onboarding_current_step' => 0,
                    'onboarding_completed_at' => null,
                    'onboarding_skipped_cai' => false,
                ]);
            
            echo "✅ Estado de onboarding reseteado exitosamente\n\n";
            
            echo "Ahora:\n";
            echo "1. Cierra sesión completamente\n";
            echo "2. Vuelve a iniciar sesión\n";
            echo "3. Deberías ver el wizard de onboarding\n\n";
            
        } catch (\Exception $e) {
            echo "❌ Error al resetear: {$e->getMessage()}\n\n";
        }
    }
}

echo "🔧 SOLUCIÓN 2: Verificar LoginResponse\n";
echo "────────────────────────────────────────────────────────────────\n";
echo "Archivo: app/Http/Responses/LoginResponse.php\n";
echo "Debe estar registrado en: app/Providers/AppServiceProvider.php\n\n";

$loginResponseFile = __DIR__ . '/app/Http/Responses/LoginResponse.php';
echo "¿Existe LoginResponse? " . (file_exists($loginResponseFile) ? "✅ SÍ" : "❌ NO") . "\n";

$appServiceProviderFile = __DIR__ . '/app/Providers/AppServiceProvider.php';
if (file_exists($appServiceProviderFile)) {
    $content = file_get_contents($appServiceProviderFile);
    $tieneRegistro = strpos($content, 'LoginResponse::class') !== false;
    echo "¿Está registrado en AppServiceProvider? " . ($tieneRegistro ? "✅ SÍ" : "❌ NO") . "\n";
}

echo "\n";

echo "🔧 SOLUCIÓN 3: Verificar rutas de onboarding\n";
echo "────────────────────────────────────────────────────────────────\n";
echo "Archivo: routes/web.php\n";
echo "Debe tener rutas /onboarding/*\n\n";

try {
    $output = shell_exec('cd ' . escapeshellarg(__DIR__) . ' && php artisan route:list --path=onboarding 2>&1');
    if ($output) {
        echo "Rutas de onboarding:\n";
        echo $output . "\n";
    } else {
        echo "⚠️  No se pudieron listar las rutas\n\n";
    }
} catch (\Exception $e) {
    echo "⚠️  Error al listar rutas: {$e->getMessage()}\n\n";
}

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  📝 RESUMEN                                                      ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

echo "Usuario: $email\n";
echo "Centro: {$centro->nombre} (ID: {$centro->id})\n";
echo "Estado onboarding: Step " . ($step ?? 'NULL') . "\n";
echo "Completado: " . ($completed ? "SÍ" : "NO") . "\n\n";

if (!empty($problemas)) {
    echo "⚠️  Se detectaron problemas que impiden ver el wizard\n";
    echo "   Sigue las soluciones propuestas arriba\n\n";
} else {
    echo "✅ Los datos parecen correctos\n";
    echo "   El problema puede estar en el código de redirección\n\n";
}
