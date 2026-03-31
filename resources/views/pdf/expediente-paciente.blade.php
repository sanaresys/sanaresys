<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tipo === 'resumen' ? 'Resumen Clinico' : 'Expediente Clinico' }} {{ $numeroExpediente }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
            background: #ffffff;
        }
        .page {
            padding: 24px 26px;
        }
        .header-wrap {
            border: 1px solid #d8e3eb;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 14px;
        }
        .header-top {
            background: #0f766e;
            color: #ffffff;
            padding: 12px 14px;
        }
        .header-top table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-title {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
        }
        .header-subtitle {
            font-size: 11px;
            opacity: 0.92;
            margin-top: 2px;
        }
        .header-badge {
            display: inline-block;
            background: #0b5f59;
            border: 1px solid #74d2c8;
            border-radius: 999px;
            font-size: 10px;
            font-weight: bold;
            padding: 4px 10px;
        }
        .header-meta {
            background: #f0f7f6;
            padding: 10px 14px;
            border-top: 1px solid #d8e3eb;
            font-size: 10.5px;
            color: #334155;
        }
        .header-meta table {
            width: 100%;
            border-collapse: collapse;
        }

        .section {
            border: 1px solid #dbe3ea;
            border-radius: 10px;
            margin-top: 12px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .section-head {
            background: #f8fafc;
            border-bottom: 1px solid #dbe3ea;
            padding: 9px 12px;
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
        }
        .section-body {
            padding: 11px 12px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .data-table td {
            vertical-align: top;
            padding: 5px 6px;
            border-bottom: 1px dashed #e5e7eb;
            word-wrap: break-word;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .label {
            display: block;
            font-size: 10px;
            color: #64748b;
            margin-bottom: 1px;
        }
        .value {
            display: block;
            font-size: 11px;
            color: #0f172a;
            font-weight: bold;
        }

        .stats-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .stats-grid td {
            width: 33.33%;
            padding: 8px;
            border-right: 1px solid #e5e7eb;
            text-align: center;
        }
        .stats-grid td:last-child {
            border-right: none;
        }
        .stat-number {
            font-size: 16px;
            font-weight: bold;
            color: #0f766e;
            margin: 2px 0;
        }
        .stat-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .chips { margin: -2px; }
        .chip {
            display: inline-block;
            margin: 2px;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: bold;
            border: 1px solid;
        }
        .chip-danger { background: #fff1f2; color: #be123c; border-color: #fecdd3; }
        .chip-success { background: #ecfdf3; color: #166534; border-color: #bbf7d0; }
        .chip-info { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }

        .consulta-card {
            border: 1px solid #dbe3ea;
            border-radius: 8px;
            margin-bottom: 10px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .consulta-head {
            background: #f8fafc;
            border-bottom: 1px solid #dbe3ea;
            padding: 8px 10px;
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
        }
        .consulta-body {
            padding: 9px 10px;
        }
        .consulta-line {
            margin-bottom: 5px;
        }
        .consulta-line:last-child {
            margin-bottom: 0;
        }
        .muted { color: #64748b; }

        .page-break {
            page-break-before: always;
        }
        .footer {
            margin-top: 14px;
            border-top: 1px solid #dbe3ea;
            padding-top: 7px;
            font-size: 10px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header-wrap">
            <div class="header-top">
                <table>
                    <tr>
                        <td style="width:72%; vertical-align:top;">
                            <div class="header-title">{{ $tipo === 'resumen' ? 'Resumen Clinico del Paciente' : 'Expediente Clinico Completo' }}</div>
                            <div class="header-subtitle">{{ $centro?->nombre_centro ?? 'Centro Medico' }}</div>
                            <div class="header-subtitle">
                                {{ $centro?->direccion ?? 'Direccion no configurada' }}
                                @if($centro?->telefono)
                                    | Tel: {{ $centro->telefono }}
                                @endif
                            </div>
                        </td>
                        <td style="width:28%; text-align:right; vertical-align:top;">
                            <span class="header-badge">{{ strtoupper($tipo) }}</span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="header-meta">
                <table>
                    <tr>
                        <td><strong>No. Expediente:</strong> {{ $numeroExpediente }}</td>
                        <td style="text-align:right;"><strong>Fecha emision:</strong> {{ $fechaEmision->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Identificacion del Paciente</div>
            <div class="section-body">
                <table class="data-table">
                    <tr>
                        <td style="width:50%;">
                            <span class="label">Nombre completo</span>
                            <span class="value">{{ trim(($paciente->persona->primer_nombre ?? '') . ' ' . ($paciente->persona->segundo_nombre ?? '') . ' ' . ($paciente->persona->primer_apellido ?? '') . ' ' . ($paciente->persona->segundo_apellido ?? '')) }}</span>
                        </td>
                        <td style="width:50%;">
                            <span class="label">DNI</span>
                            <span class="value">{{ $paciente->persona->dni ?? 'N/D' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="label">Fecha de nacimiento</span>
                            <span class="value">{{ $paciente->persona->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->persona->fecha_nacimiento)->format('d/m/Y') : 'N/D' }}</span>
                        </td>
                        <td>
                            <span class="label">Edad</span>
                            <span class="value">{{ $edad !== null ? $edad . ' anios' : 'N/D' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="label">Sexo</span>
                            <span class="value">{{ ($paciente->persona->sexo ?? null) === 'M' ? 'Masculino' : (($paciente->persona->sexo ?? null) === 'F' ? 'Femenino' : 'N/D') }}</span>
                        </td>
                        <td>
                            <span class="label">Grupo sanguineo</span>
                            <span class="value">{{ $paciente->grupo_sanguineo ?? 'N/D' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="label">Telefono</span>
                            <span class="value">{{ $paciente->persona->telefono ?? 'N/D' }}</span>
                        </td>
                        <td>
                            <span class="label">Contacto de emergencia</span>
                            <span class="value">{{ $paciente->contacto_emergencia ?? 'N/D' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <span class="label">Direccion</span>
                            <span class="value">{{ $paciente->persona->direccion ?? 'N/D' }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Resumen Clinico</div>
            <div class="section-body" style="padding: 0;">
                <table class="stats-grid">
                    <tr>
                        <td>
                            <div class="stat-label">Total consultas</div>
                            <div class="stat-number">{{ $totalConsultas }}</div>
                        </td>
                        <td>
                            <div class="stat-label">Ultima consulta</div>
                            <div class="stat-number" style="font-size: 13px;">{{ $ultimaConsulta?->created_at?->format('d/m/Y') ?? 'Sin consultas' }}</div>
                        </td>
                        <td>
                            <div class="stat-label">Medicos tratantes</div>
                            <div class="stat-number">{{ $medicosDistintos }}</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Enfermedades Cronicas / Antecedentes</div>
            <div class="section-body">
                <div class="chips">
                @if($paciente->enfermedades->isEmpty())
                    <span class="chip chip-success">Sin enfermedades registradas</span>
                @else
                    @foreach($paciente->enfermedades as $enfermedad)
                        <span class="chip chip-danger">{{ $enfermedad->enfermedades ?? 'Enfermedad' }}</span>
                    @endforeach
                @endif
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Medicamentos Activos / Recientes</div>
            <div class="section-body">
                <div class="chips">
                @if($medicamentosActivos->isEmpty())
                    <span class="chip chip-success">Sin medicamentos registrados</span>
                @else
                    @foreach($medicamentosActivos as $medicamento)
                        <span class="chip chip-info">{{ $medicamento }}</span>
                    @endforeach
                @endif
                </div>
            </div>
        </div>

        <div class="section {{ $tipo === 'completo' ? 'page-break' : '' }}">
            <div class="section-head">
                {{ $tipo === 'resumen' ? 'Ultimas Consultas (Resumen)' : 'Evolucion Clinica (Consultas)' }}
            </div>
            <div class="section-body">
                @if($consultas->isEmpty())
                    <p class="muted">No existen consultas registradas para este paciente.</p>
                @else
                    @foreach(($tipo === 'resumen' ? $consultas->take(3) : $consultas) as $consulta)
                        <div class="consulta-card">
                            <div class="consulta-head">
                                Consulta #{{ $consulta->id }} - {{ $consulta->created_at?->format('d/m/Y H:i') ?? 'N/D' }}
                            </div>
                            <div class="consulta-body">
                            <div class="consulta-line"><strong>Medico:</strong> {{ $consulta->medico?->persona?->primer_nombre ?? 'N/D' }} {{ $consulta->medico?->persona?->primer_apellido ?? '' }}</div>
                            <div class="consulta-line"><strong>Diagnostico:</strong> {{ $consulta->diagnostico ?: 'No especificado' }}</div>
                            <div class="consulta-line"><strong>Tratamiento:</strong> {{ $consulta->tratamiento ?: 'No especificado' }}</div>
                            @if($tipo === 'completo')
                                <div class="consulta-line"><strong>Observaciones:</strong> {{ $consulta->observaciones ?: 'Sin observaciones' }}</div>
                                <div class="consulta-line"><strong>Recetas:</strong>
                                    @if($consulta->recetas && $consulta->recetas->count() > 0)
                                        {{ $consulta->recetas->count() }} registradas
                                    @else
                                        Ninguna
                                    @endif
                                </div>
                                <div class="consulta-line"><strong>Examenes:</strong>
                                    @if($consulta->examenes && $consulta->examenes->count() > 0)
                                        {{ $consulta->examenes->count() }} registrados
                                    @else
                                        Ninguno
                                    @endif
                                </div>
                            @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="footer">
            Documento confidencial de uso clinico. Su distribucion no autorizada esta prohibida.
            <br>
            Emitido por {{ $centro?->nombre_centro ?? 'Sistema Clinico' }} - {{ $fechaEmision->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>
