<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Centros_Medico;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  ACTUALIZAR CLÍNICA MONCADA PARA ONBOARDING                     ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

// Buscar la clínica ClinicaMoncada
$centro = Centros_Medico::on('mysql')
    ->where('nombre_centro', 'like', '%Moncada%')
    ->orWhere('slug', 'like', '%moncada%')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$centro) {
    echo "❌ No se encontró la clínica Moncada\n\n";
    echo "Buscando por email del usuario: Moncada@example.com\n";
    
    $usuario = User::on('mysql')->where('email', 'Moncada@example.com')->first();
    
    if ($usuario && $usuario->centro_id) {
        $centro = Centros_Medico::on('mysql')->find($usuario->centro_id);
    }
}

if (!$centro) {
    echo "❌ No se pudo encontrar la clínica. Listando últimas clínicas:\n\n";
    
    $centros = Centros_Medico::on('mysql')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    foreach ($centros as $c) {
        echo "• ID: {$c->id} - {$c->nombre_centro} (creado: {$c->created_at})\n";
    }
    
    exit(1);
}

echo "✅ Clínica encontrada:\n";
echo "   • ID: {$centro->id}\n";
echo "   • Nombre: {$centro->nombre_centro}\n";
echo "   • Slug: {$centro->slug}\n";
echo "   • Creado: {$centro->created_at}\n\n";

echo "📊 ESTADO ACTUAL:\n";
echo "   • onboarding_current_step: " . ($centro->onboarding_current_step ?? 'NULL') . "\n";
echo "   • onboarding_skipped_cai: " . ($centro->onboarding_skipped_cai ?? 'NULL') . "\n";
echo "   • onboarding_completed_at: " . ($centro->onboarding_completed_at ?? 'NULL') . "\n\n";

echo "🔧 ACTUALIZANDO...\n";

try {
    DB::connection('mysql')->table('centros_medicos')
        ->where('id', $centro->id)
        ->update([
            'onboarding_current_step' => 0,
            'onboarding_skipped_cai' => false,
            'onboarding_completed_at' => null,
        ]);
    
    echo "✅ Centro actualizado correctamente\n\n";
    
    // Verificar
    $centroActualizado = Centros_Medico::on('mysql')->find($centro->id);
    
    echo "📊 ESTADO DESPUÉS DE LA ACTUALIZACIÓN:\n";
    echo "   • onboarding_current_step: {$centroActualizado->onboarding_current_step}\n";
    echo "   • onboarding_skipped_cai: " . ($centroActualizado->onboarding_skipped_cai ? 'true' : 'false') . "\n";
    echo "   • onboarding_completed_at: " . ($centroActualizado->onboarding_completed_at ?? 'NULL') . "\n\n";
    
    echo "╔══════════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ ACTUALIZACIÓN COMPLETADA                                    ║\n";
    echo "╚══════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "SIGUIENTE PASO:\n";
    echo "1. Cierra COMPLETAMENTE el navegador (todas las pestañas)\n";
    echo "2. Abre el navegador nuevamente\n";
    echo "3. Ve a: clinicamoncada.sanaresys.localhost:8000/admin/login\n";
    echo "4. Inicia sesión con: Moncada@example.com\n";
    echo "5. Deberías ser redirigido al wizard de onboarding\n\n";
    
} catch (\Exception $e) {
    echo "❌ Error al actualizar: {$e->getMessage()}\n\n";
    exit(1);
}
