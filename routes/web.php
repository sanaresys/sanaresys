<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\FacturaPdfController;
use App\Http\Controllers\NominaController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\OnboardingController;

Route::get('/', function () {
    return view('welcome');
});

// Rutas de Onboarding (requieren autenticación)
Route::middleware(['auth', 'tenant.resolve'])->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/', [OnboardingController::class, 'welcome'])->name('welcome');
    
    Route::get('/step-1', [OnboardingController::class, 'stepOne'])->name('step-1');
    Route::post('/step-1', [OnboardingController::class, 'saveStepOne'])->name('save-step-1');
    
    Route::get('/step-2', [OnboardingController::class, 'stepTwo'])->name('step-2');
    Route::post('/step-2', [OnboardingController::class, 'saveStepTwo'])->name('save-step-2');
    Route::post('/skip-cai', [OnboardingController::class, 'skipCai'])->name('skip-cai');
    
    Route::get('/step-3', [OnboardingController::class, 'stepThree'])->name('step-3');
    Route::post('/step-3', [OnboardingController::class, 'saveStepThree'])->name('save-step-3');
    
    Route::get('/complete', [OnboardingController::class, 'complete'])->name('complete');
    Route::post('/mark-completed', [OnboardingController::class, 'markCompleted'])->name('mark-completed');
});


//Rutas para facturas y diseños
Route::prefix('facturas')->group(function () {
    Route::get('preview/{diseno}/pdf', [FacturaController::class, 'generarPDF'])->name('facturas.preview.pdf');
    Route::get('preview-demo', [FacturaController::class, 'vistaPreviewDemo'])->name('facturas.preview.demo');
    Route::get('{factura}/pdf', [FacturaController::class, 'generarFacturaReal'])->name('facturas.pdf');
    Route::get('{factura}/pdf-diseño', [FacturaController::class, 'generarPDFFactura'])->name('facturas.pdf.diseno');
});

Route::get('/receta/{receta}/imprimir', [RecetaController::class, 'imprimir'])->name('receta.imprimir');

// Rutas para PDFs de facturas
Route::get('/factura/{factura}/pdf', [FacturaPdfController::class, 'generarPdf'])->name('factura.pdf');
Route::get('/factura/{factura}/pdf/preview', [FacturaPdfController::class, 'previewPdf'])->name('factura.pdf.preview');
Route::post('/factura/{factura}/pdf/guardar', [FacturaPdfController::class, 'guardarPdf'])->name('factura.pdf.guardar');
Route::get('/facturas/pdf/lote', [FacturaPdfController::class, 'generarPdfLote'])->name('facturas.pdf.lote');


// Rutas para imprimir recetas
Route::get('/receta/{receta}/imprimir', [RecetaController::class, 'imprimir'])->name('recetas.imprimir');
Route::get('/consulta/{consulta}/recetas/imprimir', [RecetaController::class, 'imprimirPorConsulta'])->name('recetas.imprimir.consulta');

// Rutas para imprimir exámenes
Route::get('/examen/{examen}/imprimir', [ExamenController::class, 'imprimir'])->name('examenes.imprimir');
Route::get('/consulta/{consulta}/examenes/imprimir', [ExamenController::class, 'imprimirPorConsulta'])->name('examenes.imprimir.consulta');

// Rutas para nóminas
Route::get('/nomina/{nomina}/pdf', [NominaController::class, 'generarPDFNomina'])->name('nomina.pdf');
