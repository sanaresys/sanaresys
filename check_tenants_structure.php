<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ESTRUCTURA DE TABLA TENANTS ===\n\n";

$columns = DB::select('SHOW COLUMNS FROM tenants');

foreach($columns as $col) {
    echo "Campo: {$col->Field}\n";
    echo "  Tipo: {$col->Type}\n";
    echo "  Null: {$col->Null}\n";
    echo "  Key: {$col->Key}\n";
    echo "  Default: " . ($col->Default ?? 'NULL') . "\n";
    echo "\n";
}

echo "=== REGISTROS ACTUALES ===\n";
$tenants = DB::table('tenants')->get();
echo "Total: " . $tenants->count() . "\n\n";

foreach($tenants as $tenant) {
    echo "ID: {$tenant->id}\n";
    if (isset($tenant->centro_id)) echo "Centro ID: {$tenant->centro_id}\n";
    if (isset($tenant->name)) echo "Name: {$tenant->name}\n";
    if (isset($tenant->domain)) echo "Domain: {$tenant->domain}\n";
    if (isset($tenant->database)) echo "Database: {$tenant->database}\n";
    echo "---\n";
}
