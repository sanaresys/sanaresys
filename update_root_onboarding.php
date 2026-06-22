<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Actualizando usuario root para onboarding...\n";

$affected = DB::connection('mysql')
    ->table('centros_medicos')
    ->where('id', 1)
    ->update([
        'onboarding_current_step' => 0,
        'onboarding_skipped_cai' => 0,
        'onboarding_completed_at' => null,
    ]);

echo "✅ {$affected} registro(s) actualizados\n";

// Verificar
$centro = DB::connection('mysql')
    ->table('centros_medicos')
    ->where('id', 1)
    ->first();

echo "\nEstado actual del centro:\n";
echo "• onboarding_current_step: {$centro->onboarding_current_step}\n";
echo "• onboarding_skipped_cai: {$centro->onboarding_skipped_cai}\n";
echo "• onboarding_completed_at: " . ($centro->onboarding_completed_at ?? 'NULL') . "\n";
echo "\n✅ El usuario root debería ver el wizard ahora\n";
