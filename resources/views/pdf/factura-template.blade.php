<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura</title>
    <style>
        @page {
            margin: {{ $diseno->margenes['top'] ?? 20 }}mm {{ $diseno->margenes['right'] ?? 15 }}mm {{ $diseno->margenes['bottom'] ?? 20 }}mm {{ $diseno->margenes['left'] ?? 15 }}mm;
        }
        
        body {
            font-family: "{{ $diseno->fuente_texto ?? 'Arial' }}", sans-serif;
            font-size: {{ $diseno->tamaño_texto ?? 12 }}px;
            color: {{ $diseno->color_texto ?? '#1f2937' }};
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        
        .factura-titulo {
            font-family: "{{ $diseno->fuente_titulo ?? 'Arial Black' }}", sans-serif;
            font-size: {{ $diseno->tamaño_titulo ?? 18 }}px;
            color: {{ $diseno->color_primario ?? '#1e40af' }};
            font-weight: bold;
            text-align: center;
            margin: 0;
        }
        
        .factura-subtitulo {
            font-family: "{{ $diseno->fuente_titulo ?? 'Arial Black' }}", sans-serif;
            font-size: {{ $diseno->tamaño_subtitulo ?? 14 }}px;
            color: {{ $diseno->color_secundario ?? '#64748b' }};
            font-weight: bold;
            margin: 5px 0;
        }
        
        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .header-left {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }
        
        .header-center {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            text-align: center;
        }
        
        .header-right {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            text-align: right;
        }
        
        .logo-container {
            width: {{ $diseno->tamaño_logo_ancho ?? 120 }}px;
            height: {{ $diseno->tamaño_logo_alto ?? 80 }}px;
            background: linear-gradient(45deg, {{ $diseno->color_primario ?? '#1e40af' }}, {{ $diseno->color_secundario ?? '#64748b' }});
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            @if($diseno->posicion_logo === 'centro')
                margin: 0 auto;
            @elseif($diseno->posicion_logo === 'derecha')
                margin-left: auto;
            @endif
        }
        
        .info-box {
            background-color: {{ $diseno->color_primario ?? '#1e40af' }}15;
            border-left: 4px solid {{ $diseno->color_primario ?? '#1e40af' }};
            padding: 15px;
            margin: 15px 0;
        }
        
        .medico-box {
            background-color: {{ $diseno->color_acento ?? '#059669' }}15;
            border-left: 4px solid {{ $diseno->color_acento ?? '#059669' }};
            padding: 15px;
            margin: 15px 0;
        }
        
        .tabla-servicios {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .tabla-servicios th {
            background-color: {{ $diseno->color_encabezado_tabla ?? '#f3f4f6' }};
            color: {{ $diseno->color_texto ?? '#1f2937' }};
            padding: 12px 8px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        
        .tabla-servicios td {
            padding: 10px 8px;
            border: 1px solid #ddd;
        }
        
        @if($diseno->alternar_color_filas ?? true)
        .tabla-servicios tr:nth-child(even) {
            background-color: {{ $diseno->color_fila_alterna ?? '#f9fafb' }};
        }
        @endif
        
        .totales-container {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
        }
        
        .total-row {
            display: table;
            width: 100%;
            padding: 8px 0;
        }
        
        .total-label {
            display: table-cell;
            text-align: left;
        }
        
        .total-value {
            display: table-cell;
            text-align: right;
        }
        
        .total-final {
            @if($diseno->resaltar_total ?? true)
                background-color: {{ $diseno->color_acento ?? '#059669' }};
                color: white;
                padding: 12px;
                font-weight: bold;
            @else
                border-top: 2px solid {{ $diseno->color_primario ?? '#1e40af' }};
                padding-top: 10px;
                font-weight: bold;
            @endif
        }
        
        .cai-info {
            font-size: 10px;
            color: {{ $diseno->color_secundario ?? '#64748b' }};
            @if($diseno->posicion_cai === 'superior')
                margin-bottom: 20px;
                border-bottom: 1px solid #ddd;
                padding-bottom: 10px;
            @elseif($diseno->posicion_cai === 'inferior')
                margin-top: 20px;
                border-top: 1px solid #ddd;
                padding-top: 10px;
            @endif
        }
        
        .pie-pagina {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: {{ $diseno->color_secundario ?? '#64748b' }};
            font-size: 11px;
        }
        
        .qr-container {
            width: 80px;
            height: 80px;
            background: {{ $diseno->color_primario ?? '#1e40af' }};
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 10px;
            margin: 10px auto;
        }
        
        .historial-pagos {
            background-color: {{ $diseno->color_secundario ?? '#64748b' }}15;
            border-left: 4px solid {{ $diseno->color_secundario ?? '#64748b' }};
            padding: 15px;
            margin: 20px 0;
        }
        
        .saldo-pendiente {
            color: {{ $diseno->color_acento ?? '#059669' }};
            font-weight: bold;
        }
        
        /* CSS Personalizado */
        {{ $diseno->css_personalizado ?? '' }}
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        <div class="header-left">
            @if($diseno->mostrar_logo ?? true)
                <div class="logo-container">LOGO</div>
            @endif
        </div>
        
        <div class="header-center">
            @if($diseno->mostrar_info_centro ?? true)
                <h2 class="factura-subtitulo">{{ $datosFactura['centro']['nombre'] }}</h2>
                @if($diseno->mostrar_direccion_centro ?? true)
                    <p>{{ $datosFactura['centro']['direccion'] }}</p>
                @endif
                @if($diseno->mostrar_telefono_centro ?? true)
                    <p>{{ $datosFactura['centro']['telefono'] }}</p>
                @endif
                @if($diseno->mostrar_rtn_centro ?? true)
                    <p>{{ $datosFactura['centro']['rtn'] }}</p>
                @endif
            @endif
        </div>
        
        <div class="header-right">
            @if($diseno->mostrar_titulo_factura ?? true)
                <h1 class="factura-titulo">{{ $diseno->texto_titulo_factura ?? 'FACTURA' }}</h1>
            @endif
            @if($diseno->mostrar_numero_factura ?? true)
                <p><strong>{{ $datosFactura['numero_factura'] }}</strong></p>
            @endif
            @if($diseno->mostrar_fecha_emision ?? true)
                <p>{{ $datosFactura['fecha_emision'] }}</p>
            @endif
            <p style="color: {{ $diseno->color_acento ?? '#059669' }}; font-weight: bold;">
                {{ $datosFactura['estado'] }}
            </p>
        </div>
    </div>

    <!-- Información CAI Superior -->
    @if(($diseno->mostrar_cai ?? true) && ($diseno->posicion_cai ?? 'superior') === 'superior' && $datosFactura['cai'])
        <div class="cai-info">
            <p><strong>CAI:</strong> {{ $datosFactura['cai']['numero'] }}</p>
            @if($diseno->mostrar_rango_cai ?? true)
                <p><strong>Rango Autorizado:</strong> {{ $datosFactura['cai']['rango_inicial'] }} - {{ $datosFactura['cai']['rango_final'] }}</p>
            @endif
            @if($diseno->mostrar_fecha_limite_cai ?? true)
                <p><strong>Fecha Límite:</strong> {{ $datosFactura['cai']['fecha_limite'] }}</p>
            @endif
        </div>
    @endif

    <!-- Información del Paciente -->
    @if($diseno->mostrar_info_paciente ?? true)
        <div class="info-box">
            <h3 class="factura-subtitulo">{{ $diseno->etiqueta_cliente ?? 'FACTURAR A:' }}</h3>
            <p><strong>{{ $datosFactura['paciente']['nombre'] }}</strong></p>
            @if($diseno->mostrar_rtn_paciente ?? true)
                <p>Identidad: {{ $datosFactura['paciente']['identidad'] }}</p>
            @endif
            @if($diseno->mostrar_telefono_paciente ?? true)
                <p>Teléfono: {{ $datosFactura['paciente']['telefono'] }}</p>
            @endif
            @if($diseno->mostrar_direccion_paciente ?? true)
                <p>Dirección: {{ $datosFactura['paciente']['direccion'] }}</p>
            @endif
        </div>
    @endif

    <!-- Información Médica -->
    <div class="medico-box">
        <h3 class="factura-subtitulo">INFORMACIÓN MÉDICA</h3>
        <p><strong>Médico:</strong> {{ $datosFactura['medico']['nombre'] }}</p>
        <p><strong>Especialidad:</strong> {{ $datosFactura['medico']['especialidad'] }}</p>
        <p><strong>Tipo Consulta:</strong> Consulta General</p>
    </div>

    <!-- Tabla de Servicios -->
    <table class="tabla-servicios">
        <thead>
            <tr>
                <th>DESCRIPCIÓN DEL SERVICIO</th>
                <th style="text-align: center;">CANTIDAD</th>
                <th style="text-align: right;">PRECIO UNIT.</th>
                <th style="text-align: right;">DESCUENTO</th>
                <th style="text-align: right;">IMPUESTO</th>
                <th style="text-align: right;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datosFactura['servicios'] as $servicio)
            <tr>
                <td>{{ $servicio['descripcion'] }}</td>
                <td style="text-align: center;">{{ $servicio['cantidad'] }}</td>
                <td style="text-align: right;">L {{ number_format($servicio['precio_unitario'], 2) }}</td>
                <td style="text-align: right;">L -</td>
                <td style="text-align: right;">L -</td>
                <td style="text-align: right; color: {{ $diseno->color_acento ?? '#059669' }}; font-weight: bold;">
                    L {{ number_format($servicio['total'], 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totales -->
    <div class="totales-container">
        @if($diseno->mostrar_subtotal ?? true)
        <div class="total-row">
            <div class="total-label">Subtotal:</div>
            <div class="total-value">L {{ number_format($datosFactura['subtotal'], 2) }}</div>
        </div>
        @endif
        
        @if(($diseno->mostrar_descuentos ?? true) && $datosFactura['descuento_total'] > 0)
        <div class="total-row">
            <div class="total-label">Descuento Total:</div>
            <div class="total-value">-L {{ number_format($datosFactura['descuento_total'], 2) }}</div>
        </div>
        @endif
        
        @if($diseno->mostrar_impuestos ?? true)
        <div class="total-row">
            <div class="total-label">Impuestos (15%):</div>
            <div class="total-value">L {{ number_format($datosFactura['impuesto_total'], 2) }}</div>
        </div>
        @endif
        
        @if($diseno->mostrar_total ?? true)
        <div class="total-row total-final">
            <div class="total-label"><strong>TOTAL:</strong></div>
            <div class="total-value"><strong>L {{ number_format($datosFactura['total'], 2) }}</strong></div>
        </div>
        @endif
    </div>

    <!-- Historial de Pagos -->
    <div class="historial-pagos">
        <h3 class="factura-subtitulo">HISTORIAL DE PAGOS:</h3>
        @foreach($datosFactura['historial_pagos'] as $pago)
        <p>{{ $pago['fecha'] }}: L {{ number_format($pago['monto'], 2) }} ({{ $pago['estado'] }})</p>
        @endforeach
        
        <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #ddd;">
            <p><strong>Total Pagado:</strong> L {{ number_format($datosFactura['total_pagado'], 2) }}</p>
            <p class="saldo-pendiente">
                <strong>Saldo Pendiente:</strong> L {{ number_format($datosFactura['saldo_pendiente'], 2) }}
            </p>
        </div>
    </div>

    <!-- Información CAI Inferior -->
    @if(($diseno->mostrar_cai ?? true) && ($diseno->posicion_cai ?? 'superior') === 'inferior' && $datosFactura['cai'])
        <div class="cai-info">
            <p><strong>CAI:</strong> {{ $datosFactura['cai']['numero'] }}</p>
            @if($diseno->mostrar_rango_cai ?? true)
                <p><strong>Rango Autorizado:</strong> {{ $datosFactura['cai']['rango_inicial'] }} - {{ $datosFactura['cai']['rango_final'] }}</p>
            @endif
            @if($diseno->mostrar_fecha_limite_cai ?? true)
                <p><strong>Fecha Límite:</strong> {{ $datosFactura['cai']['fecha_limite'] }}</p>
            @endif
        </div>
    @endif

    <!-- Pie de Página -->
    @if($diseno->mostrar_pie_pagina ?? true)
        <div class="pie-pagina">
            @if($diseno->texto_pie_pagina)
                <p>{{ $diseno->texto_pie_pagina }}</p>
            @endif
            <p>Factura generada el {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Consulte por correo en nuestros servicios médicos</p>
            <p>Válida por cualquier en nuestras instalaciones</p>
            
            @if($diseno->mostrar_qr_pago ?? false)
                <div class="qr-container">QR PAGO</div>
            @endif
        </div>
    @endif
</body>
</html>
