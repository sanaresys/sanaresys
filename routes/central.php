<?php

use App\Http\Controllers\ClinicRegistrationController;
use App\Http\Controllers\RootPortalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'central.domain'])->group(function () {
    Route::get('/registro-clinica', [ClinicRegistrationController::class, 'create'])
        ->name('clinica.registro');

    Route::post('/registro-clinica', [ClinicRegistrationController::class, 'store'])
        ->name('clinica.registro.store');

    Route::get('/registro-clinica/esperando/{publicId}', [ClinicRegistrationController::class, 'waitVerification'])
        ->name('clinica.registro.waiting');

    Route::post('/registro-clinica/esperando/{publicId}/reenviar', [ClinicRegistrationController::class, 'resendVerification'])
        ->middleware('throttle:5,1')
        ->name('clinica.registro.resend');

    Route::get('/registro-clinica/verificar/{publicId}', [ClinicRegistrationController::class, 'verify'])
        ->middleware(['signed', 'throttle:20,1'])
        ->name('clinica.registro.verify');

    Route::get('/registro-clinica/exito', [ClinicRegistrationController::class, 'success'])
        ->name('clinica.registro.exito');

    Route::middleware(['auth'])->group(function () {
        Route::get('/portal/root', [RootPortalController::class, 'index'])
            ->name('portal.root');

        Route::post('/portal/root/tenant/{centro}/entrar', [RootPortalController::class, 'enterTenant'])
            ->name('portal.root.enter-tenant');
    });
});
