@php
    $isDark = request()->cookie('theme', 'light') === 'dark';
    $colorPrimario = $disenoId ? $diseno->color_primario : '#1e40af';
    $colorSecundario = $disenoId ? $diseno->color_secundario : '#64748b';
    $colorTexto = $isDark ? '#e5e7eb' : ($disenoId ? $diseno->color_texto : '#1f2937');
    $colorFondo = $isDark ? '#1f2937' : '#ffffff';
    $colorBorde = $isDark ? '#374151' : '#e5e7eb';
@endphp

<div class="max-w-4xl mx-auto">
    <div class="shadow-xl" style="background-color: {{ $colorFondo }}; border: 1px solid {{ $colorBorde }}; border-radius: 0.5rem; padding: 2rem; font-family: {{ $fuente_texto }};">
        <!-- Encabezado -->
        <div class="flex justify-between items-start mb-6">
            @if($mostrar_logo)
            <div class="flex-shrink-0" style="width: {{ $tamaño_logo_ancho }}px;">
                <div class="bg-gray-200 dark:bg-gray-600 rounded-lg h-12 w-full flex items-center justify-center text-xs text-gray-500 dark:text-gray-400">
                    Logo
                </div>
            </div>
            @endif
        
        <div class="text-right">
            @if($mostrar_titulo_factura)
            <h1 class="text-3xl font-bold mb-2" style="font-family: {{ $fuente_titulo }}; color: {{ $colorPrimario }};">
                {{ $texto_titulo_factura }}
            </h1>
            @endif
            
            @if($mostrar_numero_factura)
            <div class="text-base font-semibold mb-1" style="color: {{ $colorSecundario }};">
                No. 001-001-01-00000001
            </div>
            @endif
            
            @if($mostrar_fecha_emision)
            <div class="text-sm" style="color: {{ $colorTexto }};">
                Fecha: {{ now()->format('d/m/Y') }}
            </div>
            @endif
        </div>
    </div>

    <!-- Información Principal -->
    <div class="grid grid-cols-2 gap-8 mb-6">
        <!-- Información del Centro -->
        @if($mostrar_info_centro)
        <div class="border-l-4" style="border-color: {{ $colorPrimario }}; padding-left: 1rem;">
            <div class="font-bold text-lg mb-2" style="color: {{ $colorPrimario }};">CENTRO MÉDICO</div>
            <div class="space-y-1" style="color: {{ $colorTexto }};">
                @if($mostrar_direccion_centro)
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Tegucigalpa</span>
                </div>
                @endif
                @if($mostrar_telefono_centro)
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span>Tel: (504) 2233-4455</span>
                </div>
                @endif
                @if($mostrar_rtn_centro)
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>RTN: 08019999999999</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Información del Paciente -->
        @if($mostrar_info_paciente)
        <div class="border-l-4" style="border-color: {{ $colorPrimario }}; padding-left: 1rem;">
            <div class="font-bold text-lg mb-2" style="color: {{ $colorPrimario }};">PACIENTE</div>
            <div class="space-y-1" style="color: {{ $colorTexto }};">
                <div class="font-medium">Juan Pérez</div>
                @if($mostrar_direccion_paciente)
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    <span>Colonia Ejemplo</span>
                </div>
                @endif
                @if($mostrar_telefono_paciente)
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span>Tel: (504) 9999-9999</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Tabla de Servicios -->
    @if($mostrar_tabla_servicios)
    <div class="mb-6">
        <div class="overflow-hidden rounded-lg border" style="border-color: {{ $colorBorde }};">
            <table class="w-full" style="color: {{ $colorTexto }};">
                <thead>
                    <tr style="background-color: {{ $isDark ? '#374151' : $color_encabezado_tabla }};">
                        @if($mostrar_columna_cantidad)
                        <th class="py-3 px-4 text-left font-semibold">Cant.</th>
                        @endif
                        @if($mostrar_columna_descripcion)
                        <th class="py-3 px-4 text-left font-semibold">Descripción</th>
                        @endif
                        @if($mostrar_columna_precio_unitario)
                        <th class="py-3 px-4 text-right font-semibold">Precio</th>
                        @endif
                        @if($mostrar_columna_total)
                        <th class="py-3 px-4 text-right font-semibold">Total</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <tr style="background-color: {{ $isDark ? '#1f2937' : 'white' }};">
                        @if($mostrar_columna_cantidad)
                        <td class="py-3 px-4 border-t" style="border-color: {{ $colorBorde }};">1</td>
                        @endif
                        @if($mostrar_columna_descripcion)
                        <td class="py-3 px-4 border-t" style="border-color: {{ $colorBorde }};">Consulta Médica General</td>
                        @endif
                        @if($mostrar_columna_precio_unitario)
                        <td class="py-3 px-4 border-t text-right" style="border-color: {{ $colorBorde }};">L. 500.00</td>
                        @endif
                        @if($mostrar_columna_total)
                        <td class="py-3 px-4 border-t text-right" style="border-color: {{ $colorBorde }};">L. 500.00</td>
                        @endif
                    </tr>
                    @if($alternar_color_filas)
                    <tr style="background-color: {{ $isDark ? '#111827' : $color_fila_alterna }};">
                        @if($mostrar_columna_cantidad)
                        <td class="py-3 px-4 border-t" style="border-color: {{ $colorBorde }};">1</td>
                        @endif
                        @if($mostrar_columna_descripcion)
                        <td class="py-3 px-4 border-t" style="border-color: {{ $colorBorde }};">Material Médico</td>
                        @endif
                        @if($mostrar_columna_precio_unitario)
                        <td class="py-3 px-4 border-t text-right" style="border-color: {{ $colorBorde }};">L. 200.00</td>
                        @endif
                        @if($mostrar_columna_total)
                        <td class="py-3 px-4 border-t text-right" style="border-color: {{ $colorBorde }};">L. 200.00</td>
                        @endif
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Totales -->
    <div class="flex justify-{{ $posicion_totales }} mb-6">
        <div class="w-1/2 md:w-1/3">
            <div class="rounded-lg border p-4" style="border-color: {{ $colorBorde }}; background-color: {{ $isDark ? '#111827' : '#f8fafc' }};">
                @if($mostrar_subtotal)
                <div class="flex justify-between items-center py-1" style="color: {{ $colorTexto }};">
                    <span class="font-medium">Subtotal:</span>
                    <span>L. 700.00</span>
                </div>
                @endif
                
                @if($mostrar_impuestos)
                <div class="flex justify-between items-center py-1 border-t" style="color: {{ $colorTexto }}; border-color: {{ $colorBorde }};">
                    <span class="font-medium">ISV (15%):</span>
                    <span>L. 105.00</span>
                </div>
                @endif
                
                @if($mostrar_total)
                <div class="flex justify-between items-center py-2 mt-1 border-t-2" 
                     style="color: {{ $colorPrimario }}; border-color: {{ $colorPrimario }};">
                    <span class="font-bold text-lg">Total:</span>
                    <span class="font-bold text-lg">L. 805.00</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    @if($mostrar_pie_pagina)
    <div class="text-center mt-8 mb-2">
        <div class="inline-block mx-auto px-8 py-2 border-t-2" style="color: {{ $colorSecundario }}; border-color: {{ $colorBorde }};">
            {{ $texto_pie_pagina }}
        </div>
    </div>
    @endif
</div>
</div>
