<div wire:poll.1s="$refresh" class="factura-preview bg-white border shadow-lg p-6 max-w-2xl mx-auto" 
     style="font-family: {{ $fuente_texto }}; font-size: {{ $tamaño_texto }}px; color: {{ $color_texto }};">
    
    <!-- CSS Dinámico -->
    <style>
        .factura-preview {
            --color-primario: {{ $color_primario }};
            --color-secundario: {{ $color_secundario }};
            --color-acento: {{ $color_acento }};
            --color-texto: {{ $color_texto }};
            --fuente-titulo: {{ $fuente_titulo }};
            --fuente-texto: {{ $fuente_texto }};
            --tamaño-titulo: {{ $tamaño_titulo }}px;
            --tamaño-subtitulo: {{ $tamaño_subtitulo }}px;
            --tamaño-texto: {{ $tamaño_texto }}px;
        }
        
        .factura-titulo {
            font-family: var(--fuente-titulo);
            font-size: var(--tamaño-titulo);
            color: var(--color-primario);
            font-weight: bold;
            text-align: center;
        }
        
        .factura-subtitulo {
            font-family: var(--fuente-titulo);
            font-size: var(--tamaño-subtitulo);
            color: var(--color-secundario);
            font-weight: bold;
        }
        
        .tabla-servicios {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .tabla-servicios th {
            background-color: {{ $color_encabezado_tabla }};
            color: var(--color-texto);
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        .tabla-servicios td {
            padding: 8px 10px;
            border: 1px solid #ddd;
        }
        
        @if($alternar_color_filas)
        .tabla-servicios tr:nth-child(even) {
            background-color: {{ $color_fila_alterna }};
        }
        @endif
        
        .total-resaltado {
            background-color: var(--color-acento);
            color: white;
            font-weight: bold;
            padding: 8px;
        }
        
        .cai-info {
            font-size: 10px;
            color: var(--color-secundario);
            @if($posicion_cai === 'superior') 
                margin-bottom: 20px;
            @elseif($posicion_cai === 'inferior')
                margin-top: 20px;
            @endif
        }
        
        .qr-container {
            @if($posicion_qr === 'izquierda')
                float: left;
            @elseif($posicion_qr === 'derecha')
                float: right;
            @else
                text-align: center;
            @endif
        }
        
        {{ $css_personalizado }}
    </style>

    <!-- Encabezado con Logo y Título -->
    <div class="flex justify-between items-start mb-6">
        <!-- Logo del Centro (si está habilitado) -->
        @if($mostrar_logo)
            <div class="logo-container" style="
                @if($posicion_logo === 'centro') margin: 0 auto; @endif
                @if($posicion_logo === 'derecha') margin-left: auto; @endif
                width: {{ $tamaño_logo_ancho }}px; 
                height: {{ $tamaño_logo_alto }}px;
                background: linear-gradient(45deg, {{ $color_primario }}, {{ $color_secundario }});
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
            ">
                LOGO
            </div>
        @endif

        <!-- Información del Centro -->
        @if($mostrar_info_centro)
            <div class="text-left flex-1 @if($mostrar_logo) ml-4 @endif">
                <h2 class="factura-subtitulo">{{ $datosFactura['centro']['nombre'] }}</h2>
                @if($mostrar_direccion_centro)
                    <p class="text-sm">{{ $datosFactura['centro']['direccion'] }}</p>
                @endif
                @if($mostrar_telefono_centro)
                    <p class="text-sm">{{ $datosFactura['centro']['telefono'] }}</p>
                @endif
                @if($mostrar_rtn_centro)
                    <p class="text-sm">{{ $datosFactura['centro']['rtn'] }}</p>
                @endif
            </div>
        @endif

        <!-- Título FACTURA y Número -->
        <div class="text-right">
            @if($mostrar_titulo_factura)
                <h1 class="factura-titulo">{{ $texto_titulo_factura }}</h1>
            @endif
            @if($mostrar_numero_factura)
                <p class="text-sm mt-2">{{ $datosFactura['numero_factura'] }}</p>
            @endif
            @if($mostrar_fecha_emision)
                <p class="text-sm" style="color: {{ $color_secundario }};">{{ $datosFactura['fecha_emision'] }}</p>
            @endif
            <p class="text-sm font-bold" style="color: {{ $color_acento }};">{{ $datosFactura['estado'] }}</p>
        </div>
    </div>

    <!-- Información CAI (Posición Superior) -->
    @if($mostrar_cai && $posicion_cai === 'superior')
        <div class="cai-info border-t border-b py-2 mb-4">
            <p><strong>CAI:</strong> {{ $datosFactura['cai']['numero'] }}</p>
            @if($mostrar_rango_cai)
                <p><strong>Rango Autorizado:</strong> {{ $datosFactura['cai']['rango_inicial'] }} - {{ $datosFactura['cai']['rango_final'] }}</p>
            @endif
            @if($mostrar_fecha_limite_cai)
                <p><strong>Fecha Límite:</strong> {{ $datosFactura['cai']['fecha_limite'] }}</p>
            @endif
        </div>
    @endif

    <!-- Información del Paciente -->
    @if($mostrar_info_paciente)
        <div class="mb-6 p-4" style="background-color: {{ $color_primario }}15; border-left: 4px solid {{ $color_primario }};">
            <h3 class="factura-subtitulo mb-2">{{ $etiqueta_cliente }}</h3>
            <p><strong>{{ $datosFactura['paciente']['nombre'] }}</strong></p>
            @if($mostrar_rtn_paciente)
                <p>Identidad: {{ $datosFactura['paciente']['identidad'] }}</p>
            @endif
            @if($mostrar_telefono_paciente)
                <p>Teléfono: {{ $datosFactura['paciente']['telefono'] }}</p>
            @endif
            @if($mostrar_direccion_paciente)
                <p>Dirección: {{ $datosFactura['paciente']['direccion'] }}</p>
            @endif
        </div>
    @endif

    <!-- Información Médica -->
    <div class="mb-6 p-4" style="background-color: {{ $color_acento }}15; border-left: 4px solid {{ $color_acento }};">
        <h3 class="factura-subtitulo mb-2">INFORMACIÓN MÉDICA</h3>
        <p><strong>Médico:</strong> {{ $datosFactura['medico']['nombre'] }}</p>
        <p><strong>Especialidad:</strong> {{ $datosFactura['medico']['especialidad'] }}</p>
        <p><strong>Tipo Consulta:</strong> Consulta General</p>
    </div>

    <!-- Tabla de Servicios -->
    <table class="tabla-servicios">
        <thead>
            <tr>
                <th>DESCRIPCIÓN DEL SERVICIO</th>
                <th class="text-center">CANTIDAD</th>
                <th class="text-right">PRECIO UNIT.</th>
                <th class="text-right">DESCUENTO</th>
                <th class="text-right">IMPUESTO</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datosFactura['servicios'] as $servicio)
            <tr>
                <td>{{ $servicio['descripcion'] }}</td>
                <td class="text-center">{{ $servicio['cantidad'] }}</td>
                <td class="text-right">L {{ number_format($servicio['precio_unitario'], 2) }}</td>
                <td class="text-right">L -</td>
                <td class="text-right">L -</td>
                <td class="text-right" style="color: {{ $color_acento }}; font-weight: bold;">
                    L {{ number_format($servicio['total'], 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totales -->
    <div class="flex justify-end mb-6">
        <div class="w-64">
            @if($mostrar_subtotal)
            <div class="flex justify-between py-2">
                <span>Subtotal:</span>
                <span>L {{ number_format($datosFactura['subtotal'], 2) }}</span>
            </div>
            @endif
            
            @if($mostrar_descuentos && $datosFactura['descuento_total'] > 0)
            <div class="flex justify-between py-2">
                <span>Descuento Total:</span>
                <span>-L {{ number_format($datosFactura['descuento_total'], 2) }}</span>
            </div>
            @endif
            
            @if($mostrar_impuestos)
            <div class="flex justify-between py-2">
                <span>Impuestos (15%):</span>
                <span>L {{ number_format($datosFactura['impuesto_total'], 2) }}</span>
            </div>
            @endif
            
            @if($mostrar_total)
            <div class="flex justify-between py-3 border-t {{ $resaltar_total ? 'total-resaltado' : '' }}">
                <span class="font-bold">TOTAL:</span>
                <span class="font-bold">L {{ number_format($datosFactura['total'], 2) }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Historial de Pagos -->
    <div class="mb-6 p-4" style="background-color: {{ $color_secundario }}15; border-left: 4px solid {{ $color_secundario }};">
        <h3 class="factura-subtitulo mb-3">HISTORIAL DE PAGOS:</h3>
        @foreach($datosFactura['historial_pagos'] as $pago)
        <p>{{ $pago['fecha'] }}: L {{ number_format($pago['monto'], 2) }} ({{ $pago['estado'] }})</p>
        @endforeach
        
        <div class="mt-3 pt-3 border-t">
            <p><strong>Total Pagado:</strong> L {{ number_format($datosFactura['total_pagado'], 2) }}</p>
            <p style="color: {{ $color_acento }}; font-weight: bold;">
                <strong>Saldo Pendiente:</strong> L {{ number_format($datosFactura['saldo_pendiente'], 2) }}
            </p>
        </div>
    </div>

    <!-- Información CAI (Posición Inferior) -->
    @if($mostrar_cai && $posicion_cai === 'inferior')
        <div class="cai-info border-t py-2 mt-4">
            <p><strong>CAI:</strong> {{ $datosFactura['cai']['numero'] }}</p>
            @if($mostrar_rango_cai)
                <p><strong>Rango Autorizado:</strong> {{ $datosFactura['cai']['rango_inicial'] }} - {{ $datosFactura['cai']['rango_final'] }}</p>
            @endif
            @if($mostrar_fecha_limite_cai)
                <p><strong>Fecha Límite:</strong> {{ $datosFactura['cai']['fecha_limite'] }}</p>
            @endif
        </div>
    @endif

    <!-- Pie de Página -->
    @if($mostrar_pie_pagina)
        <div class="text-center mt-6 pt-4 border-t" style="color: {{ $color_secundario }};">
            <p class="text-sm">{{ $texto_pie_pagina }}</p>
            <p class="text-xs mt-2">Factura generada el {{ now()->format('d/m/Y H:i:s') }}</p>
            <p class="text-xs">Consulte por correo en nuestros servicios médicos</p>
            <p class="text-xs">Válida por cualquier en nuestras instalaciones</p>
            
            <!-- QR de Pago -->
            @if($mostrar_qr_pago)
                <div class="qr-container mt-4">
                    <div style="width: 80px; height: 80px; background: {{ $color_primario }}; margin: 0 auto; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px;">
                        QR PAGO
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
