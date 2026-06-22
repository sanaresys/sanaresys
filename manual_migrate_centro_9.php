<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;

echo "=== EJECUTANDO MIGRACIONES MANUALMENTE PARA centro_9 ===\n\n";

$tenant = Tenant::find('centro_9');

if (!$tenant) {
    echo "✗ Tenant centro_9 no encontrado\n";
    exit(1);
}

echo "✓ Tenant encontrado: {$tenant->id}\n";
echo "✓ Database: {$tenant->database()->getName()}\n\n";

// Ver qué devuelve connection()
echo "Config de conexión devuelta por tenant->database()->connection():\n";
$connConfig = $tenant->database()->connection();
echo "  - Database: " . ($connConfig['database'] ?? 'NULL') . "\n";
echo "  - Host: {$connConfig['host']}\n\n";

// Verificar qué bootstrappers están configurados
echo "Bootstrappers configurados:\n";
$bootstrappers = config('tenancy.bootstrappers', []);
if (empty($bootstrappers)) {
    echo "  ✗ NO HAY BOOTSTRAPPERS CONFIGURADOS\n\n";
} else {
    foreach ($bootstrappers as $bootstrapper) {
        echo "  - " . class_basename($bootstrapper) . "\n";
    }
    echo "\n";
}

// Llamar directamente al database manager (sabemos que esto funciona)
echo "Llamando a DatabaseManager->connectToTenant()...\n";
$databaseManager = app(\Stancl\Tenancy\Database\DatabaseManager::class);
$databaseManager->connectToTenant($tenant);

echo "\nConexión tenant después de connectToTenant:\n";
$tenantConfig = config('database.connections.tenant');
echo "  - Host: {$tenantConfig['host']}\n";
echo "  - Database: " . ($tenantConfig['database'] ?? 'NULL') . "\n";
echo "  - Username: {$tenantConfig['username']}\n\n";

// Probar si funciona
try {
    $result = DB::connection('tenant')->select('SELECT DATABASE() as db');
    echo "✓ Base de datos activa: " . ($result[0]->db ?? 'NULL') . "\n\n";
} catch (\Exception $e) {
    echo "✗ Error de conexión: " . $e->getMessage() . "\n\n";
}

echo "Ejecutando migraciones...\n\n";

// Ejecutar migraciones
$exitCode = Artisan::call('migrate', [
    '--database' => 'tenant',
    '--path' => 'database/migrations/tenant',
    '--force' => true,
]);

echo Artisan::output();

if ($exitCode === 0) {
    echo "\n✓ Migraciones ejecutadas exitosamente\n";
} else {
    echo "\n✗ Error ejecutando migraciones (código: {$exitCode})\n";
}
