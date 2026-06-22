<?php

require_once 'vendor/autoload.php';

use App\Models\TipoPago;

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Primeros IDs únicos por tipo de pago:\n";

$efectivo = TipoPago::where('nombre', 'Efectivo')->first();
$tarjeta = TipoPago::where('nombre', 'Tarjeta')->first();
$pos = TipoPago::where('nombre', 'POS')->first();

if ($efectivo) echo "Efectivo: ID {$efectivo->id}\n";
if ($tarjeta) echo "Tarjeta: ID {$tarjeta->id}\n";
if ($pos) echo "POS: ID {$pos->id}\n";
