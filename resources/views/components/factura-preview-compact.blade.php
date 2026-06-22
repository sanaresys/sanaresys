<div class="w-full">
    <!-- Vista Previa Compacta -->
    <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Vista Previa de Factura
            </h3>
            <div class="flex gap-2">
                <button type="button" 
                        class="px-3 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700 transition-colors"
                        onclick="abrirVistaCompleta()">
                    📋 Ver Completa
                </button>
            </div>
        </div>
        
        <div class="border border-gray-200 dark:border-gray-600 rounded overflow-hidden bg-white">
            <div class="max-h-[400px] overflow-y-auto">
                <!-- Vista previa estática usando los datos del diseño -->
                <div class="factura-preview bg-white p-6" style="transform: scale(0.8); transform-origin: top left; width: 125%;">
                    <!-- Header de la Factura -->
                    <div class="factura-header mb-6 pb-4 border-b-2" style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}">
                        <div class="flex justify-between items-start">
                            <!-- Logo y datos del centro -->
                            <div class="flex-1">
                                @if(($diseno->mostrar_logo ?? true))
                                    <div class="h-16 w-24 bg-gray-200 rounded mb-4 flex items-center justify-center text-xs text-gray-500">
                                        LOGO
                                    </div>
                                @endif
                                <div style="color: {{ $diseno->color_texto ?? '#1f2937' }}">
                                    <h1 class="text-2xl font-bold" 
                                        style="color: {{ $diseno->color_titulo ?? '#1f2937' }}; font-family: {{ $diseno->fuente_titulo ?? 'Arial' }}; font-size: {{ $diseno->tamaño_titulo ?? 18 }}px;">
                                        Centro Médico Demo
                                    </h1>
                                    @if($diseno->mostrar_direccion_centro ?? true)
                                        <p class="text-sm" style="font-size: {{ $diseno->tamaño_texto ?? 12 }}px;">Dirección del Centro</p>
                                    @endif
                                    @if($diseno->mostrar_telefono_centro ?? true)
                                        <p class="text-sm" style="font-size: {{ $diseno->tamaño_texto ?? 12 }}px;">Teléfono: +504 1234-5678</p>
                                    @endif
                                    @if($diseno->mostrar_rtn_centro ?? true)
                                        <p class="text-sm" style="font-size: {{ $diseno->tamaño_texto ?? 12 }}px;">RTN: 12345678901234</p>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Datos de la factura -->
                            <div class="text-right">
                                <div class="bg-gray-50 p-4 rounded-lg" style="background-color: {{ $diseno->color_fondo_secundario ?? '#f9fafb' }}">
                                    @if($diseno->mostrar_titulo_factura ?? true)
                                        <h2 class="text-xl font-bold mb-2" 
                                            style="color: {{ $diseno->color_titulo ?? '#1f2937' }}; font-size: {{ $diseno->tamaño_titulo ?? 18 }}px;">
                                            {{ $diseno->texto_titulo_factura ?? 'FACTURA' }}
                                        </h2>
                                    @endif
                                    @if($diseno->mostrar_numero_factura ?? true)
                                        <p style="font-size: {{ $diseno->tamaño_texto ?? 12 }}px;"><strong>No:</strong> F001-001-000001</p>
                                    @endif
                                    @if($diseno->mostrar_fecha_emision ?? true)
                                        <p style="font-size: {{ $diseno->tamaño_texto ?? 12 }}px;"><strong>Fecha:</strong> {{ date('d/m/Y') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($diseno->mostrar_info_paciente ?? true)
                        <!-- Información del Paciente -->
                        <div class="mb-4">
                            <h3 class="font-semibold mb-2" style="color: {{ $diseno->color_titulo ?? '#1f2937' }}; font-size: {{ $diseno->tamaño_subtitulo ?? 14 }}px;">
                                FACTURAR A:
                            </h3>
                            <div style="font-size: {{ $diseno->tamaño_texto ?? 12 }}px;">
                                <p><strong>Nombre:</strong> Juan Pérez</p>
                                @if($diseno->mostrar_direccion_paciente ?? true)
                                    <p><strong>Dirección:</strong> Col. Centro, Calle Principal</p>
                                @endif
                                @if($diseno->mostrar_telefono_paciente ?? true)
                                    <p><strong>Teléfono:</strong> +504 9876-5432</p>
                                @endif
                                @if($diseno->mostrar_rtn_paciente ?? true)
                                    <p><strong>RTN:</strong> 98765432109876</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($diseno->mostrar_tabla_servicios ?? true)
                        <!-- Tabla de Servicios -->
                        <div class="mb-6">
                            <table class="w-full border-collapse border" style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}">
                                <thead>
                                    <tr style="background-color: {{ $diseno->color_encabezado_tabla ?? '#f3f4f6' }}">
                                        @if($diseno->mostrar_columna_cantidad ?? true)
                                            <th class="border p-2 text-left" style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; font-size: {{ $diseno->tamaño_texto ?? 12 }}px;">Cant.</th>
                                        @endif
                                        @if($diseno->mostrar_columna_descripcion ?? true)
                                            <th class="border p-2 text-left" style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; font-size: {{ $diseno->tamaño_texto ?? 12 }}px;">Descripción</th>
                                        @endif
                                        @if($diseno->mostrar_columna_precio_unitario ?? true)
                                            <th class="border p-2 text-right" style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; font-size: {{ $diseno->tamaño_texto ?? 12 }}px;">Precio Unit.</th>
                                        @endif
                                        @if($diseno->mostrar_columna_total ?? true)
                                            <th class="border p-2 text-right" style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; font-size: {{ $diseno->tamaño_texto ?? 12 }}px;">Total</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @if($diseno->mostrar_columna_cantidad ?? true)
                                            <td class="border p-2" style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; font-size: {{ $diseno->tamaño_texto ?? 12 }}px;">1</td>
                                        @endif
                                        @if($diseno->mostrar_columna_descripcion ?? true)
                                            <td class="border p-2" style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; font-size: {{ $diseno->tamaño_texto ?? 12 }}px;">Consulta Médica General</td>
                                        @endif
                                        @if($diseno->mostrar_columna_precio_unitario ?? true)
                                            <td class="border p-2 text-right" style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; font-size: {{ $diseno->tamaño_texto ?? 12 }}px;">L. 500.00</td>
                                        @endif
                                        @if($diseno->mostrar_columna_total ?? true)
                                            <td class="border p-2 text-right" style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; font-size: {{ $diseno->tamaño_texto ?? 12 }}px;">L. 500.00</td>
                                        @endif
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if($diseno->mostrar_total ?? true)
                        <!-- Totales -->
                        <div class="flex justify-end">
                            <div class="w-64">
                                @if($diseno->mostrar_subtotal ?? true)
                                    <div class="flex justify-between py-1" style="font-size: {{ $diseno->tamaño_texto ?? 12 }}px;">
                                        <span>Subtotal:</span>
                                        <span>L. 500.00</span>
                                    </div>
                                @endif
                                @if($diseno->mostrar_impuestos ?? true)
                                    <div class="flex justify-between py-1" style="font-size: {{ $diseno->tamaño_texto ?? 12 }}px;">
                                        <span>ISV (15%):</span>
                                        <span>L. 75.00</span>
                                    </div>
                                @endif
                                <div class="flex justify-between py-2 border-t font-bold" 
                                     style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; color: {{ $diseno->color_primario ?? '#1e40af' }}; font-size: {{ ($diseno->tamaño_texto ?? 12) + 2 }}px;">
                                    <span>TOTAL:</span>
                                    <span>L. 575.00</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($diseno->mostrar_pie_pagina ?? true)
                        <!-- Pie de página -->
                        <div class="mt-6 pt-4 border-t text-center" style="border-color: {{ $diseno->color_borde ?? '#e5e7eb' }}; font-size: {{ $diseno->tamaño_texto ?? 12 }}px;">
                            <p>{{ $diseno->texto_pie_pagina ?? 'Gracias por su preferencia' }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 text-center">
            Los cambios se reflejan automáticamente. Expande esta sección para ver más opciones.
        </div>
    </div>
</div>

@push('scripts')
<script>
function abrirVistaCompleta() {
    // Abrir modal o nueva ventana con vista previa completa
    const width = 1000;
    const height = 800;
    const left = (window.innerWidth - width) / 2;
    const top = (window.innerHeight - height) / 2;
    
    window.open(
        '/facturas/preview-demo', 
        'vista-previa-completa',
        `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`
    );
}
</script>
@endpush

<style>
.factura-preview {
    font-family: Arial, sans-serif;
    max-width: 100%;
    margin: 0 auto;
}

.dark .factura-preview {
    background-color: white !important;
    color: #1f2937 !important;
}

.factura-preview * {
    box-sizing: border-box;
}

@media (max-width: 768px) {
    .factura-preview {
        transform: scale(0.7) !important;
        width: 142% !important;
    }
}
</style>
