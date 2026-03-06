<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Medico;

// Buscar médicos sin restricciones de scope
$medicos = Medico::withoutGlobalScopes()->with('persona')->get();

echo "=== LISTA DE MÉDICOS ===\n";
foreach ($medicos as $medico) {
    $nombre = $medico->persona ? $medico->persona->nombre_completo : 'Sin nombre';
    echo "ID: {$medico->id}, Nombre: {$nombre}\n";
}

// Buscar específicamente a Aurelia Mraz
echo "\n=== BUSCANDO A AURELIA MRAZ ===\n";
$aurelia = Medico::withoutGlobalScopes()
    ->whereHas('persona', function($q) {
        $q->where('primer_nombre', 'like', '%Aurelia%')
            ->orWhere('segundo_nombre', 'like', '%Aurelia%')
            ->orWhere('primer_apellido', 'like', '%Mraz%')
            ->orWhere('segundo_apellido', 'like', '%Mraz%');
    })
    ->with('persona')
    ->first();

if ($aurelia) {
    echo "¡Encontrada! ID: {$aurelia->id}, Nombre: {$aurelia->persona->nombre_completo}\n";
} else {
    echo "No se encontró a Aurelia Mraz\n";
}

// Buscar el primer médico disponible
$primerMedico = Medico::withoutGlobalScopes()->first();
if ($primerMedico) {
    echo "\nPrimer médico disponible: ID: {$primerMedico->id}\n";
} else {
    echo "\nNo hay médicos en el sistema\n";
}
