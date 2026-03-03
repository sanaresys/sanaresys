<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Centros_Medico;
use Illuminate\Support\Facades\DB;

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICAR ÚLTIMA CLÍNICA REGISTRADA                            ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

// Obtener las últimas 3 clínicas
$clinicas = Centros_Medico::on('mysql')
    ->orderBy('created_at', 'desc')
    ->limit(3)
    ->get();

foreach ($clinicas as $i => $centro) {
    echo ($i === 0 ? "🆕 " : "   ") . "CLÍNICA #" . ($i + 1) . " (ID: {$centro->id}):\n";
    echo "   ├─ Nombre: {$centro->nombre_centro}\n";
    echo "   ├─ Slug: {$centro->slug}\n";
    echo "   ├─ Creado: {$centro->created_at}\n";
    echo "   ├─ onboarding_current_step: " . ($centro->onboarding_current_step ?? '⚠️ NULL') . "\n";
    echo "   ├─ onboarding_skipped_cai: " . ($centro->onboarding_skipped_cai ?? '⚠️ NULL') . "\n";
    echo "   └─ onboarding_completed_at: " . ($centro->onboarding_completed_at ?? 'NULL') . "\n\n";
}

$ultimaClinica = $clinicas->first();

if ($ultimaClinica) {
    echo "═══════════════════════════════════════════════════════════════════\n";
    echo "DIAGNÓSTICO DE LA ÚLTIMA CLÍNICA:\n\n";
    
    $problemas = [];
    
    if (is_null($ultimaClinica->onboarding_current_step)) {
        $problemas[] = "❌ onboarding_current_step es NULL (debería ser 0)";
    } else if ($ultimaClinica->onboarding_current_step == 0) {
        echo "✅ onboarding_current_step = 0 (correcto)\n";
    }
    
    if (is_null($ultimaClinica->onboarding_skipped_cai)) {
        $problemas[] = "❌ onboarding_skipped_cai es NULL (debería ser 0 o false)";
    } else {
        echo "✅ onboarding_skipped_cai está definido\n";
    }
    
    if (!is_null($ultimaClinica->onboarding_completed_at)) {
        $problemas[] = "❌ onboarding_completed_at tiene valor (debería ser NULL)";
    } else {
        echo "✅ onboarding_completed_at = NULL (correcto)\n";
    }
    
    if (count($problemas) > 0) {
        echo "\n⚠️ PROBLEMAS ENCONTRADOS:\n";
        foreach ($problemas as $problema) {
            echo "   {$problema}\n";
        }
        echo "\n";
    } else {
        echo "\n✅ No hay problemas detectados con los campos de onboarding\n\n";
    }
}

echo "═══════════════════════════════════════════════════════════════════\n";
