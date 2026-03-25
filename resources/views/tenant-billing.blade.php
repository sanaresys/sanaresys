<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Billing Tenant</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f7fb; padding: 24px; }
        .shell { max-width: 1180px; margin: 0 auto; display: grid; gap: 18px; }
        .hero { display: grid; grid-template-columns: 1.1fr .9fr; gap: 18px; }
        .card { background: #fff; border-radius: 18px; padding: 22px; box-shadow: 0 18px 44px rgba(15, 23, 42, .08); border: 1px solid #e2e8f0; }
        h1, h2, h3 { margin-top: 0; color: #0f172a; }
        p { color: #475569; line-height: 1.5; }
        .badge { display: inline-block; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .active { background: #dcfce7; color: #166534; }
        .past_due, .grace { background: #fef3c7; color: #92400e; }
        .suspended, .canceled, .pending { background: #fee2e2; color: #991b1b; }
        .meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-top: 18px; }
        .meta-box { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 14px; padding: 14px; }
        .amount { font-size: 40px; font-weight: 800; color: #0f172a; margin: 8px 0 16px; }
        .msg { padding: 12px 14px; border-radius: 12px; font-size: 14px; margin-bottom: 12px; }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        .warning { background: #fef3c7; color: #92400e; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border-bottom: 1px solid #e2e8f0; text-align: left; padding: 10px 8px; font-size: 14px; vertical-align: top; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; }
        .btn { border: 0; background: #1d4ed8; color: #fff; border-radius: 10px; padding: 10px 14px; font-weight: 700; cursor: pointer; text-decoration: none; }
        .btn.secondary { background: #334155; }
        .btn.warning { background: #b45309; }
        .btn.danger { background: #dc2626; }
        form.inline { display: inline; }
        select { border: 1px solid #cbd5e1; border-radius: 10px; padding: 9px 10px; }
        .module-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px; }
        .module-card { border: 1px solid #dbeafe; border-radius: 16px; padding: 16px; background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%); }
        .muted { color: #64748b; font-size: 13px; }
        #invoice-message { margin-top: 12px; }
        @media (max-width: 920px) {
            .hero, .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="shell">
    @if(session('status'))
        <div class="msg success">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="msg error">{{ session('error') }}</div>
    @endif

    <section class="hero">
        <div class="card">
            <h1>Billing de {{ $centro->nombre_centro }}</h1>
            <p>El portal administra el ciclo del plan base y los módulos desde la base local, mientras PayPal procesa los pagos manuales.</p>
            <span class="badge {{ $tenantSubscription->status }}">{{ strtoupper($tenantSubscription->status) }}</span>

            <div class="meta">
                <div class="meta-box">
                    <strong>Plan</strong>
                    <div>{{ strtoupper($tenantSubscription->plan_code) }}</div>
                </div>
                <div class="meta-box">
                    <strong>Intervalo</strong>
                    <div>{{ strtoupper($tenantSubscription->billing_interval) }}</div>
                </div>
                <div class="meta-box">
                    <strong>Renueva</strong>
                    <div>{{ optional($tenantSubscription->next_charge_at)->format('d/m/Y') ?? 'Sin fecha' }}</div>
                </div>
                <div class="meta-box">
                    <strong>Gracia hasta</strong>
                    <div>{{ optional($tenantSubscription->grace_until)->format('d/m/Y H:i') ?? 'No aplica' }}</div>
                </div>
            </div>

            <div class="actions">
                @if($tenantSubscription->cancel_at_period_end)
                    <form method="POST" action="{{ route('tenant.billing.resume-renewal') }}">
                        @csrf
                        <button class="btn secondary" type="submit">Reactivar renovacion</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('tenant.billing.cancel-at-period-end') }}">
                        @csrf
                        <button class="btn warning" type="submit">Cancelar al final del periodo</button>
                    </form>
                @endif
                <a class="btn secondary" href="/admin">Volver al panel</a>
            </div>
        </div>

        <div class="card">
            <h2>Factura abierta</h2>
            @if($openInvoice)
                <div class="amount">USD {{ number_format((float) $openInvoice->total, 2) }}</div>
                <p><strong>Tipo:</strong> {{ strtoupper(str_replace('_', ' ', $openInvoice->kind)) }}</p>
                <p><strong>Vence:</strong> {{ optional($openInvoice->due_at)->format('d/m/Y H:i') ?? 'Inmediato' }}</p>
                <p><strong>Estado:</strong> <span class="badge {{ $openInvoice->status }}">{{ strtoupper($openInvoice->status) }}</span></p>

                <div id="invoice-message" class="msg error" style="display:none;"></div>
                <div id="paypal-button-container"></div>
            @else
                <div class="msg success">No hay facturas abiertas en este momento.</div>
            @endif
        </div>
    </section>

    <section class="grid">
        <div class="card">
            <h2>Historial de facturas</h2>
            <table class="table">
                <thead>
                <tr>
                    <th>Factura</th>
                    <th>Tipo</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Periodo</th>
                </tr>
                </thead>
                <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->public_id }}</td>
                        <td>{{ strtoupper(str_replace('_', ' ', $invoice->kind)) }}</td>
                        <td>USD {{ number_format((float) $invoice->total, 2) }}</td>
                        <td><span class="badge {{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span></td>
                        <td>{{ optional($invoice->billing_starts_at)->format('d/m/Y') }} - {{ optional($invoice->billing_ends_at)->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Todavia no hay historial de facturas.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>Modulos</h2>
            <div class="module-grid">
                @foreach($modules as $module)
                    @php
                        $subscription = $module->subscriptions->first();
                    @endphp
                    <div class="module-card">
                        <h3>{{ $module->name }}</h3>
                        <p class="muted">{{ $module->description ?: 'Modulo adicional para ampliar la clinica.' }}</p>
                        <p><strong>Mensual:</strong> USD {{ number_format((float) $module->price_monthly, 2) }}</p>
                        <p><strong>Anual:</strong> USD {{ number_format((float) ($module->price_annual ?: ($module->price_monthly * 12)), 2) }}</p>
                        <p>
                            <strong>Estado:</strong>
                            <span class="badge {{ $subscription?->status ?? 'pending' }}">
                                {{ strtoupper($subscription?->status ?? 'DISPONIBLE') }}
                            </span>
                        </p>
                        <p class="muted">Proximo cobro: {{ optional($subscription?->next_charge_at)->format('d/m/Y') ?? 'Se calcula al activar' }}</p>

                        @if(! $subscription || in_array($subscription->status, ['suspended', 'canceled', 'pending'], true))
                            <form method="POST" action="{{ route('tenant.billing.modules.subscribe', $module) }}">
                                @csrf
                                <select name="billing_interval">
                                    <option value="monthly">Mensual</option>
                                    <option value="annual">Anual</option>
                                </select>
                                <div class="actions">
                                    <button class="btn" type="submit">Agregar modulo</button>
                                </div>
                            </form>
                        @else
                            <form method="POST" action="{{ route('tenant.billing.modules.cancel-at-period-end', $module) }}">
                                @csrf
                                <button class="btn warning" type="submit">Cancelar al final del periodo</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>

@if($openInvoice && $paypalClientId !== '')
    <script src="https://www.paypal.com/sdk/js?client-id={{ urlencode($paypalClientId) }}&currency={{ urlencode($paypalCurrency) }}&intent=capture"></script>
    <script>
        const messageBox = document.getElementById('invoice-message');
        function showMessage(text) {
            messageBox.textContent = text;
            messageBox.style.display = 'block';
        }

        paypal.Buttons({
            createOrder() {
                return fetch(@json(route('tenant.billing.invoices.order', $openInvoice)), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                    }
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
                return fetch(@json(route('tenant.billing.invoices.capture', $openInvoice)), {
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
                .catch((error) => showMessage(error.message || 'No se pudo confirmar el pago.'));
            },
            onCancel() {
                showMessage('Checkout cancelado. Puedes intentarlo nuevamente.');
            },
            onError(err) {
                showMessage(err.message || 'Ocurrio un error con PayPal.');
            }
        }).render('#paypal-button-container');
    </script>
@endif
</body>
</html>
