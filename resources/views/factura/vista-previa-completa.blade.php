<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa de Factura - Centro Médico</title>
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <style>
        body {
            font-family: {{ $diseno->fuente_texto ?? 'Arial' }}, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f3f4f6;
            color: {{ $diseno->color_texto ?? '#1f2937' }};
        }
        
        .preview-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .header-actions {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: {{ $diseno->color_primario ?? '#1e40af' }};
            color: white;
        }
        
        .btn-secondary {
            background-color: {{ $diseno->color_secundario ?? '#64748b' }};
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .factura-container {
            background: white;
            border: 1px solid {{ $diseno->color_borde ?? '#e5e7eb' }};
            border-radius: 8px;
            overflow: hidden;
        }
        
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            
            .header-actions {
                display: none;
            }
            
            .preview-container {
                box-shadow: none;
                padding: 0;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <div class="header-actions">
            <h1 style="color: {{ $diseno->color_titulo ?? '#1f2937' }}; font-family: {{ $diseno->fuente_titulo ?? 'Arial Black' }}, sans-serif; margin-bottom: 20px;">
                Vista Previa Completa - Diseño de Factura
            </h1>
            
            <a href="javascript:history.back()" class="btn btn-secondary">
                ← Volver al Diseño
            </a>
            
            <a href="javascript:window.print()" class="btn btn-primary">
                🖨️ Imprimir Vista Previa
            </a>
            
            @if($diseno && $diseno->id)
                <a href="/facturas/preview/{{ $diseno->id }}/pdf" class="btn btn-primary" target="_blank">
                    📄 Ver como PDF
                </a>
            @endif
        </div>
        
        <div class="factura-container">
            @livewire('factura-vista-previa', [
                'disenoId' => $diseno->id ?? null
            ])
        </div>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts

    <script>
        // Auto-actualizar cada 5 segundos si viene de la página de diseño
        if (document.referrer.includes('/admin/diseno-factura')) {
            setInterval(() => {
                window.location.reload();
            }, 5000);
        }
    </script>
</body>
</html>
