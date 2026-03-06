<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant.resolve' => \App\Http\Middleware\ResolveTenantByHostOrLegacyCentro::class,
            'tenant.canonical' => \App\Http\Middleware\EnforceCanonicalTenantDomain::class,
            'tenant.maintenance' => \App\Http\Middleware\EnsureTenantNotInMaintenance::class,
            'tenant.initialized' => \App\Http\Middleware\EnsureTenantInitialized::class,
            'central.domain' => \App\Http\Middleware\EnsureCentralDomain::class,
            'require.onboarding' => \App\Http\Middleware\RequireOnboarding::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
