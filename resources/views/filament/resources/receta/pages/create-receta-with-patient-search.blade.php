<x-filament-panels::page>
    @if (!$this->showRecetaForm)
        {{-- Formulario de búsqueda de paciente --}}
        <div class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    Paso 1: Seleccionar Paciente
                </x-slot>

                <x-slot name="description">
                    Busque y seleccione el paciente para quien desea crear la receta médica.
                </x-slot>

                <div class="space-y-6">
                    {{ $this->patientSearchForm }}

                    <div class="flex justify-end">
                        <x-filament::button
                            wire:click="selectPatient"
                            type="button"
                            size="lg"
                        >
                            <x-heroicon-m-arrow-right class="w-4 h-4 mr-2" />
                            Continuar con este paciente
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        </div>
    @else
        {{-- Formulario de creación de receta --}}
        <div class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    Paso 2: Crear Receta Médica
                </x-slot>

                <x-slot name="description">
                    Complete la información de la receta para el paciente seleccionado.
                </x-slot>

                <div class="space-y-6">
                    {{ $this->recetaForm }}

                    <div class="flex justify-between">
                        <x-filament::button
                            wire:click="changePatient"
                            type="button"
                            color="gray"
                            outlined
                        >
                            <x-heroicon-m-arrow-left class="w-4 h-4 mr-2" />
                            Cambiar paciente
                        </x-filament::button>

                        <x-filament::button
                            wire:click="create"
                            type="button"
                            size="lg"
                        >
                            <x-heroicon-m-document-text class="w-4 h-4 mr-2" />
                            Crear Receta
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        </div>
    @endif
</x-filament-panels::page>
