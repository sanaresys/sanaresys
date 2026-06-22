<x-filament-panels::page>
    {{-- Configurar polling automático cada 30 segundos solo si es edición y no está cerrada --}}
    @if(isset($record) && !$record->cerrada)
    <div wire:poll.30s="actualizarBonificacionesAutomaticamente"></div>
    @endif
    
    <div class="space-y-6">
        {{-- Formulario principal --}}
        <form id="nomina-form" wire:submit="{{ isset($record) ? 'save' : 'create' }}" class="space-y-6">
            {{ $this->form }}

            {{-- Sección de médicos --}}
            <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ isset($record) ? 'Editar Médicos en Nómina' : 'Médicos en Nómina' }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            Solo se muestran médicos con contratos activos
                        </span>
                    </p>
                </div>

                {{-- Botones de acción mejorados --}}
                <div class="px-6 py-5 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-gray-800 dark:to-gray-700 border-b border-slate-200 dark:border-gray-600">
                    <div class="flex justify-between items-center">
                        {{-- Información de médicos mejorada --}}
                        <div class="flex items-center space-x-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zM4 18v-4h3v4h2v-7.5c0-.83.67-1.5 1.5-1.5S12 9.67 12 10.5V11h2.5c.83 0 1.5.67 1.5 1.5V15h3v3H4z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-600 dark:text-gray-400">Total de Médicos</p>
                                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ count($medicosSeleccionados) }}</p>
                                </div>
                            </div>
                            
                            <div class="w-px h-12 bg-slate-300 dark:bg-gray-600"></div>
                            
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-600 dark:text-gray-400">Seleccionados</p>
                                    <p class="text-2xl font-bold text-green-600 dark:text-green-400" x-data="{ count: {{ collect($medicosSeleccionados)->where('seleccionado', true)->count() }} }" x-text="count" wire:poll.500ms="$refresh"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="flex space-x-2">
                            {{-- Botón de Actualizar Bonificaciones --}}
                            @if(isset($record) && !$record->cerrada)
                            <button 
                                type="button"
                                wire:click="actualizarBonificacionesAutomaticamente"
                                class="btn-actualizar-bonificaciones inline-flex items-center font-bold transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                <svg class="w-4 h-4 mr-1 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Actualizar Bonificaciones
                            </button>
                            @endif
                            
                            <button 
                                type="button"
                                wire:click="toggleSeleccionTodos"
                                class="btn-seleccionar-todos inline-flex items-center font-bold transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Seleccionar Todos
                            </button>
                            
                            <button 
                                type="button"
                                wire:click="deseleccionarTodos"
                                class="btn-deseleccionar-todos inline-flex items-center font-bold transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Deseleccionar Todos
                            </button>
                        </div>
                    </div>
                </div>                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gradient-to-r from-slate-100 to-slate-200 dark:from-gray-800 dark:to-gray-700">
                                    <th class="px-6 py-5 text-left border-b-2 border-slate-300 dark:border-gray-600">
                                        <span class="text-sm font-bold text-slate-700 dark:text-gray-200 uppercase tracking-wide">Incluir</span>
                                    </th>
                                    <th class="px-6 py-5 text-left border-b-2 border-slate-300 dark:border-gray-600 w-1/3">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center">
                                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-bold text-slate-700 dark:text-gray-200 uppercase tracking-wide">Médico</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-5 text-left border-b-2 border-slate-300 dark:border-gray-600">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-5 h-5 bg-green-500 rounded flex items-center justify-center">
                                                <span class="text-white text-xs font-bold">L</span>
                                            </div>
                                            <span class="text-sm font-bold text-slate-700 dark:text-gray-200 uppercase tracking-wide">Salario Base</span>
                                        </div>
                                    </th>
                                    <th class="px-3 py-4 text-left border-b-2 border-slate-300 dark:border-gray-600 w-24">
                                        <div class="flex items-center space-x-1">
                                            <div class="w-4 h-4 bg-red-500 rounded flex items-center justify-center">
                                                <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"></path>
                                                </svg>
                                            </div>
                                            <span class="text-xs font-bold text-slate-700 dark:text-gray-200 uppercase tracking-wide">Deduc.</span>
                                        </div>
                                    </th>
                                    <th class="px-3 py-4 text-left border-b-2 border-slate-300 dark:border-gray-600 w-24">
                                        <div class="flex items-center space-x-1">
                                            <div class="w-4 h-4 bg-emerald-500 rounded flex items-center justify-center">
                                                <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </div>
                                            <span class="text-xs font-bold text-slate-700 dark:text-gray-200 uppercase tracking-wide">Bonif.</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-5 text-center border-b-2 border-slate-300 dark:border-gray-600">
                                        <div class="flex items-center justify-center space-x-2">
                                            <div class="w-5 h-5 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full flex items-center justify-center">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-bold text-slate-700 dark:text-gray-200 uppercase tracking-wide">Total a Pagar</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($medicosSeleccionados as $index => $medico)
                                    @if(isset($medico['nombre']) && !empty($medico['nombre']) && $medico['nombre'] !== 'Sin nombre')
                                    <tr class="transition-all duration-200 hover:bg-blue-50 dark:hover:bg-gray-700 {{ $medico['seleccionado'] ? 'bg-green-50 dark:bg-green-900/20 ring-1 ring-green-200 dark:ring-green-800' : '' }}">
                                        <td class="px-6 py-3 border-b border-gray-200 dark:border-gray-700">
                                            <div class="flex items-center justify-center">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input 
                                                        type="checkbox" 
                                                        wire:model="medicosSeleccionados.{{ $index }}.seleccionado"
                                                        class="sr-only peer"
                                                    >
                                                    <div class="relative w-6 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-lg peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600 border-2 {{ $medico['seleccionado'] ? 'border-green-600 bg-green-600' : 'border-gray-300' }}">
                                                        @if($medico['seleccionado'])
                                                            <svg class="absolute top-0.5 left-0.5 w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                        @endif
                                                    </div>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 w-1/3">
                                            <div class="text-left">
                                                <div class="text-sm font-medium text-slate-900 dark:text-white whitespace-nowrap">
                                                    {{ $medico['nombre'] }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                            <div class="relative">
                                                <input 
                                                    type="number" 
                                                    step="0.01"
                                                    wire:model.live="medicosSeleccionados.{{ $index }}.salario_base"
                                                    class="w-full px-3 py-2 text-sm font-medium border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white {{ $medico['seleccionado'] ? 'bg-white' : 'bg-gray-50' }}"
                                                    placeholder="0"
                                                    {{ !$medico['seleccionado'] ? 'disabled' : '' }}
                                                >
                                            </div>
                                        </td>
                                        <td class="px-2 py-3 border-b border-gray-200 dark:border-gray-700 w-24">
                                            <div class="relative">
                                                <input 
                                                    type="number" 
                                                    step="0.01"
                                                    wire:model.live="medicosSeleccionados.{{ $index }}.deducciones"
                                                    class="w-full px-2 py-2 text-sm font-medium border border-gray-300 rounded focus:ring-1 focus:ring-red-500 focus:border-red-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white {{ $medico['seleccionado'] ? 'bg-white' : 'bg-gray-50' }}"
                                                    placeholder="0"
                                                    {{ !$medico['seleccionado'] ? 'disabled' : '' }}
                                                >
                                            </div>
                                        </td>
                                        <td class="px-2 py-3 border-b border-gray-200 dark:border-gray-700 w-24">
                                            <div class="relative">
                                                <input 
                                                    type="number" 
                                                    step="0.01"
                                                    wire:model.live="medicosSeleccionados.{{ $index }}.percepciones"
                                                    class="w-full px-2 py-2 text-sm font-medium border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white {{ $medico['seleccionado'] ? 'bg-white' : 'bg-gray-50' }}"
                                                    placeholder="0"
                                                    {{ !$medico['seleccionado'] ? 'disabled' : '' }}
                                                >
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                            <div class="text-right">
                                                <span class="text-sm font-medium text-slate-900 dark:text-white">
                                                    L. {{ number_format($medico['total'], 2) }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center">
                                            <div class="flex flex-col items-center justify-center space-y-3">
                                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                                <p class="text-lg font-medium text-gray-500 dark:text-gray-400">
                                                    No hay médicos con contratos activos
                                                </p>
                                                <p class="text-sm text-gray-400 dark:text-gray-500">
                                                    Asegúrate de que los médicos tengan contratos activos para incluirlos en la nómina
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Botones de acción principales --}}
            <div class="sticky bottom-0 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 px-6 py-4 shadow-lg z-10">
                <div class="flex justify-between items-center">
                    {{-- Resumen de totales mejorado --}}
                    <div class="flex items-center space-x-8">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Médicos Seleccionados</p>
                                <p class="text-lg font-bold text-blue-600 dark:text-blue-400" x-data="{ count: {{ collect($medicosSeleccionados)->where('seleccionado', true)->count() }} }" x-text="count" wire:poll.500ms="$refresh"></p>
                            </div>
                        </div>
                        
                        <div class="w-px h-10 bg-gray-300 dark:bg-gray-600"></div>
                        
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Total a Pagar</p>
                                <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                    L. {{ number_format(collect($medicosSeleccionados)->where('seleccionado', true)->sum('total'), 2) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Botones principales mejorados --}}
                    <div class="flex space-x-2">
                        <a 
                            href="{{ $this->getResource()::getUrl('index') }}"
                            class="btn-cancelar-principal inline-flex items-center font-bold transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Cancelar
                        </a>
                        
                        <button 
                            type="submit"
                            class="btn-guardar-principal inline-flex items-center font-bold transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            GUARDAR {{ isset($record) ? 'CAMBIOS' : 'NÓMINA' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Botón flotante adicional para pantallas pequeñas --}}
        <div class="fixed bottom-6 right-6 lg:hidden z-50">
            <button 
                type="submit"
                form="nomina-form"
                class="w-16 h-16 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-full shadow-2xl hover:shadow-3xl transform hover:scale-110 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-green-500 focus:ring-offset-2"
            >
                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </button>
        </div>
    </div>

    {{-- Estilos adicionales y scripts para mejorar UX --}}
    <style>
        .shadow-3xl {
            box-shadow: 0 35px 60px -12px rgba(0, 0, 0, 0.25);
        }
        
        /* Animación para el botón flotante */
        @keyframes pulse-save {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes spin-slow {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .animate-pulse-save {
            animation: pulse-save 2s ease-in-out infinite;
        }
        
        .animate-spin-slow {
            animation: spin-slow 2s linear infinite;
        }
        
        /* Transiciones suaves para inputs */
        input[type="number"]:disabled {
            background-color: #f9fafb;
            opacity: 0.6;
        }
        
        /* Mejorar el hover de las filas */
        tr:hover input[type="number"]:not(:disabled) {
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px #3b82f6;
        }

        /* Ajustes para botones compactos y agradables */
        .btn-actualizar-bonificaciones {
            background: linear-gradient(to right, #3b82f6, #2563eb); /* Azul */
            color: #ffffff;
            border: none;
            padding: 0.4rem 0.8rem; /* Compacto */
            font-size: 0.75rem; /* Texto más pequeño */
            border-radius: 0.375rem; /* Bordes redondeados */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-actualizar-bonificaciones:hover {
            background: linear-gradient(to right, #2563eb, #1d4ed8);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }
        
        .btn-seleccionar-todos {
            background: linear-gradient(to right, #34d399, #10b981); /* Verde claro */
            color: #ffffff;
            border: none;
            padding: 0.4rem 0.8rem; /* Compacto */
            font-size: 0.75rem; /* Texto más pequeño */
            border-radius: 0.375rem; /* Bordes redondeados */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-seleccionar-todos:hover {
            background: linear-gradient(to right, #10b981, #059669);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        .btn-deseleccionar-todos {
            background: linear-gradient(to right, #f87171, #ef4444); /* Rojo claro */
            color: #ffffff;
            border: none;
            padding: 0.4rem 0.8rem; /* Compacto */
            font-size: 0.75rem; /* Texto más pequeño */
            border-radius: 0.375rem; /* Bordes redondeados */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-deseleccionar-todos:hover {
            background: linear-gradient(to right, #ef4444, #dc2626);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        .btn-cancelar {
            background: linear-gradient(to right, #9ca3af, #6b7280); /* Gris claro */
            color: #ffffff;
            border: none;
            padding: 0.4rem 0.8rem; /* Compacto */
            font-size: 0.75rem; /* Texto más pequeño */
            border-radius: 0.375rem; /* Bordes redondeados */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-cancelar:hover {
            background: linear-gradient(to right, #6b7280, #4b5563);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        /* Ajustes para botones principales */
        .btn-cancelar-principal {
            background: linear-gradient(to right, #9ca3af, #6b7280); /* Gris claro */
            color: #ffffff;
            border: none;
            padding: 0.4rem 0.8rem; /* Más compacto */
            font-size: 0.75rem; /* Texto más pequeño */
            border-radius: 0.25rem; /* Bordes más redondeados */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .btn-cancelar-principal:hover {
            background: linear-gradient(to right, #6b7280, #4b5563);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }

        .btn-guardar-principal {
            background: linear-gradient(to right, #34d399, #10b981); /* Verde claro */
            color: #ffffff;
            border: none;
            padding: 0.4rem 0.8rem; /* Más compacto */
            font-size: 0.75rem; /* Texto más pequeño */
            border-radius: 0.25rem; /* Bordes más redondeados */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .btn-guardar-principal:hover {
            background: linear-gradient(to right, #10b981, #059669);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }
    </style>

    <script>
        document.addEventListener('livewire:initialized', () => {
            // Agregar efecto de pulso al botón flotante cuando hay médicos seleccionados
            const updateFloatingButton = () => {
                const floatingBtn = document.querySelector('.fixed .rounded-full');
                const selectedCount = document.querySelector('[x-text="count"]');
                
                if (floatingBtn && selectedCount) {
                    const count = parseInt(selectedCount.textContent);
                    if (count > 0) {
                        floatingBtn.classList.add('animate-pulse-save');
                    } else {
                        floatingBtn.classList.remove('animate-pulse-save');
                    }
                }
            };
            
            // Ejecutar cada vez que se actualice Livewire
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    setTimeout(updateFloatingButton, 100);
                });
            });
            
            // Ejecutar al cargar la página
            setTimeout(updateFloatingButton, 500);
        });
    </script>
</x-filament-panels::page>
