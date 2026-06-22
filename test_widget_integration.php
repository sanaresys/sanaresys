<?php

require_once 'vendor/autoload.php';

// Test de integración del widget HistorialExamenes
echo "=== Test Widget HistorialExamenes Integration ===\n";

// Simular diferentes contextos
echo "\n1. Testando clase del widget...\n";

$widgetClass = 'App\Filament\Resources\Consultas\Widgets\HistorialExamenes';
if (class_exists($widgetClass)) {
    echo "✅ Clase del widget existe: $widgetClass\n";
    
    // Verificar que tiene el método getPacienteId
    $reflection = new ReflectionClass($widgetClass);
    if ($reflection->hasMethod('getPacienteId')) {
        echo "✅ Método getPacienteId() existe\n";
    } else {
        echo "❌ Método getPacienteId() NO existe\n";
    }
} else {
    echo "❌ Clase del widget NO existe: $widgetClass\n";
}

echo "\n2. Testando archivos del sistema...\n";

$files = [
    'app/Filament/Resources/Consultas/Widgets/HistorialExamenes.php' => 'Widget class',
    'resources/views/filament/resources/consultas/widgets/historial-examenes.blade.php' => 'Widget view',
    'app/Livewire/ExamenesPrevios.php' => 'Livewire component',
    'resources/views/livewire/examenes-previos.blade.php' => 'Livewire view',
    'app/Filament/Resources/Consultas/ConsultasResource/Pages/CreateConsultas.php' => 'Create page',
    'app/Filament/Resources/Consultas/ConsultasResource/Pages/EditConsultas.php' => 'Edit page'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description: $file\n";
    } else {
        echo "❌ $description NO existe: $file\n";
    }
}

echo "\n3. Verificando contenido de archivos clave...\n";

// Verificar widget view
$widgetViewPath = 'resources/views/filament/resources/consultas/widgets/historial-examenes.blade.php';
if (file_exists($widgetViewPath)) {
    $content = file_get_contents($widgetViewPath);
    if (strpos($content, 'getPacienteId()') !== false) {
        echo "✅ Widget view usa getPacienteId()\n";
    } else {
        echo "❌ Widget view NO usa getPacienteId()\n";
    }
} else {
    echo "❌ Widget view no encontrada\n";
}

// Verificar create page
$createPagePath = 'app/Filament/Resources/Consultas/ConsultasResource/Pages/CreateConsultas.php';
if (file_exists($createPagePath)) {
    $content = file_get_contents($createPagePath);
    if (strpos($content, 'getFooterWidgets') !== false && strpos($content, 'HistorialExamenes') !== false) {
        echo "✅ Create page tiene widget en footer\n";
    } else {
        echo "❌ Create page NO tiene widget en footer\n";
    }
} else {
    echo "❌ Create page no encontrada\n";
}

echo "\n4. Test de URL con parámetros...\n";

// Simular diferentes escenarios de parámetros
$testUrls = [
    '/consultas/create?paciente_id=16',
    '/consultas/create?paciente_id=16&cita_id=123',
    '/consultas/create',
];

foreach ($testUrls as $url) {
    echo "URL: $url\n";
    parse_str(parse_url($url, PHP_URL_QUERY), $params);
    if (isset($params['paciente_id'])) {
        echo "  ✅ paciente_id disponible: {$params['paciente_id']}\n";
    } else {
        echo "  ❌ paciente_id NO disponible\n";
    }
}

echo "\n=== Test completado ===\n";
echo "\nPróximos pasos para testing:\n";
echo "1. Crear una consulta nueva con URL: /consultas/create?paciente_id=16\n";
echo "2. Verificar que aparece el widget de historial en el footer\n";
echo "3. Confirmar que se muestra el historial de exámenes del paciente 16\n";
echo "4. Probar subir imagen de resultado desde la página de creación\n";
