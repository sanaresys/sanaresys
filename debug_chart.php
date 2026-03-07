<?php
// Debug script to check chart data
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(\Illuminate\Http\Request::capture());

// List tenants
echo "=== TENANTS ===" . PHP_EOL;
$tenants = \App\Models\Tenant::all();
foreach ($tenants as $t) {
    echo "ID: {$t->id}" . PHP_EOL;
}

if ($tenants->isEmpty()) {
    echo "No tenants found!" . PHP_EOL;
    exit;
}

// Pick first tenant and initialize
$tenant = $tenants->count() > 1 ? $tenants->skip(1)->first() : $tenants->first();
echo PHP_EOL . "=== Initializing tenant: {$tenant->id} ===" . PHP_EOL;
tenancy()->initialize($tenant);
echo "Tenancy initialized: " . (tenancy()->initialized ? 'YES' : 'NO') . PHP_EOL;
echo "Current DB: " . \DB::connection()->getDatabaseName() . PHP_EOL;

// Check citas table
echo PHP_EOL . "=== CITAS TABLE ===" . PHP_EOL;
$totalCitas = \App\Models\Citas::count();
echo "Total citas: {$totalCitas}" . PHP_EOL;

$citasHoy = \App\Models\Citas::whereDate('fecha', today())->count();
echo "Citas hoy (whereDate fecha today): {$citasHoy}" . PHP_EOL;

// Show today's date
echo "Today: " . today()->toDateString() . PHP_EOL;

// Show some sample citas
echo PHP_EOL . "=== SAMPLE CITAS (last 5) ===" . PHP_EOL;
$citas = \App\Models\Citas::orderBy('id', 'desc')->take(5)->get();
foreach ($citas as $c) {
    echo "ID: {$c->id} | fecha: {$c->fecha} | estado: {$c->estado}" . PHP_EOL;
}

// Check schema of citas table
echo PHP_EOL . "=== CITAS TABLE COLUMNS ===" . PHP_EOL;
$columns = \DB::select("SHOW COLUMNS FROM citas");
foreach ($columns as $col) {
    echo "{$col->Field} ({$col->Type})" . PHP_EOL;
}

// Test actual getData query
echo PHP_EOL . "=== CHART DATA QUERY ===" . PHP_EOL;
$query = \App\Models\Citas::query()->whereDate('fecha', today());
$pendientes = $query->clone()->where('estado', 'Pendiente')->count();
$confirmadas = $query->clone()->where('estado', 'Confirmado')->count();
$realizadas = $query->clone()->where('estado', 'Realizada')->count();
$canceladas = $query->clone()->where('estado', 'Cancelado')->count();
echo "Pendientes: {$pendientes}" . PHP_EOL;
echo "Confirmadas: {$confirmadas}" . PHP_EOL;
echo "Realizadas: {$realizadas}" . PHP_EOL;
echo "Canceladas: {$canceladas}" . PHP_EOL;
