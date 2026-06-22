<x-filament-panels::page>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <!-- Formulario de configuración -->
        <div class="space-y-6">
            <div class="filament-forms-section-content">
                <div class="grid gap-4">
                    {{ $this->form }}
                </div>
            </div>
        </div>

        <!-- Vista previa en tiempo real -->
        <div class="sticky top-0 space-y-4">
            <div class="bg-white dark:bg-gray-800 shadow rounded-xl">
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        Vista Previa en Tiempo Real
                    </h3>
                    <div class="bg-white rounded-lg border border-gray-300 dark:border-gray-700 overflow-hidden">
                        <div class="relative" style="transform: scale(0.7); transform-origin: top left;">
                            @livewire('factura-vista-previa', ['compact' => true])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
