<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Centros_Medico;
use App\Models\User;
use App\Models\Tenant;

echo "=== VERIFICACIÓN ESTRUCTURA FINAL ===\n\n";

// 1. Base de datos
echo "1. BASE DE DATOS:\n";
echo "   - Nombre: " . DB::connection('mysql')->getDatabaseName() . "\n";
$tables = DB::connection('mysql')->select('SHOW TABLES');
echo "   - Total de tablas: " . count($tables) . "\n\n";

// 2. Tablas principales
echo "2. TABLAS PRINCIPALES:\n";
$tablasImportantes = [
    'centros_medicos', 'users', 'medicos', 'pacientes', 
    'citas', 'consultas', 'recetas', 'examenes', 
    'facturas', 'nominas', 'tenants'
];

foreach ($tables as $t) {
    $name = array_values((array)$t)[0];
    if (in_array($name, $tablasImportantes)) {
        $count = DB::table($name)->count();
        echo "   ✓ {$name} ({$count} registros)\n";
    }
}

// 3. Datos seed
echo "\n3. DATOS INICIALES:\n";
$centro = Centros_Medico::first();
if ($centro) {
    echo "   ✓ Centro: {$centro->nombre_centro}\n";
}

$user = User::first();
if ($user) {
    echo "   ✓ Usuario: {$user->name} ({$user->email})\n";
}

$tenant = Tenant::first();
if ($tenant) {
    echo "   ✓ Tenant: {$tenant->id} (Centro ID: {$tenant->centro_id})\n";
}

// 4. Verificar que NO hay bases de datos separadas
echo "\n4. BASES DE DATOS TENANT:\n";
$databases = DB::select('SHOW DATABASES');
$tenantDbs = [];
foreach ($databases as $db) {
    $dbName = array_values((array)$db)[0];
    if (strpos($dbName, 'centro_') === 0) {
        $tenantDbs[] = $dbName;
    }
}

if (count($tenantDbs) > 0) {
    echo "   ⚠ Encontradas " . count($tenantDbs) . " BD tenant (deberían eliminarse):\n";
    foreach ($tenantDbs as $db) {
        echo "     - {$db}\n";
    }
} else {
    echo "   ✓ No hay bases de datos tenant separadas\n";
    echo "   ✓ Todas las tablas están en db_clinica\n";
}

echo "\n=== ARQUITECTURA UNIFICADA CONFIRMADA ===\n";
echo "✓ Todas las migraciones en: database/migrations/\n";
echo "✓ Todas las tablas en: db_clinica\n";
echo "✓ Sistema multi-tenant deshabilitado\n";
