<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;
use Stancl\Tenancy\Database\DatabaseManager;

echo "=== TEST DE DATABASETENANCYBOOTSTRAPPER ===\n\n";

$tenant = Tenant::find('centro_1');

echo "1. Entorno de la app: " . app()->environment() . "\n\n";

echo "2. Verificando la BD del tenant:\n";
echo "   - getName(): " . $tenant->database()->getName() . "\n";
echo "   -  BD existe: " . ($tenant->database()->manager()->databaseExists($tenant->database()->getName()) ? 'Sí' : 'No') . "\n\n";

echo "3. Llamando manualmente al bootstrapper...\n";
try {
    $databaseManager = app(DatabaseManager::class);
    $bootstrapper = new DatabaseTenancyBootstrapper($databaseManager);
    
    echo "   ✓ Bootstrapper creado\n";
    
    $bootstrapper->bootstrap($tenant);
    echo "   ✓ Bootstrap ejecutado\n\n";
    
    echo "4. Verificando conexiones después del bootstrap:\n";
    echo "   - Default: " . \Illuminate\Support\Facades\DB::getDefaultConnection() . "\n";
    echo "   - DB (mysql): " . \Illuminate\Support\Facades\Config::get('database.connections.mysql.database') . "\n";
    echo "   - DB (tenant): " . \Illuminate\Support\Facades\Config::get('database.connections.tenant.database') . "\n\n";
    
    echo "5. SELECT DATABASE():\n";
    $db = \Illuminate\Support\Facades\DB::connection('tenant')->select('SELECT DATABASE() as db')[0]->db;
    echo "   - Tenant connection: {$db}\n";
    
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== FIN ===\n";
