<?php

use App\Http\Controllers\TenantImpersonationController;
use App\Http\Controllers\TenantBillingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['tenant.resolve', 'tenant.initialized', 'tenant.canonical', 'tenant.maintenance', 'web'])->group(function () {
    Route::get('/tenant/impersonate/{token}', TenantImpersonationController::class)
        ->name('tenant.impersonate');

    Route::middleware('auth')->group(function () {
        Route::get('/billing/inactive', [TenantBillingController::class, 'inactive'])
            ->name('tenant.billing.inactive');
        Route::post('/billing/reactivate', [TenantBillingController::class, 'startReactivation'])
            ->name('tenant.billing.reactivate');
        Route::get('/billing/reactivate/return', [TenantBillingController::class, 'returnFromPayPal'])
            ->name('tenant.billing.reactivate.return');
        Route::get('/billing/reactivate/cancel', [TenantBillingController::class, 'cancel'])
            ->name('tenant.billing.reactivate.cancel');
    });
});
