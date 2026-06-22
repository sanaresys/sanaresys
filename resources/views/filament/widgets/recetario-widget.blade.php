<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div class="flex flex-col">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Configure su recetario para prescribir medicamentos</p>

                <!-- Formulario de configuración -->
                <div class="mt-4">
                    <form wire:submit.prevent class="w-full">
                        {{ $this->form }}
                    </form>
                </div>

                <!-- Información para usuarios sin registro -->
                @if(!auth()->user()->medico)
                    <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
                        <div class="flex items-start gap-3">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" />
                            <div class="text-sm text-amber-800 dark:text-amber-200">
                                <p class="font-medium">Registro de médico pendiente</p>
                                <p class="mt-1">Para activar su recetario, debe completar su registro médico en el sistema.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
