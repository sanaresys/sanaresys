<x-filament-panels::page>
    @if (!$this->showConsultaForm)
        {{-- Formulario de búsqueda de paciente --}}
        <div class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    Paso 1: Seleccionar Paciente
                </x-slot>

                <x-slot name="description">
                    Busque y seleccione el paciente para quien desea crear la consulta médica.
                </x-slot>

                <div class="space-y-6">
                    {{ $this->patientSearchForm }}

                    <div class="flex justify-between items-center">
                        <x-filament::button
                            wire:click="redirectToCreatePatient"
                            type="button"
                            color="success"
                            outlined
                            size="lg"
                        >
                            <x-heroicon-o-user-plus class="w-4 h-4 mr-2" />
                            Crear Nuevo Paciente
                        </x-filament::button>

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
        {{-- Formulario de creación de consulta --}}
        <div class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    Paso 2: Crear Consulta Médica
                </x-slot>

                <x-slot name="description">
                    Complete la información de la consulta para el paciente seleccionado.
                </x-slot>

                <div class="space-y-6">
                    {{ $this->consultaForm }}

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
                            <x-heroicon-m-clipboard-document-list class="w-4 h-4 mr-2" />
                            Crear Consulta
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        </div>
    @endif
</x-filament-panels::page>
