<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║        AGREGAR CAMPOS DE ONBOARDING A centros_medicos           ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

try {
    // Verificar si las columnas ya existen
    $hasCurrentStep = Schema::hasColumn('centros_medicos', 'onboarding_current_step');
    $hasSkippedCai = Schema::hasColumn('centros_medicos', 'onboarding_skipped_cai');
    $hasCompletedAt = Schema::hasColumn('centros_medicos', 'onboarding_completed_at');

    echo "📊 Estado actual de las columnas:\n";
    echo "   onboarding_current_step: " . ($hasCurrentStep ? '✅ Existe' : '❌ No existe') . "\n";
    echo "   onboarding_skipped_cai: " . ($hasSkippedCai ? '✅ Existe' : '❌ No existe') . "\n";
    echo "   onboarding_completed_at: " . ($hasCompletedAt ? '✅ Existe' : '❌ No existe') . "\n\n";

    $needsUpdate = !$hasCurrentStep || !$hasSkippedCai || !$hasCompletedAt;

    if (!$needsUpdate) {
        echo "✅ Todas las columnas ya existen. No es necesario actualizar.\n\n";
        exit(0);
    }

    echo "🔧 Agregando columnas faltantes...\n\n";

    DB::statement("SET FOREIGN_KEY_CHECKS=0;");

    if (!$hasCurrentStep) {
        echo "   ➤ Agregando onboarding_current_step... ";
        DB::statement("ALTER TABLE centros_medicos ADD COLUMN onboarding_current_step INT DEFAULT 0 AFTER onboarding_completed_at");
        echo "✅\n";
    }

    if (!$hasSkippedCai) {
        echo "   ➤ Agregando onboarding_skipped_cai... ";
        DB::statement("ALTER TABLE centros_medicos ADD COLUMN onboarding_skipped_cai BOOLEAN DEFAULT FALSE AFTER onboarding_current_step");
        echo "✅\n";
    }

    if (!$hasCompletedAt) {
        echo "   ➤ Agregando onboarding_completed_at... ";
        DB::statement("ALTER TABLE centros_medicos ADD COLUMN onboarding_completed_at TIMESTAMP NULL AFTER tenancy_mode");
        echo "✅\n";
    }

    DB::statement("SET FOREIGN_KEY_CHECKS=1;");

    echo "\n✅ Columnas agregadas exitosamente.\n\n";

    // Verificar nuevamente
    echo "📊 Verificación final:\n";
    $columns = DB::select("SHOW COLUMNS FROM centros_medicos WHERE Field LIKE 'onboarding%'");
    foreach ($columns as $col) {
        echo "   ✓ {$col->Field} ({$col->Type}) - Default: " . ($col->Default ?? 'NULL') . "\n";
    }

    echo "\n🎉 ¡Base de datos actualizada correctamente!\n";
    echo "   Puedes continuar con el onboarding.\n\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    exit(1);
}
