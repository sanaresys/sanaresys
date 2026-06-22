<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Suscripcion inactiva</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { max-width: 640px; width: 100%; background: #fff; border-radius: 14px; box-shadow: 0 10px 24px rgba(0, 0, 0, .1); padding: 24px; }
        h1 { margin: 0 0 8px; color: #111827; }
        p { color: #4b5563; }
        .meta { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px; margin: 14px 0; font-size: 14px; }
        .msg { margin: 10px 0; padding: 10px 12px; border-radius: 8px; font-size: 14px; }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        label { display: block; margin-bottom: 6px; font-size: 13px; color: #111827; }
        select { width: 100%; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; }
        button { border: 0; background: #0f766e; color: #fff; border-radius: 8px; padding: 11px 14px; cursor: pointer; font-weight: 700; }
        .logout { margin-top: 10px; }
    </style>
</head>
<body>
<div class="card">
    <h1>Suscripcion inactiva</h1>
    <p>El acceso administrativo de <strong>{{ $centro->nombre_centro }}</strong> esta bloqueado hasta reactivar un plan.</p>

    @if(session('status'))
        <div class="msg success">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="msg error">{{ session('error') }}</div>
    @endif

    <div class="meta">
        <div><strong>Estado actual:</strong> {{ strtoupper($centro->billing_status ?? 'inactive') }}</div>
        <div><strong>Plan actual:</strong> {{ strtoupper($centro->billing_plan_code ?? 'N/A') }}</div>
        <div><strong>Renovacion:</strong> {{ $centro->billing_renews_at?->format('d/m/Y H:i') ?? 'No definida' }}</div>
    </div>

    <form method="POST" action="{{ route('tenant.billing.reactivate') }}">
        @csrf
        <label for="plan_code">Selecciona plan para reactivar</label>
        <select id="plan_code" name="plan_code" required>
            @foreach($plans as $code => $plan)
                <option value="{{ $code }}" {{ $selectedPlanCode === $code ? 'selected' : '' }}>
                    {{ $plan['name'] ?? strtoupper($code) }} - USD {{ number_format((float) ($plan['price'] ?? 0), 2) }}
                </option>
            @endforeach
        </select>

        <div style="margin-top: 12px;">
            <button type="submit">Reactivar con PayPal</button>
        </div>
    </form>

    <form class="logout" method="POST" action="{{ route('filament.admin.auth.logout') }}">
        @csrf
        <button type="submit" style="background:#1f2937;">Cerrar sesion</button>
    </form>
</div>
</body>
</html>
