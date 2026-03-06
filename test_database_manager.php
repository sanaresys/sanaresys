<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

echo "===TEST DIRECTO DE DATABASE MANAGER ===\n\n";

$tenant = Tenant::find('centro_9');

if (!$tenant) {
    echo "✗ Tenant centro_9 no encontrado\n";
    exit(1);
}

echo "✓ Tenant encontrado: {$tenant->id}\n";
echo "✓ Database name: {$tenant->database()->getName()}\n\n";

// Llamar directamente al database manager
$databaseManager = app(\Stancl\Tenancy\Database\DatabaseManager::class);

echo "Llamando a connectToTenant...\n";
$databaseManager->connectToTenant($tenant);

echo "\nConfig después de connectToTenant:\n";
$tenantConfig = config('database.connections.tenant');
echo "  - Host: {$tenantConfig['host']}\n";
echo "  - Database: " . ($tenantConfig['database'] ?? 'NULL') . "\n";
echo "  - Username: {$tenantConfig['username']}\n\n";

// Intentar una consulta simple
try {
    echo "Probando consulta en tenant connection...\n";
    $result = DB::connection('tenant')->select('SELECT DATABASE() as db');
    echo "  - Base de datos activa: " . ($result[0]->db ?? 'NULL') . "\n";
} catch (\Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}
