{{-- Tabla mejorada de servicios para facturas --}}
<div class="w-full">
    <div class="w-full overflow-hidden bg-white dark:bg-gray-900 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 px-6 py-4 border-b border-blue-200 dark:border-blue-700">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Servicios a Facturar
            </h3>
            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200 text-sm font-medium rounded-full">
                {{ count($detalles) }} servicio(s)
            </span>
        </div>
    </div>

    @if(count($detalles) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Servicio
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Cantidad
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Precio Unit.
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Subtotal
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Impuesto
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Total
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @php 
                        $subtotalGeneral = 0;
                        $impuestoGeneral = 0;
                        $totalGeneral = 0;
                    @endphp
                    
                    @foreach ($detalles as $detalle)
                        @php
                            $servicio = $detalle->servicio;
                            $cantidad = $detalle->cantidad ?? 1;
                            $precioUnitario = $servicio->precio_unitario ?? 0;
                            
                            // Usar subtotal del detalle si existe, sino calcular
                            $subtotalLinea = $detalle->subtotal ?? ($precioUnitario * $cantidad);
                            
                            // Calcular impuesto - usar el guardado o calcularlo si no existe
                            $impuestoLinea = $detalle->impuesto_monto ?? 0;
                            
                            // Si no hay impuesto guardado, calcularlo
                            if ($impuestoLinea == 0 && $servicio->es_exonerado !== 'SI' && $servicio->impuesto) {
                                $impuestoLinea = ($subtotalLinea * $servicio->impuesto->porcentaje) / 100;
                            }
                            
                            $totalLinea = $detalle->total_linea ?? ($subtotalLinea + $impuestoLinea);
                            
                            // Acumular totales
                            $subtotalGeneral += $subtotalLinea;
                            $impuestoGeneral += $impuestoLinea;
                            $totalGeneral += $totalLinea;
                        @endphp
                        
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <div class="flex items-center space-x-3">
                                        <!-- Icono del servicio -->
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                                <span class="text-xs font-medium text-blue-600 dark:text-blue-400">
                                                    {{ substr($servicio->codigo ?? '??', -2) }}
                                                </span>
                                            </div>
                                        </div>
                                        <!-- Información del servicio -->
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $servicio->nombre ?? 'Servicio sin nombre' }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center space-x-2">
                                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-xs font-mono">
                                                    {{ $servicio->codigo ?? 'N/A' }}
                                                </span>
                                                @if($servicio->es_exonerado === 'SI')
                                                    <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-xs font-medium">
                                                        Exonerado
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
                                    {{ number_format($cantidad, 0) }}
                                </span>
                            </td>
                            
                            <td class="px-6 py-4 text-right">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    L. {{ number_format($precioUnitario, 2) }}
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 text-right">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    L. {{ number_format($subtotalLinea, 2) }}
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 text-center">
                                @if($servicio->es_exonerado === 'SI')
                                    <span class="text-xs text-gray-400 dark:text-gray-500">-</span>
                                @else
                                    <div class="text-center">
                                        <div class="text-xs text-gray-600 dark:text-gray-300">
                                            {{ $servicio->impuesto->porcentaje ?? 0 }}%
                                        </div>
                                        <div class="text-sm font-medium text-orange-600 dark:text-orange-400">
                                            L. {{ number_format($impuestoLinea, 2) }}
                                        </div>
                                    </div>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 text-right">
                                <div class="text-sm font-bold text-green-600 dark:text-green-400">
                                    L. {{ number_format($totalLinea, 2) }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                
                <!-- Totales -->
                <tfoot class="bg-gray-50 dark:bg-gray-800">
                    <tr class="border-t-2 border-gray-200 dark:border-gray-600">
                        <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                            Totales:
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-bold text-gray-900 dark:text-gray-100">
                            L. {{ number_format($subtotalGeneral, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-bold text-orange-600 dark:text-orange-400">
                            L. {{ number_format($impuestoGeneral, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right text-lg font-bold text-green-600 dark:text-green-400">
                            L. {{ number_format($totalGeneral, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <!-- Estado vacío -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No hay servicios</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                No hay servicios agregados a esta consulta para facturar.
            </p>
        </div>
    @endif
    </div>
</div>
