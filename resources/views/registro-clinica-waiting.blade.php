<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Estado del Registro</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fb; margin: 0; padding: 24px; }
        .card { max-width: 760px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 10px 25px rgba(0, 0, 0, .08); }
        h1 { margin: 0 0 8px; font-size: 26px; color: #111827; }
        .muted { color: #4b5563; margin-bottom: 18px; }
        .pill { display: inline-block; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
        .pending { background: #fef3c7; color: #92400e; }
        .ok { background: #d1fae5; color: #065f46; }
        .bad { background: #fee2e2; color: #991b1b; }
        .box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; margin: 16px 0; }
        .btn { border: 0; background: #0f766e; color: white; padding: 12px 16px; border-radius: 8px; cursor: pointer; font-weight: 700; }
        .btn-secondary { display: inline-block; text-decoration: none; background: #374151; color: #fff; padding: 12px 16px; border-radius: 8px; font-weight: 700; }
        .msg { margin: 10px 0; padding: 10px 12px; border-radius: 8px; font-size: 14px; }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        .status-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    </style>
</head>
<body>
<div class="card">
    <h1>Seguimiento del registro</h1>
    <p class="muted">
        Estado para <strong>{{ $registration->nombre_centro }}</strong>. Correo: <strong>{{ $registration->owner_email }}</strong>
    </p>
    <p class="muted" style="margin-top:-8px; margin-bottom:18px;">
        Flujo: <strong>registro -> verificacion por correo -> 30 dias gratis -> pago al vencimiento.</strong>
    </p>

    @if (session('status'))
        <div class="msg success">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="msg error">{{ session('error') }}</div>
    @endif

    @php
        $statusClass = match($registration->status) {
            \App\Models\ClinicRegistrationRequest::STATUS_PROVISIONED => 'ok',
            \App\Models\ClinicRegistrationRequest::STATUS_FAILED, \App\Models\ClinicRegistrationRequest::STATUS_EXPIRED => 'bad',
            default => 'pending',
        };
        $paymentClass = in_array($registration->payment_status, ['active', 'paid'], true)
            ? 'ok'
            : ($registration->payment_status === 'failed' ? 'bad' : 'pending');
    @endphp

    <div class="status-grid">
        <div>
            <span class="pill {{ $statusClass }}">Registro: {{ str_replace('_', ' ', $registration->status) }}</span>
        </div>
        <div>
            <span class="pill {{ $paymentClass }}">Pago: {{ $registration->payment_status ?? 'pending' }}</span>
        </div>
    </div>

    <div class="box">
        <p><strong>Plan:</strong> {{ strtoupper($registration->plan_code ?? 'N/A') }}</p>
        <p><strong>Enlace de verificacion vence:</strong> {{ optional($registration->verification_expires_at)->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
        <p><strong>Reenvios:</strong> {{ $registration->resend_count }}</p>
        <p><strong>Suscripcion PayPal:</strong> {{ $registration->paypal_subscription_id ?? 'Sin crear' }}</p>
    </div>

    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        @if($registration->isProvisioned())
            <a class="btn-secondary" href="{{ route('clinica.registro.tenant.enter', ['publicId' => $registration->public_id]) }}">Entrar al tenant</a>
        @endif

        @if($canStartPayment)
            <form method="POST" action="{{ route('clinica.registro.payment.start', ['publicId' => $registration->public_id]) }}">
                @csrf
                <button class="btn" type="submit">Ir a pago pendiente</button>
            </form>
        @endif

        @if($canResend)
            <form method="POST" action="{{ route('clinica.registro.resend', ['publicId' => $registration->public_id]) }}">
                @csrf
                <button class="btn" type="submit">Reenviar correo</button>
            </form>
        @endif

        <a class="btn-secondary" href="{{ route('clinica.registro') }}">Volver al formulario</a>
    </div>
</div>
</body>
</html>
