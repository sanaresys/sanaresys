<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $factura->numero_factura }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #2563eb;
        }

        .logo-section {
            flex: 1;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 8px;
        }

        .company-info {
            color: #666;
            line-height: 1.6;
        }

        .invoice-info {
            text-align: right;
            flex: 1;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 8px;
        }

        .invoice-number {
            font-size: 16px;
            color: #666;
            margin-bottom: 5px;
        }

        .invoice-date {
            color: #666;
        }

        .billing-section {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            gap: 40px;
        }

        .billing-info {
            flex: 1;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-block {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #2563eb;
        }

        .info-row {
            margin-bottom: 8px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .label {
            font-weight: bold;
            color: #374151;
            display: inline-block;
            width: 120px;
        }

        .value {
            color: #6b7280;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .items-table thead {
            background: #2563eb;
            color: white;
        }

        .items-table th,
        .items-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .items-table tbody tr:hover {
            background: #f3f4f6;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-section {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }

        .totals-table {
            width: 300px;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .totals-table .label-col {
            text-align: right;
            font-weight: 600;
            color: #374151;
            width: 60%;
        }

        .totals-table .amount-col {
            text-align: right;
            color: #6b7280;
            width: 40%;
        }

        .totals-table .total-row {
            background: #2563eb;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .totals-table .total-row td {
            border-bottom: none;
        }

        .payment-info {
            margin: 30px 0;
            background: #f0f9ff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #bae6fd;
        }

        .payment-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pagada {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .status-pendiente {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .status-parcial {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 11px;
        }

        .observations {
            margin: 20px 0;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #6b7280;
        }

        .currency {
            font-weight: 600;
            color: #059669;
        }

        @media print {
            .container {
                padding: 0;
            }
            
            body {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="logo">{{ $factura->centro->nombre_centro ?? 'Centro Médico' }}</div>
                <div class="company-info">
                    @if($factura->centro)
                        <div>{{ $factura->centro->direccion ?? 'Dirección no disponible' }}</div>
                        <div>Tel: {{ $factura->centro->telefono ?? 'N/A' }}</div>
                        @if($factura->centro->email)
                            <div>Email: {{ $factura->centro->email }}</div>
                        @endif
                        @if($factura->centro->rtn)
                            <div>RTN: {{ $factura->centro->rtn }}</div>
                        @endif
                    @endif
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">FACTURA</div>
                <div class="invoice-number"># 
                    @if($factura->usa_cai && $factura->caiCorrelativo)
                        {{ $factura->caiCorrelativo->numero_factura }}
                    @else
                        PROV-{{ $factura->centro_id }}-{{ $factura->fecha_emision->year }}-{{ str_pad($factura->fecha_emision->month, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($factura->id, 6, '0', STR_PAD_LEFT) }}
                    @endif
                </div>
                
                <!-- CAI debajo del número de factura -->
                @if($factura->usa_cai && $factura->caiAutorizacion)
                    <div style="font-size: 12px; color: #666; margin: 5px 0;">
                        <strong>CAI:</strong> {{ $factura->caiAutorizacion->cai_codigo }}
                    </div>
                @endif
                
                <div class="invoice-date">{{ $factura->fecha_emision->format('d/m/Y') }}</div>
                <div class="payment-status status-{{ strtolower($factura->estado) }}">
                    {{ ucfirst($factura->estado) }}
                </div>
            </div>
        </div>

        <!-- Información de Facturación -->
        <div class="billing-section">
            <div class="billing-info">
                <div class="section-title">Facturar a:</div>
                <div class="info-block">
                    @if($factura->paciente && $factura->paciente->persona)
                        <div class="info-row">
                            <span class="label">Paciente:</span>
                            <span class="value">{{ $factura->paciente->persona->nombre_completo }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Identidad:</span>
                            <span class="value">{{ $factura->paciente->persona->dni ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Teléfono:</span>
                            <span class="value">{{ $factura->paciente->persona->telefono ?? 'N/A' }}</span>
                        </div>
                    @else
                        <div class="info-row">
                            <span class="value">Información del paciente no disponible</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="billing-info">
                <div class="section-title">Información Médica:</div>
                <div class="info-block">
                    @if($factura->medico && $factura->medico->persona)
                        <div class="info-row">
                            <span class="label">Médico:</span>
                            <span class="value">Dr. {{ $factura->medico->persona->nombre_completo }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Especialidad:</span>
                            <span class="value">
                                @if($factura->medico->especialidades && $factura->medico->especialidades->count() > 0)
                                    {{ $factura->medico->especialidades->pluck('nombre')->join(', ') }}
                                @else
                                    General
                                @endif
                            </span>
                        </div>
                    @else
                        <div class="info-row">
                            <span class="label">Médico:</span>
                            <span class="value">No asignado</span>
                        </div>
                    @endif
                    @if($factura->cita)
                        <div class="info-row">
                            <span class="label">Fecha Cita:</span>
                            <span class="value">{{ $factura->cita->fecha ? \Carbon\Carbon::parse($factura->cita->fecha)->format('d/m/Y H:i') : 'N/A' }}</span>
                        </div>
                    @endif
                    @if($factura->consulta)
                        <div class="info-row">
                            <span class="label">Tipo Consulta:</span>
                            <span class="value">{{ $factura->consulta->tipo ?? 'Consulta General' }}</span>
                        </div>
                    @else
                        <div class="info-row">
                            <span class="label">Tipo Consulta:</span>
                            <span class="value">Consulta General</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabla de Servicios -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40%">Descripción del Servicio</th>
                    <th style="width: 10%" class="text-center">Cantidad</th>
                    <th style="width: 15%" class="text-right">Precio Unit.</th>
                    <th style="width: 10%" class="text-right">Descuento</th>
                    <th style="width: 10%" class="text-right">Impuesto</th>
                    <th style="width: 15%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($factura->detalles as $detalle)
                    <tr>
                        <td>
                            <strong>{{ $detalle->servicio->nombre ?? 'Consulta Médica' }}</strong>
                            @if($detalle->servicio && $detalle->servicio->descripcion)
                                <br><small style="color: #6b7280;">{{ $detalle->servicio->descripcion }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $detalle->cantidad ?? 1 }}</td>
                        <td class="text-right">L. {{ number_format($detalle->precio_unitario ?? 0, 2) }}</td>
                        <td class="text-right">
                            @if(isset($detalle->descuento_monto) && $detalle->descuento_monto > 0)
                                L. {{ number_format($detalle->descuento_monto, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right">
                            @if(isset($detalle->impuesto_monto) && $detalle->impuesto_monto > 0)
                                L. {{ number_format($detalle->impuesto_monto, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right currency">L. {{ number_format($detalle->total_linea ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 30px; color: #6b7280;">
                            <strong>Consulta Médica General</strong><br>
                            <small>Servicio médico completo</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Totales -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label-col">Subtotal:</td>
                    <td class="amount-col">L. {{ number_format($factura->subtotal, 2) }}</td>
                </tr>
                @if($factura->descuento_total > 0)
                    <tr>
                        <td class="label-col">Descuento Total:</td>
                        <td class="amount-col">- L. {{ number_format($factura->descuento_total, 2) }}</td>
                    </tr>
                @endif
                @if($factura->impuesto_total > 0)
                    <tr>
                        <td class="label-col">Impuestos (15%):</td>
                        <td class="amount-col">L. {{ number_format($factura->impuesto_total, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td class="label-col">TOTAL:</td>
                    <td class="amount-col">L. {{ number_format($factura->total, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Información de Pagos -->
        @if($factura->pagos && $factura->pagos->count() > 0)
            <div class="payment-info">
                <div class="section-title">Historial de Pagos:</div>
                @php
                    $totalPagado = $factura->pagos->sum('monto_recibido');
                    $saldoPendiente = $factura->total - $totalPagado;
                @endphp
                
                @foreach($factura->pagos as $pago)
                    <div class="info-row">
                        <span class="label">{{ $pago->fecha_pago ? \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') : 'N/A' }}:</span>
                        <span class="value">L. {{ number_format($pago->monto_recibido, 2) }} 
                            @if($pago->tipoPago)
                                ({{ $pago->tipoPago->nombre }})
                            @endif
                            @if($pago->monto_devolucion > 0)
                                - Cambio: L. {{ number_format($pago->monto_devolucion, 2) }}
                            @endif
                        </span>
                    </div>
                @endforeach
                
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #bae6fd;">
                    <div class="info-row">
                        <span class="label">Total Pagado:</span>
                        <span class="value currency">L. {{ number_format($totalPagado, 2) }}</span>
                    </div>
                    @if($saldoPendiente > 0)
                        <div class="info-row">
                            <span class="label">Saldo Pendiente:</span>
                            <span class="value" style="color: #dc2626; font-weight: bold;">L. {{ number_format($saldoPendiente, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="payment-info">
                <div class="section-title">Estado de Pago:</div>
                <div class="info-row">
                    <span class="value" style="color: #dc2626; font-weight: bold;">
                        No se han registrado pagos para esta factura
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Saldo Pendiente:</span>
                    <span class="value" style="color: #dc2626; font-weight: bold;">L. {{ number_format($factura->total, 2) }}</span>
                </div>
            </div>
        @endif

        <!-- Observaciones -->
        @if($factura->observaciones)
            <div class="observations">
                <div class="section-title">Observaciones:</div>
                <div>{{ $factura->observaciones }}</div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div>Factura generada el {{ now()->format('d/m/Y H:i:s') }}</div>
            <div style="margin-top: 10px;">
                @if($factura->createdByUser)
                    Creada por: {{ $factura->createdByUser->name }}
                @endif
            </div>
            <div style="margin-top: 15px; font-style: italic;">
                "Gracias por confiar en nuestros servicios médicos"
            </div>
        </div>
    </div>
</body>
</html>
