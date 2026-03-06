<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Información del proceso -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <x-heroicon-o-information-circle class="h-5 w-5 text-blue-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">
                        Gestión de Cuentas por Cobrar
                    </h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Use esta página para buscar facturas pendientes de pago y procesar pagos parciales o totales.</p>
                        <ul class="list-disc list-inside mt-2 space-y-1">
                            <li>Busque la factura por su número (CAI o Proforma)</li>
                            <li>Verifique el saldo pendiente</li>
                            <li>Procese el pago (puede ser parcial o total)</li>
                            <li>El sistema actualizará automáticamente el estado de la factura y cuenta por cobrar</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario principal -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                {{ $this->form }}
                
                <div class="mt-6 flex justify-end space-x-3">
                    @foreach ($this->getFormActions() as $action)
                        {{ $action }}
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        @if($this->factura && $this->cuentaPorCobrar)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-currency-dollar class="h-5 w-5 text-green-400" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">Total Pagado</p>
                        <p class="text-lg font-semibold text-green-900">
                            L.{{ number_format($this->factura->montoPagado(), 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-clock class="h-5 w-5 text-yellow-400" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800">Saldo Pendiente</p>
                        <p class="text-lg font-semibold text-yellow-900">
                            L.{{ number_format($this->cuentaPorCobrar->saldo_pendiente, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-document-text class="h-5 w-5 text-blue-400" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800">Estado</p>
                        <p class="text-lg font-semibold text-blue-900">
                            {{ $this->cuentaPorCobrar->estado_cuentas_por_cobrar }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>
