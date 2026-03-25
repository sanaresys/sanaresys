<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Root</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f3f4f6; padding: 24px; }
        .wrapper { max-width: 1400px; margin: 0 auto; background: #fff; border-radius: 18px; padding: 22px; box-shadow: 0 16px 40px rgba(15, 23, 42, .08); }
        h1 { margin: 0 0 10px; color: #0f172a; }
        .hint { color: #64748b; margin: 0 0 16px; }
        .msg { margin: 10px 0; padding: 10px 12px; border-radius: 10px; font-size: 14px; }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; font-size: 13px; vertical-align: top; }
        th { background: #f8fafc; }
        .badge { display: inline-block; border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: 700; margin-bottom: 4px; }
        .active { background: #dcfce7; color: #166534; }
        .past_due, .grace { background: #fef3c7; color: #92400e; }
        .suspended, .canceled, .pending { background: #fee2e2; color: #991b1b; }
        .override { background: #dbeafe; color: #1e40af; }
        .muted { color: #6b7280; }
        .actions { display: grid; gap: 8px; min-width: 280px; }
        .actions form { display: grid; gap: 6px; }
        button { border: 0; background: #0f766e; color: #fff; padding: 8px 10px; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 700; }
        button.secondary { background: #1f2937; }
        button.warning { background: #b45309; }
        button.danger { background: #dc2626; }
        input, select { border: 1px solid #cbd5e1; border-radius: 8px; padding: 7px 8px; font-size: 12px; width: 100%; box-sizing: border-box; }
        .tiny { font-size: 12px; line-height: 1.5; }
    </style>
</head>
<body>
<div class="wrapper">
    <h1>Portal Root - Billing Interno</h1>
    <p class="hint">Fuente de verdad local para estado, facturas, gracia, bloqueo y acciones manuales por tenant.</p>

    @if(session('status'))
        <div class="msg success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="msg error">{{ $errors->first() }}</div>
    @endif

    <table>
        <thead>
        <tr>
            <th>Clinica</th>
            <th>Estado</th>
            <th>Plan / Ciclo</th>
            <th>Factura abierta</th>
            <th>Modulos</th>
            <th>Root Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($centros as $centro)
            @php
                $subscription = $centro->billingTenantSubscription;
                $openInvoice = $centro->billingInvoices->first(fn ($invoice) => in_array($invoice->status, ['open', 'past_due'], true));
            @endphp
            <tr>
                <td>
                    <strong>{{ $centro->nombre_centro }}</strong><br>
                    <span class="muted">ID {{ $centro->id }} · {{ $centro->tenant?->getPrimaryDomain() ?? 'Sin dominio' }}</span><br>
                    <span class="muted">Ultimo sync: {{ optional($centro->billing_last_sync_at)->format('d/m/Y H:i') ?? 'N/A' }}</span>
                </td>
                <td>
                    <span class="badge {{ $centro->billing_status }}">{{ strtoupper($centro->billing_status ?? 'suspended') }}</span><br>
                    @if($centro->billing_override)
                        <span class="badge override">{{ $centro->billing_override }}</span><br>
                    @endif
                    <span class="muted">Renueva: {{ optional($centro->billing_renews_at)->format('d/m/Y H:i') ?? 'N/A' }}</span>
                </td>
                <td class="tiny">
                    <div><strong>Plan:</strong> {{ strtoupper($subscription?->plan_code ?? $centro->billing_plan_code ?? 'N/A') }}</div>
                    <div><strong>Intervalo:</strong> {{ strtoupper($subscription?->billing_interval ?? 'N/A') }}</div>
                    <div><strong>Proximo cobro:</strong> {{ optional($subscription?->next_charge_at)->format('d/m/Y') ?? 'N/A' }}</div>
                    <div><strong>Gracia:</strong> {{ optional($subscription?->grace_until)->format('d/m/Y H:i') ?? 'N/A' }}</div>
                    <div><strong>Cancelacion programada:</strong> {{ $subscription?->cancel_at_period_end ? 'Si' : 'No' }}</div>
                </td>
                <td class="tiny">
                    @if($openInvoice)
                        <div><strong>{{ $openInvoice->public_id }}</strong></div>
                        <div>{{ strtoupper(str_replace('_', ' ', $openInvoice->kind)) }}</div>
                        <div>USD {{ number_format((float) $openInvoice->total, 2) }}</div>
                        <div>Vence: {{ optional($openInvoice->due_at)->format('d/m/Y H:i') ?? 'N/A' }}</div>
                        <div>Estado: <span class="badge {{ $openInvoice->status }}">{{ strtoupper($openInvoice->status) }}</span></div>
                    @else
                        <span class="muted">Sin factura abierta</span>
                    @endif
                </td>
                <td class="tiny">
                    @forelse($centro->billingModuleSubscriptions as $moduleSubscription)
                        <div style="margin-bottom:8px;">
                            <span class="badge {{ $moduleSubscription->status }}">{{ strtoupper($moduleSubscription->module?->code ?? 'MOD') }} · {{ strtoupper($moduleSubscription->status) }}</span>
                            <div class="muted">
                                {{ strtoupper($moduleSubscription->billing_interval ?? 'monthly') }}
                                · proximo {{ optional($moduleSubscription->next_charge_at)->format('d/m/Y') ?? 'N/A' }}
                            </div>
                        </div>
                    @empty
                        <span class="muted">Sin modulos activos</span>
                    @endforelse
                </td>
                <td>
                    <div class="actions">
                        @if($centro->tenant)
                            <form method="POST" action="{{ route('portal.root.enter-tenant', $centro) }}">
                                @csrf
                                <button type="submit" class="secondary">Entrar al tenant</button>
                            </form>
                        @endif

                        @if($openInvoice)
                            <form method="POST" action="{{ route('portal.root.mark-invoice-paid', $centro) }}">
                                @csrf
                                <input type="hidden" name="invoice_id" value="{{ $openInvoice->id }}">
                                <button type="submit">Marcar factura pagada</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('portal.root.extend-billing', $centro) }}">
                            @csrf
                            <input type="number" name="days" min="1" max="365" value="7" required>
                            <input type="text" name="reason" placeholder="Motivo de extension" required minlength="5">
                            <button type="submit" class="secondary">Extender vigencia</button>
                        </form>

                        <form method="POST" action="{{ route('portal.root.set-billing-status', $centro) }}">
                            @csrf
                            <select name="status" required>
                                <option value="active">Active</option>
                                <option value="past_due">Past due</option>
                                <option value="grace">Grace</option>
                                <option value="suspended">Suspended</option>
                                <option value="canceled">Canceled</option>
                            </select>
                            <input type="text" name="reason" placeholder="Motivo del cambio" required minlength="5">
                            <button type="submit" class="warning">Cambiar estado</button>
                        </form>

                        <form method="POST" action="{{ route('portal.root.cancel-at-period-end', $centro) }}">
                            @csrf
                            <select name="enabled" required>
                                <option value="1">Programar cancelacion</option>
                                <option value="0">Quitar cancelacion</option>
                            </select>
                            <input type="text" name="reason" placeholder="Motivo" required minlength="5">
                            <button type="submit" class="secondary">Actualizar cancelacion</button>
                        </form>

                        <form method="POST" action="{{ route('portal.root.billing-override', $centro) }}">
                            @csrf
                            <select name="override" required>
                                <option value="force_active">Forzar activo</option>
                                <option value="force_inactive">Forzar suspendido</option>
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
                <td colspan="6">No hay centros en modo domain.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>
