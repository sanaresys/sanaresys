@php
    $showBanner = false;
    $alerts = collect();

    if (auth()->check() && tenancy()->initialized) {
        $user = auth()->user();
        if ($user && ($user->hasRole('root') || $user->can('gestionar modulos billing'))) {
            $alerts = app(\App\Services\Billing\TenantModuleAccessService::class)
                ->expiringModulesForCurrentTenant((array) config('billing.module_billing.reminder_offsets', [7, 3, 1]));
            $showBanner = $alerts->isNotEmpty();
        }
    }
@endphp

@if($showBanner)
    <div style="padding: 8px 16px; background: #fef3c7; border-bottom: 1px solid #f59e0b; color: #92400e; font-size: 13px;">
        <strong>Recordatorio de renovacion:</strong>
        @foreach($alerts as $alert)
            <span style="margin-right: 8px;">
                {{ strtoupper($alert['module_name'] ?? $alert['module_code'] ?? 'MODULO') }} vence en
                <strong>{{ $alert['days_before_expiry'] }}</strong> dia(s)
                ({{ $alert['renews_at']?->format('d/m/Y') }}).
            </span>
        @endforeach
        <a href="{{ route('tenant.billing.modules.index') }}" style="margin-left: 6px; font-weight: 700; color: #92400e; text-decoration: underline;">
            Renovar ahora
        </a>
    </div>
@endif

