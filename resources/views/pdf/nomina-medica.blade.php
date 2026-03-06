<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tituloNomina }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 10px;
            line-height: 1.2;
            color: #000000;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000000;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 3px;
            color: #000000;
        }
        .company-info {
            font-size: 10px;
            color: #333333;
            margin-bottom: 5px;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
            color: #000000;
            text-align: center;
        }
        .info-section {
            margin-bottom: 15px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 1px solid #000000;
        }
        .info-table td {
            padding: 6px 8px;
            border: 1px solid #000000;
            font-size: 9px;
            vertical-align: middle;
        }
        .info-table .label {
            background-color: #f0f0f0;
            color: #000000;
            font-weight: bold;
            width: 25%;
            text-align: center;
        }
        .info-table .value {
            background-color: #ffffff;
            color: #000000;
        }
        .employees-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border: 1px solid #000000;
        }
        .employees-table th,
        .employees-table td {
            border: 1px solid #000000;
            padding: 6px 4px;
            font-size: 9px;
            vertical-align: middle;
        }
        .employees-table th {
            background-color: #e0e0e0;
            color: #000000;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }
        .employees-table .number-cell {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: 500;
        }
        .employees-table .name-cell {
            font-weight: 600;
            color: #1f2937;
        }
        .employees-table tbody tr:nth-child(even) {
            background-color: #f7fafc;
        }
        .employees-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .number-cell {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: 500;
        }
        .name-cell {
            font-weight: 600;
            color: #2d3748;
            text-align: left;
        }
        .currency {
            color: #38a169;
            font-weight: 600;
        }
        .signatures {
            margin-top: 50px;
            margin-bottom: 20px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-cell {
            width: 33.33%;
            text-align: center;
            padding: 40px 10px 10px 10px;
            border-bottom: 2px solid #4a5568;
            font-size: 10px;
            color: #4a5568;
        }
        .signature-label {
            margin-top: 10px;
            font-weight: 600;
            font-size: 10px;
            color: #2d3748;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
        .status {
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
        }
        .status.cerrada {
            color: #e53e3e;
            background-color: #fed7d7;
            border: 1px solid #feb2b2;
        }
        .status.abierta {
            color: #38a169;
            background-color: #c6f6d5;
            border: 1px solid #9ae6b4;
        }
        .quincenal-badge {
            background-color: #667eea;
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 9px;
            font-weight: 600;
            margin-left: 8px;
        }
        .period-highlight {
            font-weight: 600;
            color: #2c5282;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $nomina->empresa }}</div>
        <div class="company-info">{{ $centroMedico->direccion ?? 'Colonia Palmira, Tegucigalpa' }}</div>
        <div class="company-info">Teléfono: {{ $centroMedico->telefono ?? '2233-4455' }}</div>
        <div class="title">{{ $tituloNomina }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Fecha de generación</td>
                <td class="value">{{ $fechaGeneracion }}</td>
                <td class="label">Período</td>
                <td class="value">{{ $periodo }}</td>
            </tr>
            <tr>
                <td class="label">Tipo de Pago</td>
                <td class="value">
                    {{ ucfirst($nomina->tipo_pago) }}
                    @if($nomina->tipo_pago === 'quincenal' && $nomina->quincena)
                        - {{ $nomina->quincena == 1 ? 'Primera Quincena' : 'Segunda Quincena' }}
                    @endif
                </td>
                <td class="label">Estado</td>
                <td class="value">
                    <span class="status {{ $nomina->cerrada ? 'cerrada' : 'abierta' }}">
                        {{ $nomina->cerrada ? 'Cerrada' : 'Abierta' }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="title">Detalle de Médicos</div>
    
    <table class="employees-table">
        <thead>
            <tr>
                <th style="width: 35%;">Médico</th>
                <th style="width: 18%;">
                    @if($nomina->tipo_pago === 'quincenal')
                        Salario Quincenal
                    @else
                        Salario Mensual
                    @endif
                </th>
                <th style="width: 15%;">Deducciones</th>
                <th style="width: 15%;">Percepciones</th>
                <th style="width: 17%;">Total a Pagar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalles as $detalle)
            <tr>
                <td class="name-cell">{{ $detalle->medico->persona->nombre_completo }}</td>
                <td class="number-cell">L. {{ number_format($detalle->salario_base, 2) }}</td>
                <td class="number-cell">L. {{ number_format($detalle->deducciones, 2) }}</td>
                <td class="number-cell">L. {{ number_format($detalle->percepciones, 2) }}</td>
                <td class="number-cell">L. {{ number_format($detalle->total_pagar, 2) }}</td>
            </tr>
            @if($detalle->percepciones_detalle)
            <tr>
                <td colspan="5" style="background-color: #f0fff4; padding: 6px; font-size: 8px;">
                    <div style="font-weight: bold; margin-bottom: 3px;">Detalles de percepciones:</div>
                    <div style="white-space: pre-wrap; padding-left: 10px;">{{ $detalle->percepciones_detalle }}</div>
                </td>
            </tr>
            @endif
            @if($detalle->deducciones_detalle)
            <tr>
                <td colspan="5" style="background-color: #fff5f5; padding: 6px; font-size: 8px;">
                    <div style="font-weight: bold; margin-bottom: 3px;">Detalles de deducciones:</div>
                    <div style="white-space: pre-wrap; padding-left: 10px;">{{ $detalle->deducciones_detalle }}</div>
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL NÓMINA:</td>
                <td class="number-cell" style="font-weight: bold;">L. {{ number_format($totalNomina, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="signatures">
        <table class="signature-table">
            <tr>
                <td class="signature-cell">
                    <div class="signature-label">Elaborado por</div>
                </td>
                <td class="signature-cell">
                    <div class="signature-label">Revisado por</div>
                </td>
                <td class="signature-cell">
                    <div class="signature-label">Autorizado por</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p><strong>Reporte generado el {{ $fechaGeneracion }}</strong></p>
        <p>Sistema de Gestión de Clínicas Médicas - {{ $nomina->empresa }}</p>
        @if($centroMedico && $centroMedico->rtn)
            <p><strong>RTN:</strong> {{ $centroMedico->rtn }}</p>
        @endif
        @if($nomina->tipo_pago === 'quincenal')
            <p style="font-style: italic; color: #7c3aed;">
                Nómina {{ $nomina->quincena == 1 ? 'de la primera quincena' : 'de la segunda quincena' }} del mes de {{ $mesNombre }}
            </p>
        @endif
    </div>
</body>
</html>
