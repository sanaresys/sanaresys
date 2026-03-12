<?php

use App\Http\Controllers\ClinicRegistrationController;
use App\Http\Controllers\PayPalWebhookController;
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

    Route::post('/registro-clinica/{publicId}/pago', [ClinicRegistrationController::class, 'startPayment'])
        ->name('clinica.registro.payment.start');

    Route::get('/registro-clinica/{publicId}/pago/retorno', [ClinicRegistrationController::class, 'paymentReturn'])
        ->name('clinica.registro.payment.return');

    Route::get('/registro-clinica/{publicId}/pago/cancelar', [ClinicRegistrationController::class, 'paymentCancel'])
        ->name('clinica.registro.payment.cancel');

    Route::get('/registro-clinica/exito', [ClinicRegistrationController::class, 'success'])
        ->name('clinica.registro.exito');

    Route::post('/webhooks/paypal', PayPalWebhookController::class)
        ->name('webhooks.paypal');

    Route::middleware(['auth'])->group(function () {
        Route::get('/portal/root', [RootPortalController::class, 'index'])
            ->name('portal.root');

        Route::post('/portal/root/tenant/{centro}/entrar', [RootPortalController::class, 'enterTenant'])
            ->name('portal.root.enter-tenant');

        Route::post('/portal/root/tenant/{centro}/billing-override', [RootPortalController::class, 'setBillingOverride'])
            ->name('portal.root.billing-override');
    });
});
