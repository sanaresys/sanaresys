<?php

use App\Http\Controllers\TenantImpersonationController;
use App\Http\Controllers\TenantBillingController;
use App\Http\Controllers\TenantModuleBillingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['tenant.resolve', 'tenant.initialized', 'tenant.canonical', 'tenant.maintenance', 'web'])->group(function () {
    Route::get('/tenant/impersonate/{token}', TenantImpersonationController::class)
        ->name('tenant.impersonate');

    Route::middleware('auth')->group(function () {
        Route::get('/billing', [TenantBillingController::class, 'index'])
            ->name('tenant.billing.index');
        Route::get('/billing/inactive', [TenantBillingController::class, 'inactive'])
            ->name('tenant.billing.inactive');
        Route::post('/billing/reactivate', [TenantBillingController::class, 'startReactivation'])
            ->name('tenant.billing.reactivate');
        Route::post('/billing/invoices/{invoice}/order', [TenantBillingController::class, 'createOrder'])
            ->name('tenant.billing.invoices.order');
        Route::post('/billing/invoices/{invoice}/capture', [TenantBillingController::class, 'capture'])
            ->name('tenant.billing.invoices.capture');
        Route::post('/billing/cancel-at-period-end', [TenantBillingController::class, 'cancelAtPeriodEnd'])
            ->name('tenant.billing.cancel-at-period-end');
        Route::post('/billing/resume-renewal', [TenantBillingController::class, 'resumeRenewal'])
            ->name('tenant.billing.resume-renewal');
        Route::get('/billing/reactivate/return', [TenantBillingController::class, 'returnFromPayPal'])
            ->name('tenant.billing.reactivate.return');
        Route::get('/billing/reactivate/cancel', [TenantBillingController::class, 'cancel'])
            ->name('tenant.billing.reactivate.cancel');

        Route::get('/billing/modules', [TenantModuleBillingController::class, 'index'])
            ->name('tenant.billing.modules.index');
        Route::post('/billing/modules/checkout', [TenantModuleBillingController::class, 'startCheckout'])
            ->name('tenant.billing.modules.checkout');
        Route::post('/billing/modules/{module}/subscribe', [TenantModuleBillingController::class, 'subscribe'])
            ->name('tenant.billing.modules.subscribe');
        Route::post('/billing/modules/{module}/cancel-at-period-end', [TenantModuleBillingController::class, 'cancelAtPeriodEnd'])
            ->name('tenant.billing.modules.cancel-at-period-end');
        Route::get('/billing/modules/return', [TenantModuleBillingController::class, 'returnFromPayPal'])
            ->name('tenant.billing.modules.return');
        Route::get('/billing/modules/cancel', [TenantModuleBillingController::class, 'cancel'])
            ->name('tenant.billing.modules.cancel');
    });
});
