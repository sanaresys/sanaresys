<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EnsureTenantNotInMaintenance
{
    public function handle(Request $request, Closure $next)
    {
        if (! tenancy()->initialized) {
            return $next($request);
        }

        $tenantId = tenancy()->tenant?->id;

        if (! $tenantId) {
            return $next($request);
        }

        if (Cache::get("tenant:maintenance:{$tenantId}") === true) {
            abort(503, 'El tenant está temporalmente en mantenimiento.');
        }

        return $next($request);
    }
}

