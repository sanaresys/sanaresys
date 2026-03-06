{{-- resources/views/filament/resources/consultas/widgets/informacion-factura.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Información de la Factura
        </x-slot>
        
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-700">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Paciente -->
                <div class="text-center">
                    <div class="flex justify-center mb-3">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Paciente</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $pacienteNombre }}</p>
                </div>
                
                <!-- Médico -->
                <div class="text-center">
                    <div class="flex justify-center mb-3">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Médico</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $medicoNombre }}</p>
                </div>
                
                <!-- Centro -->
                <div class="text-center">
                    <div class="flex justify-center mb-3">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-800 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Centro</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $centroNombre }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $fecha }}</p>
                </div>
            </div>
            
            <!-- Información de la Factura -->
            <div class="mt-6 pt-6 border-t border-blue-200 dark:border-blue-700">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Número de Factura -->
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Número de Factura</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $numeroFactura }}</p>
                    </div>
                    
                    <!-- Fecha de Emisión -->
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Fecha de Emisión</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $fechaEmision }}</p>
                    </div>
                    
                    <!-- Estado -->
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Estado</p>
                        <x-filament::badge 
                            :color="match($estado) {
                                'PENDIENTE' => 'warning',
                                'PAGADA' => 'success',
                                'PARCIAL' => 'info',
                                'ANULADA' => 'danger',
                                default => 'gray'
                            }"
                        >
                            {{ $estado }}
                        </x-filament::badge>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
