<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Examen Médico - {{ $paciente->persona->nombre_completo }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 10mm 12mm 10mm;
        }
        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
                margin: 0;
                padding: 0;
                background: white !important;
                box-shadow: none !important;
            }
            .examen-container {
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 0 0 0 !important;
            }
            .no-print { display: none !important; }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        
        .examen-container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .print-button {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .btn-print {
            background-color: #3b82f6;
            color: white;
            padding: 12px 28px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin: 0 8px 8px 0;
            box-shadow: 0 2px 8px #0001;
            transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-print:hover {
            background-color: #2563eb;
            box-shadow: 0 4px 16px #0002;
            transform: translateY(-2px) scale(1.03);
        }
        .btn-secondary {
            background-color: #e5e7eb;
            color: #1e293b;
            border: 1px solid #cbd5e1;
        }
        .btn-secondary:hover {
            background-color: #cbd5e1;
            color: #1e40af;
        }

        /* Estilos para la orden de examen */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .logo-section {
            flex: 1;
        }
        
        .info-section {
            flex: 2;
            text-align: center;
        }
        
        .contact-section {
            flex: 1;
            text-align: right;
            font-size: 12px;
            color: #666;
        }
        
        .centro-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin: 0;
        }
        
        .centro-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin: 5px 0;
        }
        
        .document-title {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
            margin-top: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
        }
        
        .card-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 15px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #374151;
            width: 120px;
            flex-shrink: 0;
        }
        
        .info-value {
            color: #1f2937;
            flex: 1;
        }
        
        .examen-section {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
        }
        
        .examen-title {
            font-size: 18px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .examen-content {
            font-size: 16px;
            color: #1f2937;
            line-height: 1.6;
        }
        
        .observaciones-section {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .firma-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
        }
        
        .firma-box {
            text-align: center;
            padding: 20px 0;
        }
        
        .firma-line {
            border-top: 2px solid #374151;
            margin-bottom: 10px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
        
        .firma-label {
            font-weight: bold;
            color: #374151;
            font-size: 14px;
        }
        
        .estado-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .estado-solicitado {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #f59e0b;
        }
        
        .estado-completado {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        
        .footer-info {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="print-button no-print">
        <button class="btn-print" onclick="window.print()">
            <span style="font-size: 1.2em;">🖨️</span> Imprimir Orden de Examen
        </button>
        <button class="btn-print btn-secondary" onclick="window.history.back()">
            <span style="font-size: 1.2em;">⬅️</span> Volver
        </button>
    </div>
    
    <div class="examen-container">
        <!-- Encabezado -->
        <div class="header">
            <div class="logo-section">
                <!-- Aquí puedes agregar un logo si lo tienes -->
                <div style="width: 80px; height: 80px; background: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">
                    🏥
                </div>
            </div>
            
            <div class="info-section">
                <h1 class="centro-name">{{ $centro->nombre ?? 'Centro Médico' }}</h1>
                <p class="centro-subtitle">{{ $centro->direccion ?? '' }}</p>
                <div class="document-title">🔬 ORDEN DE EXAMEN MÉDICO</div>
            </div>
            
            <div class="contact-section">
                @if($centro->telefono)
                    <p>📞 {{ $centro->telefono }}</p>
                @endif
                <p>📅 {{ now()->format('d/m/Y H:i') }}</p>
                <div class="estado-badge estado-{{ strtolower(str_replace(' ', '-', $examen->estado)) }}">
                    {{ $examen->estado }}
                </div>
            </div>
        </div>

        <!-- Información del paciente y médico -->
        <div class="info-grid">
            <div class="info-card">
                <div class="card-title">👤 Información del Paciente</div>
                <div class="info-row">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value">{{ $paciente->persona->nombre_completo }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">DNI:</span>
                    <span class="info-value">{{ $paciente->persona->dni ?? 'No disponible' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Edad:</span>
                    <span class="info-value">
                        @if($paciente->persona->fecha_nacimiento)
                            {{ \Carbon\Carbon::parse($paciente->persona->fecha_nacimiento)->age }} años
                        @else
                            No disponible
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value">{{ $paciente->persona->telefono ?? 'No disponible' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Sexo:</span>
                    <span class="info-value">{{ $paciente->persona->sexo ?? 'No disponible' }}</span>
                </div>
            </div>

            <div class="info-card">
                <div class="card-title">👨‍⚕️ Información del Médico</div>
                <div class="info-row">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value">Dr(a). {{ $medico->persona->nombre_completo }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Colegiación:</span>
                    <span class="info-value">{{ $medico->numero_colegiacion ?? 'No disponible' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Especialidad:</span>
                    <span class="info-value">{{ $medico->especialidad ?? 'No especificada' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Consulta #:</span>
                    <span class="info-value">{{ $consulta->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha Consulta:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($consulta->created_at)->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Examen solicitado -->
        <div class="examen-section">
            <div class="examen-title">🔬 EXAMEN SOLICITADO</div>
            <div class="examen-content">
                <strong>{{ $examen->tipo_examen }}</strong>
            </div>
        </div>

        <!-- Observaciones -->
        @if($examen->observaciones)
        <div class="observaciones-section">
            <div class="card-title">📝 Observaciones e Indicaciones</div>
            <div style="line-height: 1.6; color: #1f2937;">
                {{ $examen->observaciones }}
            </div>
        </div>
        @endif

        <!-- Información de fechas -->
        <div class="info-grid" style="margin-top: 30px;">
            <div class="info-card">
                <div class="card-title">📅 Información de Fechas</div>
                <div class="info-row">
                    <span class="info-label">Solicitado:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($examen->created_at)->format('d/m/Y H:i') }}</span>
                </div>
                @if($examen->fecha_completado)
                <div class="info-row">
                    <span class="info-label">Completado:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($examen->fecha_completado)->format('d/m/Y H:i') }}</span>
                </div>
                @endif
            </div>

            <div class="info-card">
                <div class="card-title">ℹ️ Información Adicional</div>
                <div class="info-row">
                    <span class="info-label">Estado:</span>
                    <span class="info-value">
                        <span class="estado-badge estado-{{ strtolower(str_replace(' ', '-', $examen->estado)) }}">
                            {{ $examen->estado }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Examen ID:</span>
                    <span class="info-value">#{{ $examen->id }}</span>
                </div>
            </div>
        </div>

        <!-- Área de firmas -->
        <div class="firma-section">
            <div class="firma-box">
                <div class="firma-line"></div>
                <div class="firma-label">Firma del Médico</div>
                <div style="font-size: 12px; margin-top: 5px; color: #6b7280;">
                    Dr(a). {{ $medico->persona->nombre_completo }}<br>
                    {{ $medico->numero_colegiacion ? 'Col: ' . $medico->numero_colegiacion : '' }}
                </div>
            </div>
            
            <div class="firma-box">
                <div class="firma-line"></div>
                <div class="firma-label">Sello del Centro Médico</div>
                <div style="font-size: 12px; margin-top: 5px; color: #6b7280;">
                    {{ $centro->nombre ?? 'Centro Médico' }}
                </div>
            </div>
        </div>

        <!-- Pie de página -->
        <div class="footer-info">
            <p>Este documento es válido únicamente con la firma del médico</p>
            <p>Orden de examen generada el {{ now()->format('d/m/Y \a \l\a\s H:i') }}</p>
            <p>Dr(a). {{ $medico->persona->nombre_completo }} - {{ $centro->nombre ?? 'Centro Médico' }}</p>
        </div>
    </div>

    <script>
        // Auto-focus para impresión
        window.onload = function() {
            document.querySelector('.btn-print').focus();
        }
    </script>
</body>
</html>
