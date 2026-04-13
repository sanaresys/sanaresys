<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Estado de pagos</title>
    <style>
        :root {
            --bg: #f3f6fb;
            --card: #ffffff;
            --ink: #0f172a;
            --muted: #475569;
            --line: #d7e1ee;
            --soft: #f8fbff;
            --brand: #0f766e;
            --brand-dark: #0b5b55;
            --action: #1d4ed8;
            --action-dark: #173ea9;
            --warning: #b45309;
            --warning-dark: #8a3f07;
            --danger: #b91c1c;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
            background: radial-gradient(circle at top left, #ffffff 0%, var(--bg) 55%, #edf3ff 100%);
        }

        .shell {
            max-width: 1180px;
            margin: 0 auto;
            display: grid;
            gap: 18px;
        }

        .hero,
        .grid {
            display: grid;
            gap: 18px;
        }

        .hero {
            grid-template-columns: 1.1fr .9fr;
        }

        .grid {
            grid-template-columns: 1fr 1fr;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 24px;
            box-shadow: 0 22px 55px rgba(15, 23, 42, .08);
        }

        h1, h2, h3 {
            margin-top: 0;
            color: var(--ink);
        }

        h1 {
            margin-bottom: 10px;
            font-size: 2.15rem;
        }

        h2 {
            margin-bottom: 10px;
            font-size: 1.75rem;
        }

        h3 {
            margin-bottom: 8px;
            font-size: 1.3rem;
        }

        p {
            margin: 0;
            line-height: 1.6;
            color: var(--muted);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #e6fffb;
            color: var(--brand-dark);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .section-copy {
            margin-bottom: 16px;
        }

        .status-copy {
            margin-top: 12px;
            font-size: 15px;
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .active,
        .paid {
            background: #dcfce7;
            color: #166534;
        }

        .past_due,
        .grace,
        .warning-badge {
            background: #fef3c7;
            color: #92400e;
        }

        .suspended,
        .canceled,
        .pending,
        .failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .open {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .available {
            background: #e2e8f0;
            color: #334155;
        }

        .refunded {
            background: #ede9fe;
            color: #6d28d9;
        }

        .voided {
            background: #e5e7eb;
            color: #374151;
        }

        .meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 18px;
        }

        .meta-box {
            padding: 15px;
            border: 1px solid #cbd5e1;
            border-radius: 16px;
            background: var(--soft);
        }

        .meta-box strong {
            display: block;
            margin-bottom: 6px;
            color: var(--ink);
        }

        .amount {
            margin: 8px 0 14px;
            font-size: 44px;
            font-weight: 800;
            color: var(--ink);
        }

        .msg {
            margin-bottom: 12px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 14px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .warning {
            background: #fef3c7;
            color: #92400e;
        }

        .notice {
            margin-top: 16px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1e3a8a;
        }

        .divider {
            height: 1px;
            margin: 18px 0;
            background: linear-gradient(90deg, rgba(148, 163, 184, 0), rgba(148, 163, 184, .55), rgba(148, 163, 184, 0));
        }

        .list {
            display: grid;
            gap: 10px;
            margin-top: 14px;
        }

        .list-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: var(--soft);
        }

        .list-item strong {
            display: block;
            color: var(--ink);
        }

        .small {
            font-size: 13px;
            color: #64748b;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: top;
            font-size: 14px;
        }

        .table th {
            font-size: 12px;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #334155;
        }

        .actions,
        .module-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .actions {
            margin-top: 16px;
        }

        .module-actions {
            margin-top: 14px;
        }

        .btn {
            border: 0;
            border-radius: 12px;
            padding: 11px 15px;
            font-weight: 700;
            color: #fff;
            background: var(--action);
            text-decoration: none;
            cursor: pointer;
            transition: transform .18s ease, background .18s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            background: var(--action-dark);
        }

        .btn.secondary {
            background: #334155;
        }

        .btn.secondary:hover {
            background: #1f2937;
        }

        .btn.warning {
            background: var(--warning);
        }

        .btn.warning:hover {
            background: var(--warning-dark);
        }

        .btn.danger {
            background: var(--danger);
        }

        .btn.danger:hover {
            background: #991b1b;
        }

        select {
            min-width: 160px;
            padding: 9px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: #fff;
        }

        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 14px;
        }

        .module-card {
            padding: 18px;
            border: 1px solid #dbeafe;
            border-radius: 20px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .muted {
            color: #64748b;
            font-size: 13px;
        }

        #invoice-message {
            margin-top: 12px;
        }

        @media (max-width: 920px) {
            .hero,
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
@php
    $tenantStatusLabels = [
        'active' => 'Al dia',
        'past_due' => 'Pago pendiente',
        'grace' => 'En gracia',
        'suspended' => 'Suspendida',
        'canceled' => 'Cancelada',
    ];

    $invoiceKindLabels = [
        'onboarding' => 'Pago inicial de la clinica',
        'renewal' => 'Renovacion del plan',
        'reactivation' => 'Reactivacion de la clinica',
        'module_proration' => 'Cobro proporcional de modulo',
        'refund_replacement' => 'Saldo pendiente por reverso',
        'base_plan' => 'Plan principal',
        'module_renewal' => 'Renovacion de modulo',
    ];

    $invoiceStatusLabels = [
        'open' => 'Pendiente',
        'past_due' => 'Atrasada',
        'paid' => 'Pagada',
        'refunded' => 'Revertida',
        'voided' => 'Anulada',
    ];

    $moduleStatusLabels = [
        'active' => 'Activo',
        'pending' => 'Pendiente',
        'past_due' => 'Pago pendiente',
        'grace' => 'En gracia',
        'suspended' => 'Suspendido',
        'canceled' => 'Cancelado',
        'available' => 'Disponible',
    ];

    $tenantStatusText = $tenantStatusLabels[$tenantSubscription->status] ?? strtoupper($tenantSubscription->status);

    $tenantStatusHelp = match ($tenantSubscription->status) {
        'active' => 'Tu clinica esta al dia. Puedes seguir trabajando normalmente.',
        'past_due' => 'Hay un cobro pendiente, pero todavia conservas acceso.',
        'grace' => 'Hay un pago pendiente y la clinica sigue dentro del periodo de gracia.',
        'suspended' => 'La clinica esta suspendida por falta de pago. Necesitas pagar para recuperar acceso.',
        'canceled' => 'La renovacion se detuvo y la clinica quedo fuera del ciclo normal.',
        default => 'Revisa esta pantalla para confirmar si tienes alguna accion pendiente.',
    };
@endphp

<div class="shell">
    @if(session('status'))
        <div class="msg success">{{ session('status') }}</div>
    @endif

    @if(session('error'))
        <div class="msg error">{{ session('error') }}</div>
    @endif

    <section class="hero">
        <div class="card">
            <span class="eyebrow">Resumen de pagos</span>
            <h1>Pagos de {{ $centro->nombre_centro }}</h1>
            <p class="section-copy">Desde aqui puedes ver si tu clinica esta al dia, cuando vuelve a vencer tu plan y si hay algun cobro pendiente.</p>

            <span class="badge {{ $tenantSubscription->status }}">{{ $tenantStatusText }}</span>
            <p class="status-copy">{{ $tenantStatusHelp }}</p>

            <div class="meta">
                <div class="meta-box">
                    <strong>Plan actual</strong>
                    <div>{{ strtoupper($tenantSubscription->plan_code) }}</div>
                </div>
                <div class="meta-box">
                    <strong>Se cobra cada</strong>
                    <div>{{ $tenantSubscription->billing_interval === 'annual' ? 'Ano' : 'Mes' }}</div>
                </div>
                <div class="meta-box">
                    <strong>Proximo vencimiento</strong>
                    <div>{{ optional($tenantSubscription->next_charge_at)->format('d/m/Y') ?? 'Sin fecha' }}</div>
                </div>
                <div class="meta-box">
                    <strong>Periodo de gracia</strong>
                    <div>{{ optional($tenantSubscription->grace_until)->format('d/m/Y H:i') ?? 'No aplica' }}</div>
                </div>
            </div>

            @if($tenantSubscription->cancel_at_period_end)
                <div class="notice">
                    La renovacion esta detenida al final del periodo actual. La clinica seguira activa hasta
                    <strong>{{ optional($tenantSubscription->next_charge_at)->format('d/m/Y') ?? 'la fecha registrada' }}</strong>.
                </div>
            @else
                <div class="notice">
                    La renovacion sigue habilitada. Si no quieres continuar despues de este periodo, puedes detenerla aqui.
                </div>
            @endif

            <div class="actions">
                @if($tenantSubscription->cancel_at_period_end)
                    <form method="POST" action="{{ route('tenant.billing.resume-renewal') }}">
                        @csrf
                        <button class="btn secondary" type="submit">Seguir renovando normalmente</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('tenant.billing.cancel-at-period-end') }}">
                        @csrf
                        <button class="btn warning" type="submit">Detener renovacion al final de este periodo</button>
                    </form>
                @endif

                <a class="btn secondary" href="/admin">Volver al panel</a>
            </div>
        </div>

        <div class="card">
            <span class="eyebrow">Pago pendiente</span>
            <h2>Lo que esta esperando pago</h2>

            @if($openInvoice)
                @php
                    $friendlyKind = $invoiceKindLabels[$openInvoice->kind] ?? strtoupper(str_replace('_', ' ', $openInvoice->kind));
                    $friendlyInvoiceStatus = $invoiceStatusLabels[$openInvoice->status] ?? strtoupper($openInvoice->status);
                @endphp

                <div class="amount">USD {{ number_format((float) $openInvoice->total, 2) }}</div>

                <div class="meta">
                    <div class="meta-box">
                        <strong>Motivo del cobro</strong>
                        <div>{{ $friendlyKind }}</div>
                    </div>
                    <div class="meta-box">
                        <strong>Fecha limite</strong>
                        <div>{{ optional($openInvoice->due_at)->format('d/m/Y H:i') ?? 'Inmediato' }}</div>
                    </div>
                    <div class="meta-box">
                        <strong>Estado</strong>
                        <div><span class="badge {{ $openInvoice->status }}">{{ $friendlyInvoiceStatus }}</span></div>
                    </div>
                </div>

                @if($openInvoice->items->isNotEmpty())
                    <div class="divider"></div>
                    <p class="section-copy">Este pago incluye:</p>

                    <div class="list">
                        @foreach($openInvoice->items as $item)
                            <div class="list-item">
                                <div>
                                    <strong>{{ $item->description }}</strong>
                                    <div class="small">
                                        {{ $invoiceKindLabels[$item->item_type] ?? ucfirst(str_replace('_', ' ', $item->item_type)) }}
                                        @if($item->period_starts_at || $item->period_ends_at)
                                            | {{ optional($item->period_starts_at)->format('d/m/Y') ?? 'Inicio' }} - {{ optional($item->period_ends_at)->format('d/m/Y') ?? 'Fin' }}
                                        @endif
                                    </div>
                                </div>
                                <strong>USD {{ number_format((float) $item->amount, 2) }}</strong>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($openInvoice->kind === 'module_proration')
                    <div class="notice">
                        Este cobro aparece cuando agregas un modulo a mitad del periodo. Se cobra solo la parte proporcional
                        del tiempo que falta en el ciclo actual.
                    </div>
                @elseif(in_array($tenantSubscription->status, ['active', 'past_due', 'grace'], true))
                    <div class="notice">
                        Este es el cobro pendiente mas reciente. Tu estado general y tus facturas se muestran por separado para que
                        puedas ver claramente si la clinica sigue activa y que pago falta resolver.
                    </div>
                @endif

                <div id="invoice-message" class="msg error" style="display:none;"></div>
                <div id="paypal-button-container"></div>
            @else
                <div class="msg success">No tienes pagos pendientes en este momento.</div>
            @endif
        </div>
    </section>

    <section class="grid">
        <div class="card">
            <span class="eyebrow">Historial</span>
            <h2>Pagos y facturas anteriores</h2>

            <table class="table">
                <thead>
                <tr>
                    <th>Referencia</th>
                    <th>Concepto</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Cubre</th>
                </tr>
                </thead>
                <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->public_id }}</td>
                        <td>{{ $invoiceKindLabels[$invoice->kind] ?? strtoupper(str_replace('_', ' ', $invoice->kind)) }}</td>
                        <td>USD {{ number_format((float) $invoice->total, 2) }}</td>
                        <td>
                            <span class="badge {{ $invoice->status }}">
                                {{ $invoiceStatusLabels[$invoice->status] ?? strtoupper($invoice->status) }}
                            </span>
                        </td>
                        <td>{{ optional($invoice->billing_starts_at)->format('d/m/Y') }} - {{ optional($invoice->billing_ends_at)->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Todavia no hay historial para mostrar.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card">
            <span class="eyebrow">Modulos</span>
            <h2>Servicios adicionales</h2>
            <p class="section-copy">Aqui puedes activar modulos extra para tu clinica. Si activas uno a mitad del periodo, primero se genera un cobro proporcional y despues entra al siguiente ciclo normal.</p>

            <div class="module-grid">
                @foreach($modules as $module)
                    @php
                        $subscription = $module->subscriptions->first();
                        $moduleStatus = $subscription?->status ?? 'available';
                        $moduleStatusText = $moduleStatusLabels[$moduleStatus] ?? strtoupper($moduleStatus);
                    @endphp

                    <div class="module-card">
                        <h3>{{ $module->name }}</h3>
                        <p class="muted">{{ $module->description ?: 'Servicio adicional para ampliar lo que puede hacer tu clinica.' }}</p>

                        <div class="divider"></div>

                        <p><strong>Precio mensual:</strong> USD {{ number_format((float) $module->price_monthly, 2) }}</p>
                        <p><strong>Precio anual:</strong> USD {{ number_format((float) ($module->price_annual ?: ($module->price_monthly * 12)), 2) }}</p>
                        <p>
                            <strong>Estado actual:</strong>
                            <span class="badge {{ $moduleStatus }}">{{ $moduleStatusText }}</span>
                        </p>
                        <p class="muted">Proximo cobro estimado: {{ optional($subscription?->next_charge_at)->format('d/m/Y') ?? 'Se calcula cuando lo actives' }}</p>

                        @if($subscription?->cancel_at_period_end)
                            <div class="notice">Este modulo ya quedo programado para terminar al final del periodo actual.</div>
                        @endif

                        @if(! $subscription || in_array($subscription->status, ['suspended', 'canceled', 'pending'], true))
                            <form method="POST" action="{{ route('tenant.billing.modules.subscribe', $module) }}">
                                @csrf
                                <label class="small" for="billing-interval-{{ $module->id }}">Quieres cobrarlo como:</label>
                                <select id="billing-interval-{{ $module->id }}" name="billing_interval">
                                    <option value="monthly">Mensual</option>
                                    <option value="annual">Anual</option>
                                </select>

                                <div class="module-actions">
                                    <button class="btn" type="submit">Activar modulo</button>
                                </div>
                            </form>
                        @else
                            <form method="POST" action="{{ route('tenant.billing.modules.cancel-at-period-end', $module) }}">
                                @csrf
                                <div class="module-actions">
                                    <button class="btn warning" type="submit">Dar de baja al terminar este periodo</button>
                                </div>
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
                        throw new Error(payload.message || 'No se pudo iniciar el pago.');
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
                showMessage('Cancelaste el pago. Puedes intentarlo nuevamente.');
            },
            onError(err) {
                showMessage(err.message || 'Ocurrio un error con PayPal.');
            }
        }).render('#paypal-button-container');
    </script>
@endif
</body>
</html>
