<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Root</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f3f4f6; padding: 24px; }
        .wrapper { max-width: 1200px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 10px 24px rgba(0, 0, 0, .08); }
        h1 { margin: 0 0 12px; }
        .hint { color: #6b7280; margin: 0 0 16px; }
        .msg { margin: 10px 0; padding: 10px 12px; border-radius: 8px; font-size: 14px; }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; font-size: 13px; vertical-align: top; }
        th { background: #f9fafb; }
        .badge { display: inline-block; border-radius: 999px; padding: 2px 10px; font-size: 12px; font-weight: 700; }
        .active { background: #dcfce7; color: #166534; }
        .inactive { background: #fee2e2; color: #991b1b; }
        .override { background: #dbeafe; color: #1e40af; }
        .muted { color: #6b7280; }
        .actions { display: flex; flex-direction: column; gap: 6px; min-width: 260px; }
        .actions form { display: grid; gap: 6px; }
        button { border: 0; background: #0f766e; color: #fff; padding: 8px 10px; border-radius: 8px; cursor: pointer; font-size: 12px; }
        button.secondary { background: #1f2937; }
        button.danger { background: #dc2626; }
        input, select { border: 1px solid #d1d5db; border-radius: 8px; padding: 7px 8px; font-size: 12px; width: 100%; box-sizing: border-box; }
        .enter-btn { background: #2563eb; text-decoration: none; color: #fff; padding: 7px 10px; border-radius: 8px; display: inline-block; font-size: 12px; }
    </style>
</head>
<body>
<div class="wrapper">
    <h1>Portal Root - Billing y Tenants</h1>
    <p class="hint">Vista central de estado de suscripciones, renovaciones y acceso por tenant.</p>

    @if(session('status'))
        <div class="msg success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="msg error">{{ $errors->first() }}</div>
    @endif

    <table>
        <thead>
        <tr>
            <th>Clinica</th>
            <th>Tenant / Dominio</th>
            <th>Estado</th>
            <th>Plan</th>
            <th>Renovacion</th>
            <th>Ultimo Sync</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        @forelse($centros as $centro)
            @php
                $tenant = $centro->tenant;
                $latestSubscription = $centro->billingSubscriptions->first();
                $renewsAt = $centro->billing_renews_at;
                $daysRemaining = $renewsAt ? now()->diffInDays($renewsAt, false) : null;
            @endphp
            <tr>
                <td>
                    <strong>{{ $centro->nombre_centro }}</strong><br>
                    <span class="muted">ID: {{ $centro->id }} | Slug: {{ $centro->slug ?? 'N/A' }}</span>
                </td>
                <td>
                    @if($tenant)
                        <span class="muted">{{ $tenant->id }}</span><br>
                        <span>{{ $tenant->getPrimaryDomain() ?? 'Sin dominio' }}</span>
                    @else
                        <span class="muted">Sin tenant</span>
                    @endif
                </td>
                <td>
                    <span class="badge {{ $centro->billing_status === 'active' ? 'active' : 'inactive' }}">
                        {{ strtoupper($centro->billing_status ?? 'inactive') }}
                    </span>
                    @if($centro->billing_override)
                        <br><span class="badge override">{{ $centro->billing_override }}</span>
                    @endif
                </td>
                <td>
                    <strong>{{ strtoupper($centro->billing_plan_code ?? 'N/A') }}</strong><br>
                    <span class="muted">{{ strtoupper($latestSubscription->provider_status ?? 'SIN DATOS') }}</span>
                </td>
                <td>
                    @if($renewsAt)
                        {{ $renewsAt->format('d/m/Y H:i') }}<br>
                        <span class="muted">{{ $daysRemaining }} dias restantes</span>
                    @else
                        <span class="muted">No definida</span>
                    @endif
                </td>
                <td>
                    @if($centro->billing_last_sync_at)
                        {{ $centro->billing_last_sync_at->format('d/m/Y H:i') }}
                    @else
                        <span class="muted">Sin sync</span>
                    @endif
                </td>
                <td>
                    <div class="actions">
                        @if($tenant)
                            <form method="POST" action="{{ route('portal.root.enter-tenant', $centro) }}">
                                @csrf
                                <button type="submit" class="secondary">Entrar al tenant</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('portal.root.billing-override', $centro) }}">
                            @csrf
                            <select name="override" required>
                                <option value="force_active">Forzar activo</option>
                                <option value="force_inactive">Forzar inactivo</option>
                                <option value="none">Quitar override</option>
                            </select>
                            <input type="text" name="reason" placeholder="Motivo de auditoria" required minlength="5">
                            <button type="submit" class="danger">Aplicar override</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">No hay centros en modo domain.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>
