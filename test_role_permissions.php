<?php

use App\Models\User;
use App\Models\Citas;
use App\Models\Consulta;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

echo "=== TEST DE PERMISOS POR ROLES ===\n\n";

// Test 1: Buscar usuarios por rol
echo "1. VERIFICANDO USUARIOS POR ROL:\n";

$root = User::whereHas('roles', function($q) {
    $q->where('name', 'root');
})->first();

$admin = User::whereHas('roles', function($q) {
    $q->where('name', 'administrador');
})->first();

$medico = User::whereHas('roles', function($q) {
    $q->where('name', 'medico');
})->first();

echo "- Root: " . ($root ? $root->name . " (ID: {$root->id})" : "No encontrado") . "\n";
echo "- Administrador: " . ($admin ? $admin->name . " (ID: {$admin->id})" : "No encontrado") . "\n";
echo "- Médico: " . ($medico ? $medico->name . " (ID: {$medico->id})" : "No encontrado") . "\n\n";

// Test 2: Verificar permisos de citas
if ($medico) {
    echo "2. PERMISOS DEL MÉDICO:\n";
    Auth::login($medico);
    
    $cita = Citas::first();
    if ($cita) {
        echo "- Ver citas: " . (Gate::allows('view', $cita) ? "✓" : "✗") . "\n";
        echo "- Crear citas: " . (Gate::allows('create', Citas::class) ? "✓" : "✗") . "\n";
        echo "- Editar citas: " . (Gate::allows('update', $cita) ? "✓" : "✗") . "\n";
        echo "- Eliminar citas: " . (Gate::allows('delete', $cita) ? "✓" : "✗") . "\n";
        echo "- Crear consultas: " . (Gate::allows('create', Consulta::class) ? "✓" : "✗") . "\n";
    }
    Auth::logout();
    echo "\n";
}

if ($admin) {
    echo "3. PERMISOS DEL ADMINISTRADOR:\n";
    Auth::login($admin);
    
    $cita = Citas::first();
    if ($cita) {
        echo "- Ver citas: " . (Gate::allows('view', $cita) ? "✓" : "✗") . "\n";
        echo "- Crear citas: " . (Gate::allows('create', Citas::class) ? "✓" : "✗") . "\n";
        echo "- Editar citas: " . (Gate::allows('update', $cita) ? "✓" : "✗") . "\n";
        echo "- Eliminar citas: " . (Gate::allows('delete', $cita) ? "✓" : "✗") . "\n";
        echo "- Crear consultas: " . (Gate::allows('create', Consulta::class) ? "✓" : "✗") . "\n";
    }
    Auth::logout();
    echo "\n";
}

if ($root) {
    echo "4. PERMISOS DEL ROOT:\n";
    Auth::login($root);
    
    $cita = Citas::first();
    if ($cita) {
        echo "- Ver citas: " . (Gate::allows('view', $cita) ? "✓" : "✗") . "\n";
        echo "- Crear citas: " . (Gate::allows('create', Citas::class) ? "✓" : "✗") . "\n";
        echo "- Editar citas: " . (Gate::allows('update', $cita) ? "✓" : "✗") . "\n";
        echo "- Eliminar citas: " . (Gate::allows('delete', $cita) ? "✓" : "✗") . "\n";
        echo "- Crear consultas: " . (Gate::allows('create', Consulta::class) ? "✓" : "✗") . "\n";
    }
    Auth::logout();
    echo "\n";
}

// Test 3: Verificar filtrado de datos
echo "5. VERIFICANDO FILTRADO DE DATOS:\n";

$totalCitas = Citas::count();
echo "- Total de citas en BD: {$totalCitas}\n";

if ($medico) {
    Auth::login($medico);
    $citasMedico = Citas::where('medico_id', $medico->medico?->id)->count();
    echo "- Citas del médico: {$citasMedico}\n";
    Auth::logout();
}

$totalConsultas = Consulta::count();
echo "- Total de consultas en BD: {$totalConsultas}\n\n";

echo "=== FIN DEL TEST ===\n";
