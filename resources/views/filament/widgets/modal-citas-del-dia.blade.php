@php
    use Filament\Support\Enums\IconPosition;
@endphp

<x-filament::modal id="citas-del-dia-modal" width="md" display-classes="block">
    <x-slot name="header">
        <div class="flex items-center gap-x-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-50 text-primary-500">
                <x-filament::icon icon="heroicon-o-calendar" class="w-6 h-6" />
            </div>
            <div>
                <h2 class="text-xl font-bold tracking-tight">Citas del día</h2>
                <p class="text-sm text-gray-500">{{ $diaSeleccionado }}</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if(empty($citasDelDia))
            <div class="p-6 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-4">
                    <x-filament::icon icon="heroicon-o-calendar" class="w-6 h-6 text-gray-500" />
                </div>
                <h3 class="text-lg font-medium text-gray-900">No hay citas programadas</h3>
                <p class="mt-2 text-sm text-gray-500">No se encontraron citas para este día.</p>
            </div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach($citasDelDia as $cita)
                    <li class="py-3">
                        <div class="flex items-start justify-between gap-x-3">
                            <div class="flex items-start gap-x-3">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-primary-50">
                                        <span class="font-medium text-primary-700">
                                            {{ $cita['hora'] }}
                                        </span>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $cita['paciente'] ?? 'Paciente' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mb-1">
                                        {{ $cita['medico'] ?? '' }} {{ !empty($cita['especialidad']) ? '- ' . $cita['especialidad'] : '' }}
                                    </p>
                                    
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @if($cita['estado'] === 'Pendiente') bg-yellow-100 text-yellow-800 dark:bg-yellow-800/30 dark:text-yellow-200
                                        @elseif($cita['estado'] === 'Confirmado') bg-blue-100 text-blue-800 dark:bg-blue-800/30 dark:text-blue-200
                                        @elseif($cita['estado'] === 'Realizada') bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-200
                                        @elseif($cita['estado'] === 'Cancelado') bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-200
                                        @endif">
                                        {{ $cita['estado'] }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex flex-col gap-y-2">
                                @if($cita['estado'] === 'Pendiente')
                                    <x-filament::button
                                        wire:click="confirmarCita({{ $cita['id'] }})"
                                        color="info"
                                        size="xs"
                                    >
                                        Confirmar
                                    </x-filament::button>
                                @endif
                                
                                @if($cita['estado'] === 'Confirmado')
                                    {{-- Botón principal usando el método del widget --}}
                                    <x-filament::button
                                        tag="a"
                                        href="{{ route('filament.admin.resources.consultas.consultas.create', ['paciente_id' => $cita['paciente_id'] ?? '', 'cita_id' => $cita['id']]) }}"
                                        target="_self"
                                        color="success"
                                        size="xs"
                                        class="mt-1"
                                    >
                                        Ir a crear consulta 
                                    </x-filament::button>
                                @endif
                                
                                @if(in_array($cita['estado'], ['Pendiente', 'Confirmado']))
                                    <x-filament::button
                                        wire:click="cancelarCita({{ $cita['id'] }})"
                                        color="danger"
                                        size="xs"
                                    >
                                        Cancelar
                                    </x-filament::button>
                                @endif
                                
                                <x-filament::button
                                    tag="a"
                                    href="{{ route('filament.admin.resources.citas.citas.'.((in_array($cita['estado'], ['Cancelado', 'Realizada'])) ? 'view' : 'edit'), $cita['id']) }}"
                                    color="gray"
                                    size="xs"
                                >
                                    {{ in_array($cita['estado'], ['Cancelado', 'Realizada']) ? 'Ver detalles' : 'Ver cita' }}
                                </x-filament::button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <x-slot name="footer">
        <div class="flex justify-end gap-x-3">
            <x-filament::button color="gray" wire:click="$dispatch('close-modal', { id: 'citas-del-dia-modal' })">
                Cerrar
            </x-filament::button>
            <x-filament::button 
                tag="a" 
                href="{{ route('filament.admin.resources.citas.citas.create', ['fecha' => $fechaSeleccionadaUrl ?? '']) }}"
                icon="heroicon-m-plus" 
                icon-position="{{ IconPosition::Before }}"
            >
                Crear cita
            </x-filament::button>
        </div>
    </x-slot>
</x-filament::modal>
