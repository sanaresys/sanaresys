<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recetas de Consulta - {{ $recetas->first()->paciente->persona->nombre_completo }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .receta-page { page-break-after: always; }
            .receta-page:last-child { page-break-after: auto; }
        }

        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }

        .print-header {
            text-align: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .consulta-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
            margin-bottom: 20px;
        }

        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-print {
            background-color: #3b82f6;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin: 0 10px;
        }

        .btn-print:hover {
            background-color: #2563eb;
        }

        .btn-secondary {
            background-color: #6b7280;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .receta-container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto 30px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .receta-number {
            background: #dbeafe;
            color: #1e40af;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="print-header no-print">
        <h1 style="margin: 0; color: #1f2937;">📋 Recetas de Consulta</h1>
        <div class="consulta-info">
            <h3 style="margin: 0 0 10px 0; color: #374151;">Información de la Consulta</h3>
            <p style="margin: 5px 0;"><strong>Paciente:</strong> {{ $recetas->first()->paciente->persona->nombre_completo }}</p>
            <p style="margin: 5px 0;"><strong>Médico:</strong> Dr. {{ $recetas->first()->medico->persona->nombre_completo }}</p>
            <p style="margin: 5px 0;"><strong>Fecha de Consulta:</strong> {{ $consulta->created_at->format('d/m/Y H:i') }}</p>
            <p style="margin: 5px 0;"><strong>Total de Recetas:</strong> {{ $recetas->count() }}</p>
        </div>

        <div class="print-button">
            <button class="btn-print" onclick="window.print()">🖨️ Imprimir Todas las Recetas</button>
            <button class="btn-print btn-secondary" onclick="window.history.back()">⬅️ Volver</button>
        </div>
    </div>

    @foreach($recetas as $index => $receta)
        <div class="receta-container receta-page">
            <div class="receta-number">
                Receta {{ $index + 1 }} de {{ $recetas->count() }}
                @if($receta->fecha_receta)
                    - {{ \Carbon\Carbon::parse($receta->fecha_receta)->format('d/m/Y H:i') }}
                @endif
            </div>

            @include('components.recetario-preview', [
                'medico' => $receta->medico,
                'receta' => $receta
            ])

            <!-- Pie de página con información adicional -->
            <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 15px;">
                <p style="margin: 0;">Receta {{ $index + 1 }} generada el {{ now()->format('d/m/Y \a \l\a\s H:i') }}</p>
                <p style="margin: 5px 0 0 0;">Dr. {{ $receta->medico->persona->nombre_completo }}</p>
                @if($receta->diagnostico)
                    <p style="margin: 5px 0 0 0; font-style: italic;">Diagnóstico: {{ $receta->diagnostico }}</p>
                @endif
            </div>
        </div>
    @endforeach

    <script>
        // Auto-focus para impresión
        window.onload = function() {
            document.querySelector('.btn-print').focus();
        }

        // Agregar información de página en modo impresión
        window.addEventListener('beforeprint', function() {
            document.title = 'Recetas_{{ $recetas->first()->paciente->persona->primer_nombre }}_{{ $recetas->first()->paciente->persona->primer_apellido }}_{{ now()->format("d-m-Y") }}';
        });
    </script>
</body>
</html>
