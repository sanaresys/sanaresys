<?php

// Script para verificar sintaxis del ConsultasResource
try {
    require_once 'vendor/autoload.php';
    
    // Intentar cargar la clase
    if (class_exists('App\Filament\Resources\Consultas\ConsultasResource')) {
        echo "✅ Clase ConsultasResource existe\n";
        
        // Intentar crear el formulario
        $form = new \Filament\Forms\Form();
        $resource = \App\Filament\Resources\Consultas\ConsultasResource::form($form);
        echo "✅ Formulario se carga sin errores\n";
        
    } else {
        echo "❌ Clase ConsultasResource no existe\n";
    }
    
} catch (ParseError $e) {
    echo "❌ ERROR DE SINTAXIS: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
