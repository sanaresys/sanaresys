<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Centros_Medico;
use Illuminate\Support\Facades\DB;

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  ACTUALIZAR CLINICA OBED PARA ONBOARDING                        ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

$centro = Centros_Medico::on('mysql')
    ->where('nombre_centro', 'ClinicaObed')
    ->orWhere('slug', 'clinicaobed')
    ->first();

if (!$centro) {
    echo "❌ No se encontró la clínica ClinicaObed\n";
    exit(1);
}

echo "✅ Clínica encontrada:\n";
echo "   • ID: {$centro->id}\n";
echo "   • Nombre: {$centro->nombre_centro}\n";
echo "   • Slug: {$centro->slug}\n\n";

echo "📊 ANTES:\n";
echo "   • onboarding_current_step: {$centro->onboarding_current_step}\n";
echo "   • onboarding_completed_at: {$centro->onboarding_completed_at}\n\n";

DB::connection('mysql')->table('centros_medicos')
    ->where('id', $centro->id)
    ->update([
        'onboarding_current_step' => 0,
        'onboarding_skipped_cai' => false,
        'onboarding_completed_at' => null,
    ]);

$centroActualizado = Centros_Medico::on('mysql')->find($centro->id);

echo "📊 DESPUÉS:\n";
echo "   • onboarding_current_step: {$centroActualizado->onboarding_current_step}\n";
echo "   • onboarding_completed_at: " . ($centroActualizado->onboarding_completed_at ?? 'NULL') . "\n\n";

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  ✅ ACTUALIZACIÓN COMPLETADA                                    ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

echo "SIGUIENTE PASO:\n";
echo "1. Cierra el navegador completamente\n";
echo "2. Ve a: clinicaobed.sanaresys.localhost:8000/admin/login\n";
echo "3. Inicia sesión con el email que registraste\n";
echo "4. Deberías ver el wizard de onboarding\n\n";
