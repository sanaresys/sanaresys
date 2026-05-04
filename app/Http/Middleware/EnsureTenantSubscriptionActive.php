<?php

namespace App\Http\Middleware;

use App\Models\Centros_Medico;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! tenancy()->initialized) {
            return $next($request);
        }

        if ($request->routeIs('filament.admin.auth.*')) {
            return $next($request);
        }

        if ($request->routeIs('filament.admin.pages.billing')) {
            return $next($request);
        }

        if ($request->routeIs('tenant.billing.*')) {
            return $next($request);
        }

        $tenant = tenancy()->tenant;
        if (! $tenant || ! $tenant->centro_id) {
            return $next($request);
        }

        $user = auth()->user();
        if ($user && $user->hasRole('root')) {
            return $next($request);
        }

        $centro = Centros_Medico::on('mysql')
            ->select(['id', 'billing_status'])
            ->find($tenant->centro_id);

        if (! $centro || in_array($centro->billing_status, ['active', 'past_due', 'grace'], true)) {
            return $next($request);
        }

        if ($user) {
            return redirect()->route('tenant.billing.inactive');
        }

        return redirect()->route('filament.admin.auth.login');
    }
}
