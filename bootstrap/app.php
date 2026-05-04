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
        $middleware->prependToGroup('web', \App\Http\Middleware\ResolveTenantByHostOrLegacyCentro::class);

        $middleware->alias([
            'tenant.resolve' => \App\Http\Middleware\ResolveTenantByHostOrLegacyCentro::class,
            'tenant.canonical' => \App\Http\Middleware\EnforceCanonicalTenantDomain::class,
            'tenant.maintenance' => \App\Http\Middleware\EnsureTenantNotInMaintenance::class,
            'tenant.initialized' => \App\Http\Middleware\EnsureTenantInitialized::class,
            'tenant.subscription.active' => \App\Http\Middleware\EnsureTenantSubscriptionActive::class,
            'tenant.module.active' => \App\Http\Middleware\EnsureTenantModuleActive::class,
            'central.domain' => \App\Http\Middleware\EnsureCentralDomain::class,
            'require.onboarding' => \App\Http\Middleware\RequireOnboarding::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/paypal',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
