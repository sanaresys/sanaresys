<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Examen - 
        @if($examen)
            {{ $examen->consulta->paciente->persona->nombre_completo ?? 'Paciente' }}
        @elseif(isset($medico) && isset($examenesLista) && count($examenesLista) > 0)
            {{ $medico->persona->nombre_completo ?? 'Exámenes' }}
        @else
            Orden de Examen
        @endif
    </title>
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
    </style>
</head>
<body>
    <div class="print-button no-print">
        <button class="btn-print" onclick="window.print()">
            <span style="font-size: 1.2em;">🖨️</span> 
            @if($examen)
                Imprimir Orden
            @else
                Imprimir Órdenes
            @endif
        </button>
        <button class="btn-print btn-secondary" onclick="window.history.back()">
            <span style="font-size: 1.2em;">⬅️</span> Volver
        </button>
    </div>
    
    <div class="examen-container">
        @if($examen)
            @include('components.examen-preview', [
                'medico' => $examen->medico,
                'examen' => $examen
            ])
        @elseif(isset($medico) && isset($examenesLista))
            @include('components.examen-preview', [
                'medico' => $medico,
                'examenesLista' => $examenesLista,
                'examen' => null,
                'consulta' => $consulta ?? null
            ])
        @endif
        
        <!-- Pie de página con información adicional -->
        <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 15px;">
            <p style="margin: 0;">Orden generada el {{ now()->format('d/m/Y \a \l\a\s H:i') }}</p>
            @if($examen && $examen->medico && $examen->medico->persona)
                <p style="margin: 5px 0 0 0;">Dr(a). {{ $examen->medico->persona->nombre_completo }}</p>
            @elseif(isset($medico) && $medico->persona)
                <p style="margin: 5px 0 0 0;">Dr(a). {{ $medico->persona->nombre_completo }}</p>
            @endif
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
