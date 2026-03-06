<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTenantInitialized
{
    public function handle(Request $request, Closure $next)
    {
        if (! tenancy()->initialized) {
            abort(404);
        }

        return $next($request);
    }
}

