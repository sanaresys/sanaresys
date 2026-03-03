<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICAR DÓNDE ESTÁN LOS USUARIOS                             ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

// Verificar usuarios en base de datos central
echo "═══  BASE DE DATOS CENTRAL (mysql) ═══\n\n";
$usuariosCentral = User::on('mysql')->get();

if ($usuariosCentral->count() > 0) {
    echo "✅ {$usuariosCentral->count()} usuarios encontrados:\n\n";
    foreach ($usuariosCentral as $user) {
        echo "• {$user->email}\n";
        echo "  - ID: {$user->id}\n";
        echo "  - Name: {$user->name}\n";
        echo "  - centro_id: " . ($user->centro_id ?? 'NULL') . "\n\n";
    }
} else {
    echo "⚠️ NO hay usuarios en la base de datos central\n\n";
}

echo "═══════════════════════════════════════════════════════════════════\n\n";

// Verificar usuarios en bases de datos de tenants
echo "═══  BASES DE DATOS DE TENANTS ═══\n\n";

$databases = DB::select('SHOW DATABASES');
$tenantDatabases = array_filter($databases, function($db) {
    return str_starts_with($db->Database, 'centro_') || 
           str_contains($db->Database, 'clinica');
});

if (count($tenantDatabases) > 0) {
    foreach ($tenantDatabases as $db) {
        $dbName = $db->Database;
        echo "📁 DATABASE: {$dbName}\n";
        
        try {
            // Verificar si existe la tabla users
            $tableExists = DB::select("SELECT COUNT(*) as count FROM information_schema.tables 
                                      WHERE table_schema = ? AND table_name = 'users'", [$dbName]);
            
            if ($tableExists[0]->count > 0) {
                $usuarios = DB::connection('mysql')->select("SELECT id, name, email FROM `{$dbName}`.users LIMIT 10");
                
                if (count($usuarios) > 0) {
                    echo "   ✅ {" . count($usuarios) . "} usuarios:\n";
                    foreach ($usuarios as $user) {
                        echo "      • {$user->email} (ID: {$user->id})\n";
                    }
                } else {
                    echo "   ⚠️ Tabla 'users' existe pero está vacía\n";
                }
            } else {
                echo "   ❌ No tiene tabla 'users'\n";
            }
        } catch (\Exception $e) {
            echo "   ❌ Error: {$e->getMessage()}\n";
        }
        
        echo "\n";
    }
} else {
    echo "⚠️ NO se encontraron bases de datos de tenants\n\n";
}
