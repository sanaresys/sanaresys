<?php

require_once 'vendor/autoload.php';

use App\Models\Servicio;
use Illuminate\Support\Facades\DB;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Creando servicios adicionales...\n";

// Obtener el primer centro médico
$centro = DB::table('centros_medicos')->first();
if (!$centro) {
    echo "No hay centros médicos disponibles\n";
    exit;
}

echo "Usando centro médico ID: {$centro->id}\n";

// Crear servicios adicionales
$servicios = [
    [
        'nombre' => 'Examen General',
        'codigo' => 'EXG001',
        'descripcion' => 'Examen médico general',
        'precio_unitario' => 500.00,
        'centro_id' => $centro->id
    ],
    [
        'nombre' => 'Radiografía',
        'codigo' => 'RAD001',
        'descripcion' => 'Radiografía de tórax',
        'precio_unitario' => 350.00,
        'centro_id' => $centro->id
    ],
    [
        'nombre' => 'Análisis de Sangre',
        'codigo' => 'ANA001',
        'descripcion' => 'Análisis de sangre completo',
        'precio_unitario' => 250.00,
        'centro_id' => $centro->id
    ],
    [
        'nombre' => 'Electrocardiograma',
        'codigo' => 'ECG001',
        'descripcion' => 'Electrocardiograma',
        'precio_unitario' => 400.00,
        'centro_id' => $centro->id
    ],
    [
        'nombre' => 'Ultrasonido',
        'codigo' => 'ULT001',
        'descripcion' => 'Ultrasonido abdominal',
        'precio_unitario' => 600.00,
        'centro_id' => $centro->id
    ]
];

foreach ($servicios as $servicioData) {
    // Verificar si ya existe
    $existe = Servicio::where('codigo', $servicioData['codigo'])->first();
    if (!$existe) {
        $servicio = Servicio::create($servicioData);
        echo "✓ Creado: {$servicio->nombre} (ID: {$servicio->id})\n";
    } else {
        echo "- Ya existe: {$existe->nombre} (ID: {$existe->id})\n";
    }
}

echo "\nServicios disponibles ahora:\n";
foreach (Servicio::all() as $servicio) {
    echo "ID: {$servicio->id} - {$servicio->nombre} - L.{$servicio->precio_unitario}\n";
}

echo "\nListo para probar!\n";
