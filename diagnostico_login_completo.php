<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Centros_Medico;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  DIAGNÓSTICO COMPLETO - ÚLTIMAS 3 CLÍNICAS Y USUARIOS          ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

$clinicas = Centros_Medico::on('mysql')
    ->orderBy('created_at', 'desc')
    ->limit(3)
    ->get();

foreach ($clinicas as $i => $centro) {
    echo ($i === 0 ? "🆕 " : "   ") . "═══════════════════════════════════════════════════════════\n";
    echo "CLÍNICA: {$centro->nombre_centro} (ID: {$centro->id})\n";
    echo "───────────────────────────────────────────────────────────────\n";
    echo "• Slug: {$centro->slug}\n";
    echo "• Creado: {$centro->created_at}\n";
    echo "• onboarding_current_step: " . ($centro->onboarding_current_step ?? 'NULL') . "\n";
    echo "• onboarding_skipped_cai: " . ($centro->onboarding_skipped_cai ?? 'NULL') . "\n";
    echo "• onboarding_completed_at: " . ($centro->onboarding_completed_at ?? 'NULL') . "\n\n";
    
    // Buscar usuarios de este centro
    $usuarios = User::on('mysql')
        ->where('centro_id', $centro->id)
        ->get();
    
    if ($usuarios->count() > 0) {
        echo "👥 USUARIOS ({$usuarios->count()}):\n";
        foreach ($usuarios as $user) {
            echo "   • {$user->email}\n";
            echo "     - name: {$user->name}\n";
            echo "     - centro_id: {$user->centro_id}\n";
            
            // Verificar roles
            $roles = DB::connection('mysql')
                ->table('model_has_roles')
                ->where('model_id', $user->id)
                ->where('model_type', 'App\\Models\\User')
                ->pluck('role_id')
                ->toArray();
            
            if (!empty($roles)) {
                $roleNames = DB::connection('mysql')
                    ->table('roles')
                    ->whereIn('id', $roles)
                    ->pluck('name')
                    ->toArray();
                echo "     - roles: " . implode(', ', $roleNames) . "\n";
            } else {
                echo "     - roles: (ninguno)\n";
            }
        }
    } else {
        echo "⚠️ NO HAY USUARIOS PARA ESTE CENTRO\n";
    }
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "PRUEBA DEL LOGINRESPONSE:\n";
echo "───────────────────────────────────────────────────────────────\n\n";

$ultimaClinica = $clinicas->first();
$usuario = User::on('mysql')->where('centro_id', $ultimaClinica->id)->first();

if ($usuario) {
    echo "Simulando login de: {$usuario->email}\n";
    echo "Centro ID del usuario: {$usuario->centro_id}\n\n";
    
    echo "Consultando centro desde LoginResponse...\n";
    $centroDesdeLogin = Centros_Medico::on('mysql')
        ->select(['id', 'onboarding_completed_at'])
        ->find($usuario->centro_id);
    
    if ($centroDesdeLogin) {
        echo "✅ Centro encontrado: ID {$centroDesdeLogin->id}\n";
        echo "   onboarding_completed_at: " . ($centroDesdeLogin->onboarding_completed_at ?? 'NULL') . "\n\n";
        
        if (!$centroDesdeLogin->onboarding_completed_at) {
            echo "✅ DEBERÍA REDIRIGIR A: /onboarding/welcome\n";
        } else {
            echo "❌ IRÍA AL DASHBOARD (onboarding ya completado)\n";
        }
    } else {
        echo "❌ Centro NO encontrado\n";
    }
} else {
    echo "❌ No hay usuarios para la última clínica\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "VERIFICAR RUTA DE ONBOARDING:\n";
echo "───────────────────────────────────────────────────────────────\n";

// Verificar que la ruta existe
try {
    $route = app('router')->getRoutes()->getByName('onboarding.welcome');
    if ($route) {
        echo "✅ Ruta 'onboarding.welcome' existe\n";
        echo "   URI: {$route->uri()}\n";
        echo "   Métodos: " . implode(', ', $route->methods()) . "\n";
    } else {
        echo "❌ Ruta 'onboarding.welcome' NO encontrada\n";
    }
} catch (\Exception $e) {
    echo "❌ Error al verificar ruta: {$e->getMessage()}\n";
}
echo "\n";
