<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modulos de la Clinica</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f3f4f6; padding: 24px; }
        .wrapper { max-width: 980px; margin: 0 auto; }
        .header { background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 10px 24px rgba(0,0,0,.08); margin-bottom: 16px; }
        .header h1 { margin: 0 0 8px; }
        .msg { margin: 10px 0; padding: 10px 12px; border-radius: 8px; font-size: 14px; }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; }
        .card { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 6px 18px rgba(0,0,0,.06); border: 1px solid #e5e7eb; }
        .badge { display: inline-block; font-size: 12px; font-weight: 700; border-radius: 999px; padding: 4px 10px; }
        .active { background: #dcfce7; color: #166534; }
        .inactive { background: #fee2e2; color: #991b1b; }
        .refund_review { background: #fef3c7; color: #92400e; }
        .muted { color: #6b7280; font-size: 13px; }
        .price { font-size: 22px; font-weight: 700; color: #111827; margin: 10px 0 4px; }
        button { border: 0; background: #0f766e; color: #fff; border-radius: 8px; padding: 10px 12px; cursor: pointer; font-weight: 700; width: 100%; }
        .disabled { background: #374151; color: #fff; display: inline-block; border-radius: 8px; padding: 8px 10px; font-size: 12px; }
        .top-actions { margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap; }
        .top-actions a { text-decoration: none; font-weight: 700; color: #fff; background: #1f2937; border-radius: 8px; padding: 10px 12px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Modulos de {{ $centro->nombre_centro }}</h1>
        <p class="muted">Gestiona la compra y renovacion mensual de modulos adicionales del sistema.</p>

        @if(session('status'))
            <div class="msg success">{{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="msg error">{{ session('error') }}</div>
        @endif
        @if(!empty($moduleBillingUnavailable))
            <div class="msg error">Facturacion modular no disponible temporalmente. Ejecuta migraciones pendientes e intenta de nuevo.</div>
        @endif

        <div class="top-actions">
            <a href="/admin">Volver al panel</a>
        </div>
    </div>

    <div class="grid">
        @forelse($modules as $item)
            @php
                $module = $item['module'];
                $subscription = $item['subscription'];
                $status = $item['effective_status'];
                $latestOrder = $item['latest_order'];
            @endphp
            <div class="card">
                <h3 style="margin: 0 0 8px;">{{ $module->name }}</h3>
                <span class="badge {{ $status }}">{{ strtoupper(str_replace('_', ' ', $status)) }}</span>

                <div class="price">USD {{ number_format((float) $module->price_monthly, 2) }}<span style="font-size:13px;font-weight:500;"> / mes</span></div>
                <p class="muted" style="min-height: 36px;">{{ $module->description ?: 'Modulo adicional configurable para tu clinica.' }}</p>

                <p class="muted"><strong>Renueva:</strong> {{ $subscription?->renews_at?->format('d/m/Y H:i') ?? 'Sin vigencia activa' }}</p>
                <p class="muted"><strong>Ultima orden:</strong> {{ $latestOrder?->paypal_order_id ?? 'Sin ordenes' }}</p>

                @if($canPurchase)
                    <form method="POST" action="{{ route('tenant.billing.modules.checkout') }}">
                        @csrf
                        <input type="hidden" name="module_code" value="{{ $module->code }}">
                        <button type="submit">
                            {{ $status === 'active' ? 'Renovar modulo' : 'Comprar modulo' }}
                        </button>
                    </form>
                @else
                    <span class="disabled">Solo administradores autorizados pueden comprar o renovar modulos.</span>
                @endif
            </div>
        @empty
            <div class="card">
                <h3 style="margin: 0 0 8px;">Sin modulos disponibles</h3>
                <p class="muted">No hay modulos cargados en el catalogo actualmente.</p>
            </div>
        @endforelse
    </div>
</div>
</body>
</html>
