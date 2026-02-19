<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

$tenant = Tenant::first();
tenancy()->initialize($tenant);

// Verificar nacionalidades
$nacionalidadesCount = DB::connection('tenant')->table('nacionalidades')->count();
echo "Nacionalidades en tenant: $nacionalidadesCount" . PHP_EOL;

if ($nacionalidadesCount == 0) {
    echo "Insertando nacionalidades..." . PHP_EOL;
    DB::connection('tenant')->table('nacionalidades')->insert([
        'id' => 1,
        'pais' => 'Honduras',
        'gentilicio' => 'Hondureño/a',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✓ Nacionalidad insertada" . PHP_EOL;
}

tenancy()->end();
