<?php

use App\Http\Controllers\ClinicRegistrationController;
use App\Http\Controllers\RootPortalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'central.domain'])->group(function () {
    Route::get('/registro-clinica', [ClinicRegistrationController::class, 'create'])
        ->name('clinica.registro');

    Route::post('/registro-clinica', [ClinicRegistrationController::class, 'store'])
        ->name('clinica.registro.store');

    Route::get('/registro-clinica/exito', [ClinicRegistrationController::class, 'success'])
        ->name('clinica.registro.exito');

    Route::middleware(['auth'])->group(function () {
        Route::get('/portal/root', [RootPortalController::class, 'index'])
            ->name('portal.root');

        Route::post('/portal/root/tenant/{centro}/entrar', [RootPortalController::class, 'enterTenant'])
            ->name('portal.root.enter-tenant');
    });
});

