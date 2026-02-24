<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCentralDomain
{
    public function handle(Request $request, Closure $next)
    {
        $host = strtolower($request->getHost());
        $centralDomains = array_values(array_filter(array_map(
            static fn (string $domain): string => strtolower(trim($domain)),
            (array) config('tenancy.central_domains', [])
        )));

        if (! in_array($host, $centralDomains, true)) {
            abort(404);
        }

        return $next($request);
    }
}

