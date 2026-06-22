<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Billing de Registro</title>
    <style>
        body { font-family: Arial, sans-serif; background: linear-gradient(180deg, #f6fbfb 0%, #eef6ff 100%); margin: 0; padding: 24px; }
        .shell { max-width: 980px; margin: 0 auto; display: grid; grid-template-columns: 1.1fr .9fr; gap: 18px; }
        .card { background: #fff; border-radius: 18px; box-shadow: 0 18px 45px rgba(15, 23, 42, .08); padding: 24px; border: 1px solid #dbeafe; }
        .eyebrow { color: #0f766e; font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        h1 { margin: 10px 0 8px; color: #0f172a; font-size: 32px; }
        p { color: #475569; line-height: 1.5; }
        .amount { font-size: 42px; font-weight: 800; color: #0f172a; margin: 14px 0; }
        .muted { color: #64748b; }
        .summary { display: grid; gap: 12px; margin-top: 18px; }
        .summary-row { display: flex; justify-content: space-between; gap: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; }
        .pill { display: inline-block; padding: 6px 10px; border-radius: 999px; background: #dcfce7; color: #166534; font-weight: 700; font-size: 12px; }
        .consent { margin-top: 18px; padding: 14px; border-radius: 14px; background: #f8fafc; border: 1px solid #cbd5e1; }
        .consent label { display: flex; gap: 10px; align-items: flex-start; color: #0f172a; font-size: 14px; }
        .consent input { margin-top: 3px; }
        .msg { margin-top: 12px; padding: 12px 14px; border-radius: 12px; font-size: 14px; display: none; }
        .msg.show { display: block; }
        .msg.error { background: #fee2e2; color: #991b1b; }
        .msg.success { background: #dcfce7; color: #166534; }
        .badge { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; background: #ecfeff; color: #155e75; font-size: 12px; font-weight: 700; }
        #paypal-button-container { margin-top: 18px; }
        .meta-box { border-radius: 16px; background: #0f172a; color: #e2e8f0; padding: 22px; }
        .meta-box h2 { margin-top: 0; color: #fff; }
        .meta-box strong { color: #fff; }
        .actions { margin-top: 18px; display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { display: inline-block; text-decoration: none; border: 0; background: #1d4ed8; color: #fff; border-radius: 10px; padding: 11px 14px; font-weight: 700; cursor: pointer; }
        .btn.secondary { background: #334155; }
        @media (max-width: 860px) { .shell { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
@php
    $mode = (string) ($billingMode ?? 'onboarding');
    $isRenewalBilling = $mode === 'renewal';
    $isTrialActivation = ! $isRenewalBilling && (
        (float) ($invoice->total ?? 0) <= 0
        || data_get($invoice->meta, 'origin') === 'onboarding_trial'
    );
@endphp
<div class="shell">
    <section class="card">
        <div class="eyebrow">
            @if($isRenewalBilling)
                Renovacion de Plan
            @elseif($isTrialActivation)
                Activacion de Trial
            @else
                Billing Manual
            @endif
        </div>
        <h1>
            @if($isRenewalBilling)
                Renueva tu plan para continuar
            @elseif($isTrialActivation)
                Tu periodo gratis fue activado
            @else
                Tu clinica ya esta verificada
            @endif
        </h1>
        <p>
            @if($isRenewalBilling)
                El periodo gratis de <strong>{{ (int) ($freeTrialDays ?? 30) }} dias</strong> finalizo.
                Completa este pago para mantener activa la clinica <strong>{{ $registration->nombre_centro }}</strong>.
            @elseif($isTrialActivation)
                Tu clinica <strong>{{ $registration->nombre_centro }}</strong> esta en periodo gratis y no requiere tarjeta ni PayPal para esta activacion.
                Al vencer el trial, aqui mismo podras completar la renovacion.
            @else
                Ahora solo falta completar el pago inicial para activar <strong>{{ $registration->nombre_centro }}</strong> y provisionar el tenant.
            @endif
        </p>

        <div class="amount">USD {{ number_format((float) $invoice->total, 2) }}</div>
        <span class="pill">{{ strtoupper($registration->plan_code ?? 'monthly') }}</span>

        <div class="summary">
            @foreach($invoice->items as $item)
                <div class="summary-row">
                    <div>
                        <strong>{{ $item->description }}</strong>
                        <div class="muted">
                            {{ strtoupper($item->billing_interval ?? 'monthly') }}
                            @if($item->period_ends_at)
                                - vigencia hasta {{ $item->period_ends_at->format('d/m/Y') }}
                            @endif
                        </div>
                    </div>
                    <strong>USD {{ number_format((float) $item->amount, 2) }}</strong>
                </div>
            @endforeach
        </div>

        <div class="consent">
            <label>
                <input type="checkbox" id="billing-consent" {{ $registration->consent_at ? 'checked' : '' }}>
                <span>
                    @if($isRenewalBilling)
                        Autorizo continuar con el cobro de renovacion del plan y acepto la politica de facturacion vigente para periodos activos.
                    @else
                        Autorizo continuar con el cobro manual del plan seleccionado y acepto la politica de renovacion gestionada por el portal segun los modulos y periodos activos.
                    @endif
                    Version del texto: <strong>{{ $consentTextVersion }}</strong>.
                </span>
            </label>
        </div>

        <div id="billing-message" class="msg error"></div>
        <div id="paypal-button-container"></div>

        <div class="actions">
            <a class="btn secondary" href="{{ route('clinica.registro.waiting', ['publicId' => $registration->public_id]) }}">Volver</a>
        </div>
    </section>

    <aside class="meta-box">
        <h2>Resumen del registro</h2>
        <p><strong>Clinica:</strong> {{ $registration->nombre_centro }}</p>
        <p><strong>Administrador:</strong> {{ $registration->owner_name }}</p>
        <p><strong>Correo:</strong> {{ $registration->owner_email }}</p>
        <p><strong>Estado:</strong> {{ strtoupper($registration->status) }}</p>
        <p><strong>Factura:</strong> {{ $invoice->public_id }}</p>
        <p><strong>Vencimiento:</strong> {{ optional($invoice->due_at)->format('d/m/Y H:i') ?? 'Inmediato' }}</p>
        <p class="badge">Pago con cuenta PayPal o tarjeta dentro del flujo seguro de PayPal</p>
    </aside>
</div>

@if($paypalClientId !== '')
    <script src="https://www.paypal.com/sdk/js?client-id={{ urlencode($paypalClientId) }}&currency={{ urlencode($paypalCurrency) }}&intent=capture"></script>
    <script>
        const messageBox = document.getElementById('billing-message');
        const consent = document.getElementById('billing-consent');

        function showMessage(text, type = 'error') {
            messageBox.textContent = text;
            messageBox.className = 'msg show ' + type;
        }

        paypal.Buttons({
            createOrder() {
                if (!consent.checked) {
                    showMessage('Debes aceptar el consentimiento de billing para continuar.');
                    throw new Error('missing-consent');
                }

                return fetch(@json(route('clinica.registro.billing.order', ['publicId' => $registration->public_id])), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                    },
                    body: JSON.stringify({
                        consent: true,
                    }),
                })
                .then(async (response) => {
                    const payload = await response.json();
                    if (!response.ok) {
                        throw new Error(payload.message || 'No se pudo iniciar la orden.');
                    }

                    return payload.orderId;
                });
            },
            onApprove(data) {
                return fetch(@json(route('clinica.registro.billing.capture', ['publicId' => $registration->public_id])), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                    },
                    body: JSON.stringify({
                        order_id: data.orderID,
                    }),
                })
                .then(async (response) => {
                    const payload = await response.json();
                    if (!response.ok) {
                        throw new Error(payload.message || 'No se pudo confirmar el pago.');
                    }

                    window.location.href = payload.redirect_url;
                })
                .catch((error) => {
                    showMessage(error.message || 'No se pudo confirmar el pago.');
                });
            },
            onCancel() {
                showMessage('Checkout cancelado. Puedes intentarlo nuevamente.');
            },
            onError(err) {
                showMessage(err.message || 'Ocurrio un error con PayPal.');
            }
        }).render('#paypal-button-container');
    </script>
@else
    <script>
        document.getElementById('billing-message').className = 'msg show error';
        document.getElementById('billing-message').textContent = 'Falta configurar PAYPAL_CLIENT_ID para renderizar el checkout.';
    </script>
@endif
</body>
</html>
