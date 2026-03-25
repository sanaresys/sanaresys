<?php

namespace App\Http\Middleware;

use App\Services\Billing\TenantModuleAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantModuleActive
{
    public function __construct(
        protected TenantModuleAccessService $moduleAccessService,
    ) {
    }

    public function handle(Request $request, Closure $next, string $moduleCode): Response
    {
        if (! tenancy()->initialized || ! tenancy()->tenant) {
            return $next($request);
        }

        if ($this->moduleAccessService->isModuleActive($moduleCode)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'El modulo no esta activo para esta clinica.');
        }

        return redirect()
            ->route('tenant.billing.modules.index')
            ->with('error', 'Debes adquirir o renovar este modulo para usar esta funcionalidad.');
    }
}

