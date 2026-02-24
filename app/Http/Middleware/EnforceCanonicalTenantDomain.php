<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnforceCanonicalTenantDomain
{
    public function handle(Request $request, Closure $next)
    {
        if (! tenancy()->initialized) {
            return $next($request);
        }

        $tenant = tenancy()->tenant;

        if (! method_exists($tenant, 'getPrimaryDomain')) {
            return $next($request);
        }

        $expectedHost = strtolower((string) $tenant->getPrimaryDomain());
        $currentHost = strtolower($request->getHost());

        if (! $expectedHost || $expectedHost === $currentHost) {
            return $next($request);
        }

        $scheme = $request->isSecure() ? 'https' : 'http';
        $target = "{$scheme}://{$expectedHost}{$request->getRequestUri()}";

        Log::info('Tenant alias redireccionado a dominio canonico.', [
            'tenant_id' => $tenant->id ?? null,
            'from_host' => $currentHost,
            'to_host' => $expectedHost,
            'path' => $request->getRequestUri(),
        ]);

        return redirect()->away($target, 302);
    }
}
