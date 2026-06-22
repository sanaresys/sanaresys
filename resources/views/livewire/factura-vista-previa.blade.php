<div class="max-w-4xl mx-auto">
    <div wire:loading.class="loading-preview" class="factura-preview bg-white print:bg-white dark:print:bg-white p-8 border border-gray-200 rounded-lg relative shadow-lg">
        <!-- Indicador de carga -->
        <div wire:loading class="absolute inset-0 bg-white bg-opacity-90 flex items-center justify-center z-10 rounded-lg">
            <div class="text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
                <p class="text-sm text-gray-600">Actualizando datos...</p>
            </div>
        </div>

        <!-- Contenido de la factura -->
        <div class="factura-content print:bg-white" style="background-color: white !important; color: black !important;">
        @if($diseno)
            <!-- Header de la Factura -->
            <div class="factura-header mb-6 pb-4 border-b-2" 
                 style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}">
                <div class="flex justify-between items-start">
                    <!-- Logo y datos del centro -->
                    <div class="flex-1">
                        @if(($diseno->mostrar_logo ?? true) && ($diseno->logo_url ?? false))
                            <img src="{{ $diseno->logo_url ?? '/images/default-logo.png' }}" 
                                 alt="Logo" 
                                 class="h-16 w-auto mb-4">
                        @endif
                        <div style="color: {{ $diseno->color_texto_primario ?? '#1f2937' }}" class="text-gray-800 dark:text-gray-200">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white" 
                                style="color: {{ $diseno->color_titulo ?? '#1f2937' }}; font-family: {{ $diseno->fuente_titulo ?? 'Arial' }}">
                                {{ $datosFactura['centro']['nombre'] }}
                            </h1>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $datosFactura['centro']['direccion'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $datosFactura['centro']['telefono'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $datosFactura['centro']['rtn'] }}</p>
                            @if($diseno->mostrar_email ?? true)
                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $datosFactura['centro']['email'] }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Datos de la factura -->
                    <div class="text-right">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg" 
                             style="background-color: {{ $diseno->color_fondo_secundario ?? '#f9fafb' }}">
                            <h2 class="text-xl font-bold mb-2 text-gray-900 dark:text-white" 
                                style="color: {{ $diseno->color_titulo ?? '#1f2937' }}">
                                FACTURA
                            </h2>
                            <p class="text-gray-700 dark:text-gray-300"><strong>No:</strong> {{ $datosFactura['numero_factura'] }}</p>
                            <p class="text-gray-700 dark:text-gray-300"><strong>Fecha:</strong> {{ $datosFactura['fecha_emision'] }}</p>
                            <p class="text-gray-700 dark:text-gray-300"><strong>Estado:</strong> 
                                <span class="px-2 py-1 rounded text-sm font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                                    {{ $datosFactura['estado'] }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información CAI -->
            @if(($diseno->mostrar_cai ?? true) && ($datosFactura['cai'] ?? false))
                <div class="cai-info mb-6 p-3 rounded-lg bg-gray-50 dark:bg-gray-700" 
                     style="background-color: {{ $diseno->color_fondo_secundario ?? '#f9fafb' }}">
                    <h3 class="font-semibold mb-2 text-gray-900 dark:text-white" style="color: {{ $diseno->color_titulo ?? '#1f2937' }}">
                        Información CAI
                    </h3>
                    <div class="grid grid-cols-2 gap-4 text-sm text-gray-700 dark:text-gray-300">
                        <div>
                            <p><strong>CAI:</strong> {{ $datosFactura['cai']['numero'] }}</p>
                            <p><strong>Rango:</strong> {{ $datosFactura['cai']['rango_inicial'] }} - {{ $datosFactura['cai']['rango_final'] }}</p>
                        </div>
                        <div>
                            <p><strong>Fecha Límite:</strong> {{ $datosFactura['cai']['fecha_limite'] }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Información del Paciente y Médico -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Datos del Paciente -->
                <div class="paciente-info">
                    <h3 class="font-semibold mb-3 pb-2 border-b text-gray-900 dark:text-white border-gray-200 dark:border-gray-600" 
                        style="color: {{ $diseno->color_titulo ?? '#1f2937' }}; border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}">
                        DATOS DEL PACIENTE
                    </h3>
                    <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300" style="color: {{ $diseno->color_texto_primario ?? '#374151' }}">
                        <p><strong>Nombre:</strong> {{ $datosFactura['paciente']['nombre'] }}</p>
                        <p><strong>Identidad:</strong> {{ $datosFactura['paciente']['identidad'] }}</p>
                        <p><strong>Teléfono:</strong> {{ $datosFactura['paciente']['telefono'] }}</p>
                        <p><strong>Dirección:</strong> {{ $datosFactura['paciente']['direccion'] }}</p>
                    </div>
                </div>

                <!-- Datos del Médico -->
                @if($diseno->mostrar_medico ?? true)
                    <div class="medico-info">
                        <h3 class="font-semibold mb-3 pb-2 border-b text-gray-900 dark:text-white border-gray-200 dark:border-gray-600" 
                            style="color: {{ $diseno->color_titulo ?? '#1f2937' }}; border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}">
                            MÉDICO TRATANTE
                        </h3>
                        <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300" style="color: {{ $diseno->color_texto_primario ?? '#374151' }}">
                            <p><strong>Nombre:</strong> {{ $datosFactura['medico']['nombre'] }}</p>
                            <p><strong>Especialidad:</strong> {{ $datosFactura['medico']['especialidad'] }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Tabla de Servicios -->
            <div class="servicios-tabla mb-6">
                <h3 class="font-semibold mb-3 text-gray-900 dark:text-white" style="color: {{ $diseno->color_titulo ?? '#1f2937' }}">
                    SERVICIOS FACTURADOS
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-200 dark:border-gray-600" 
                           style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700" style="background-color: {{ $diseno->color_fondo_tabla ?? '#f9fafb' }}">
                                <th class="border border-gray-200 dark:border-gray-600 p-3 text-left text-gray-900 dark:text-white" 
                                    style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; color: {{ $diseno->color_titulo ?? '#1f2937' }}">
                                    Descripción
                                </th>
                                <th class="border border-gray-200 dark:border-gray-600 p-3 text-center text-gray-900 dark:text-white" 
                                    style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; color: {{ $diseno->color_titulo ?? '#1f2937' }}">
                                    Cant.
                                </th>
                                <th class="border border-gray-200 dark:border-gray-600 p-3 text-right text-gray-900 dark:text-white" 
                                    style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; color: {{ $diseno->color_titulo ?? '#1f2937' }}">
                                    Precio Unit.
                                </th>
                                <th class="border border-gray-200 dark:border-gray-600 p-3 text-right text-gray-900 dark:text-white" 
                                    style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; color: {{ $diseno->color_titulo ?? '#1f2937' }}">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($datosFactura['servicios'] as $servicio)
                                <tr class="bg-white dark:bg-gray-800">
                                    <td class="border border-gray-200 dark:border-gray-600 p-3 text-gray-700 dark:text-gray-300" 
                                        style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; color: {{ $diseno->color_texto_primario ?? '#374151' }}">
                                        {{ $servicio['descripcion'] }}
                                    </td>
                                    <td class="border border-gray-200 dark:border-gray-600 p-3 text-center text-gray-700 dark:text-gray-300" 
                                        style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; color: {{ $diseno->color_texto_primario ?? '#374151' }}">
                                        {{ $servicio['cantidad'] }}
                                    </td>
                                    <td class="border border-gray-200 dark:border-gray-600 p-3 text-right text-gray-700 dark:text-gray-300" 
                                        style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; color: {{ $diseno->color_texto_primario ?? '#374151' }}">
                                        L. {{ number_format($servicio['precio_unitario'], 2) }}
                                    </td>
                                    <td class="border border-gray-200 dark:border-gray-600 p-3 text-right font-semibold text-gray-700 dark:text-gray-300" 
                                        style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; color: {{ $diseno->color_texto_primario ?? '#374151' }}">
                                        L. {{ number_format($servicio['total'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totales -->
            <div class="totales-section">
                <div class="flex justify-end">
                    <div class="w-full max-w-sm">
                        <div class="space-y-2 p-4 rounded-lg bg-gray-50 dark:bg-gray-700" 
                             style="background-color: {{ $diseno->color_fondo_secundario ?? '#f9fafb' }}">
                            @if($diseno->mostrar_subtotal)
                                <div class="flex justify-between">
                                    <span class="text-gray-700 dark:text-gray-300" style="color: {{ $diseno->color_texto_primario ?? '#374151' }}">Subtotal:</span>
                                    <span class="text-gray-700 dark:text-gray-300" style="color: {{ $diseno->color_texto_primario ?? '#374151' }}">L. {{ number_format($datosFactura['subtotal'], 2) }}</span>
                                </div>
                            @endif
                            
                            @if($diseno->mostrar_descuentos && $datosFactura['descuento_total'] > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-700 dark:text-gray-300" style="color: {{ $diseno->color_texto_primario ?? '#374151' }}">Descuento:</span>
                                    <span class="text-gray-700 dark:text-gray-300" style="color: {{ $diseno->color_texto_primario ?? '#374151' }}">-L. {{ number_format($datosFactura['descuento_total'], 2) }}</span>
                                </div>
                            @endif
                            
                            @if($diseno->mostrar_impuestos)
                                <div class="flex justify-between">
                                    <span class="text-gray-700 dark:text-gray-300" style="color: {{ $diseno->color_texto_primario ?? '#374151' }}">ISV (15%):</span>
                                    <span class="text-gray-700 dark:text-gray-300" style="color: {{ $diseno->color_texto_primario ?? '#374151' }}">L. {{ number_format($datosFactura['impuesto_total'], 2) }}</span>
                                </div>
                            @endif
                            
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-2" style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}">
                                <div class="flex justify-between text-lg font-bold">
                                    <span class="text-gray-900 dark:text-white" style="color: {{ $diseno->color_titulo ?? '#1f2937' }}">TOTAL:</span>
                                    <span class="text-blue-600 dark:text-blue-400" style="color: {{ $diseno->color_acento ?? '#2563eb' }}">L. {{ number_format($datosFactura['total'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado de Pagos -->
            @if($diseno->mostrar_historial_pagos && count($datosFactura['historial_pagos']) > 0)
                <div class="pagos-section mt-6">
                    <h3 class="font-semibold mb-3 text-gray-900 dark:text-white" style="color: {{ $diseno->color_titulo ?? '#1f2937' }}">
                        HISTORIAL DE PAGOS
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <table class="w-full border-collapse border border-gray-200 dark:border-gray-600 text-sm" 
                                   style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700" style="background-color: {{ $diseno->color_fondo_tabla ?? '#f9fafb' }}">
                                        <th class="border border-gray-200 dark:border-gray-600 p-2 text-left text-gray-900 dark:text-white" 
                                            style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}">Fecha</th>
                                        <th class="border border-gray-200 dark:border-gray-600 p-2 text-left text-gray-900 dark:text-white" 
                                            style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}">Método</th>
                                        <th class="border border-gray-200 dark:border-gray-600 p-2 text-right text-gray-900 dark:text-white" 
                                            style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datosFactura['historial_pagos'] as $pago)
                                        <tr class="bg-white dark:bg-gray-800">
                                            <td class="border border-gray-200 dark:border-gray-600 p-2 text-gray-700 dark:text-gray-300" 
                                                style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}">{{ $pago['fecha'] }}</td>
                                            <td class="border border-gray-200 dark:border-gray-600 p-2 text-gray-700 dark:text-gray-300" 
                                                style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}">{{ $pago['estado'] }}</td>
                                            <td class="border border-gray-200 dark:border-gray-600 p-2 text-right text-gray-700 dark:text-gray-300" 
                                                style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}">L. {{ number_format($pago['monto'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700" 
                             style="background-color: {{ $diseno->color_fondo_secundario ?? '#f9fafb' }}">
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-700 dark:text-gray-300" style="color: {{ $diseno->color_texto_primario ?? '#374151' }}">Total Pagado:</span>
                                    <span class="font-semibold text-green-600 dark:text-green-400">L. {{ number_format($datosFactura['total_pagado'], 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-700 dark:text-gray-300" style="color: {{ $diseno->color_texto_primario ?? '#374151' }}">Saldo Pendiente:</span>
                                    <span class="font-semibold text-red-600 dark:text-red-400">L. {{ number_format($datosFactura['saldo_pendiente'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Footer -->
            @if($diseno->mostrar_pie_pagina && $diseno->texto_pie_pagina)
                <div class="footer-section mt-8 pt-4 border-t border-gray-200 dark:border-gray-600 text-center text-sm" 
                     style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; color: {{ $diseno->color_texto_secundario ?? '#6b7280' }}" 
                     class="text-gray-500 dark:text-gray-400">
                    <p>{{ $diseno->texto_pie_pagina }}</p>
                </div>
            @endif
        @else
            <!-- Vista cuando no hay diseño -->
            <div class="text-center py-8">
                <div class="text-gray-400 dark:text-gray-500 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400">Selecciona o configura un diseño para ver la vista previa</p>
            </div>
        @endif
    </div>
</div>
