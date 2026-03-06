<?php

use App\Http\Controllers\TenantImpersonationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['tenant.resolve', 'tenant.initialized', 'tenant.canonical', 'tenant.maintenance', 'web'])->group(function () {
    Route::get('/tenant/impersonate/{token}', TenantImpersonationController::class)
        ->name('tenant.impersonate');
});
