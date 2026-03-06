{{-- resources/views/filament/resources/consultas/widgets/facturacion-status.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Estado de Facturación</h3>
                @if($tieneFactura)
                    <x-filament::badge color="success" size="lg">
                        Facturada
                    </x-filament::badge>
                @else
                    <x-filament::badge color="warning" size="lg">
                        Pendiente de facturar
                    </x-filament::badge>
                @endif
            </div>

            @if($tieneFactura)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Número de Factura:</span>
                            <span class="text-gray-900 dark:text-white font-mono">
                                {{ str_pad($factura->numero_factura ?? 0, 8, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Total:</span>
                            <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                L. {{ number_format($factura->total ?? 0, 2) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Estado:</span>
                            <x-filament::badge 
                                :color="match($factura->estado ?? 'PENDIENTE') {
                                    'PENDIENTE' => 'warning',
                                    'PAGADA' => 'success',
                                    'PARCIAL' => 'info',
                                    'ANULADA' => 'danger',
                                    default => 'gray'
                                }"
                            >
                                {{ $factura->estado ?? 'PENDIENTE' }}
                            </x-filament::badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Fecha Emisión:</span>
                            <span class="text-gray-900 dark:text-white">
                                {{ $factura->fecha_emision?->format('d/m/Y') ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Consulta pendiente de facturar
                            </h4>
                            <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                Esta consulta aún no ha sido facturada. Utiliza el botón "Crear Factura" para generar la factura.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>