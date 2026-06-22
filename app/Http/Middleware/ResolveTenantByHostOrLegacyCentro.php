<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;

class ResolveTenantByHostOrLegacyCentro
{
    public function handle(Request $request, Closure $next)
    {
        if (tenancy()->initialized) {
            $this->scopePermissionCacheKey(tenancy()->tenant);

            return $next($request);
        }

        $host = strtolower($request->getHost());
        $tenant = $this->findTenantByHost($host);

        if ($tenant) {
            try {
                tenancy()->initialize($tenant);
            } catch (\Throwable $e) {
                Log::error('No se pudo inicializar tenancy.', [
                    'host' => $host,
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);

                abort(500, 'No se pudo inicializar el tenant.');
            }
        }

        // Unknown non-central hosts must never initialize application context.
        if (! $tenant && ! $this->isCentralDomain($host) && ! in_array($host, ['localhost', '127.0.0.1'], true)) {
            abort(404);
        }

        $this->scopePermissionCacheKey($tenant);

        return $next($request);
    }

    protected function findTenantByHost(string $host): ?Tenant
    {
        if ($this->isCentralDomain($host)) {
            return null;
        }

        return Tenant::query()
            ->whereHas('domains', function ($query) use ($host) {
                $query->where('domain', $host);
            })
            ->first();
    }

    protected function isCentralDomain(string $host): bool
    {
        return in_array($host, $this->getCentralDomains(), true);
    }

    protected function getCentralDomains(): array
    {
        return array_values(array_filter(array_map(
            static fn (string $domain): string => strtolower(trim($domain)),
            (array) config('tenancy.central_domains', [])
        )));
    }

    protected function scopePermissionCacheKey(?Tenant $tenant): void
    {
        $baseKey = 'spatie.permission.cache';
        $currentConfigKey = (string) config('permission.cache.key', $baseKey);

        // Normalize key if current config already carries a context suffix.
        $normalizedBaseKey = preg_replace('/\.(tenant\.[^\.]+|central)$/', '', $currentConfigKey) ?: $baseKey;
        $context = $tenant ? 'tenant.' . $tenant->getTenantKey() : 'central';
        $scopedKey = $normalizedBaseKey . '.' . $context;

        if ($currentConfigKey !== $scopedKey) {
            config(['permission.cache.key' => $scopedKey]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
