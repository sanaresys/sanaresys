{{-- resources/views/filament/widgets/calendario-citas-widget.blade.php --}}
<x-filament-widgets::widget>
    {{-- 1. Encabezado --------------------------------------------------------- --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                📅 Calendario de Citas
            </h2>
        </div>
    </x-slot>

    {{-- 2. Calendario --------------------------------------------------------- --}}
    <div
        id="calendario-widget-{{ $this->getId() }}"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
    >
        {{-- Barra superior --}}
        <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center w-full">
                <div class="flex items-center space-x-3 flex-shrink-0">
                    {{-- Select de Mes --}}
                    <select wire:model.live="mes" class="min-w-[120px] px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg font-medium text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 shadow-sm">
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>

                    {{-- Select de Año --}}
                    <select wire:model.live="anio" class="w-20 px-2 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg font-medium text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 shadow-sm text-center">
                        @for ($year = 2020; $year <= 2030; $year++)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                    </select>
                </div>

                <div class="flex-shrink-0">
                    <button wire:click="irHoy" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white rounded-lg font-bold text-sm transition-all duration-200 shadow-lg hover:shadow-xl flex items-center whitespace-nowrap border-2 border-blue-600 hover:border-blue-700 dark:border-blue-500 dark:hover:border-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-white font-bold uppercase tracking-wide">HOY</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- 2.1 Cuerpo del calendario --}}
        <div class="p-4">
            {{-- Días de la semana --}}
            <div class="grid grid-cols-7 gap-px mb-3">
                @foreach (['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d)
                    <div class="py-3 text-center font-bold text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-600 rounded-lg border border-gray-200 dark:border-gray-500">{{ $d }}</div>
                @endforeach
            </div>

            {{-- Días del mes --}}
            <div class="grid grid-cols-7 gap-px">
                @php
                    $primerDia  = Carbon\Carbon::createFromDate($anio,$mes,1);
                    $ultimoDia  = Carbon\Carbon::createFromDate($anio,$mes,1)->endOfMonth();
                    $diasVacios = ($primerDia->dayOfWeek - 1 + 7) % 7;   // lunes = 0
                    $totalDias  = $ultimoDia->day;
                    $diasRest   = 42 - ($diasVacios + $totalDias);      // 6 filas × 7 columnas
                @endphp

                {{-- huecos antes --}}
                @for ($i=0;$i<$diasVacios;$i++)
                    <div class="h-32 p-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-lg opacity-60"></div>
                @endfor

                {{-- días reales --}}
                @for ($dia=1; $dia<= $totalDias; $dia++)
                    @php
                        $hoy          = Carbon\Carbon::now();
                        $esHoy        = $hoy->day==$dia && $hoy->month==$mes && $hoy->year==$anio;
                        $tieneCitas   = !empty($citasPorDia[$dia]);
                        $citasCount   = $tieneCitas ? count($citasPorDia[$dia]) : 0;
                    @endphp

                    <div
                        wire:click="mostrarCitasDelDia('{{ $dia }}')"
                        class="dia-calendario h-32 p-2 border rounded-lg transition-all duration-200 transform hover:scale-105
                               {{ $esHoy ? 'border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-900/30 shadow-lg' : 'border-gray-200 dark:border-gray-700' }}
                               {{ $tieneCitas ? 'bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 hover:from-green-100 hover:to-emerald-100' : 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700' }}
                               overflow-hidden cursor-pointer shadow-sm hover:shadow-md"
                    >
                        <div class="flex justify-between items-start mb-1">
                            @if ($esHoy)
                                <div class="flex items-center justify-center w-7 h-7 rounded-full font-bold text-sm bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md">
                                    {{ $dia }}
                                </div>
                            @elseif ($tieneCitas)
                                <div class="flex items-center justify-center w-7 h-7 rounded-full font-bold text-sm bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-sm">
                                    {{ $dia }}
                                </div>
                            @else
                                <div class="flex items-center justify-center w-7 h-7 rounded-full font-bold text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                    {{ $dia }}
                                </div>
                            @endif

                            @if ($tieneCitas)
                                <div class="flex items-center space-x-1">
                                    <span class="text-xs font-bold px-2 py-1 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-full shadow-sm flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $citasCount }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        @if ($tieneCitas)
                            <div class="space-y-1.5 max-h-20 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300">
                                @foreach (array_slice($citasPorDia[$dia],0,3) as $index => $cita)
                                    <div class="p-1.5 text-xs rounded-lg transform transition-all duration-150 hover:scale-102 cursor-pointer"
                                         style="background:{{ $cita['color'] }}20; border-left: 3px solid {{ $cita['color'] }};">
                                        <div
                                            class="cita-preview block"
                                            wire:click.stop="mostrarCitasDelDia('{{ $dia }}', {{ $cita['id'] }})"
                                        >
                                            <div class="flex items-center justify-between">
                                                <span class="font-semibold text-gray-800 dark:text-gray-100 flex items-center">
                                                    ⏰ {{ $cita['hora'] }}
                                                </span>
                                                @if($cita['estado'] === 'Confirmado')
                                                    <span class="text-green-600">✅</span>
                                                @elseif($cita['estado'] === 'Pendiente')
                                                    <span class="text-yellow-600">⏳</span>
                                                @elseif($cita['estado'] === 'Cancelado')
                                                    <span class="text-red-600">❌</span>
                                                @else
                                                    <span class="text-blue-600">✨</span>
                                                @endif
                                            </div>
                                            <div class="mt-1 text-gray-600 dark:text-gray-300 truncate font-medium">
                                                👤 {{ $cita['paciente'] }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if (count($citasPorDia[$dia])>3)
                                    <div class="text-xs text-center font-medium px-2 py-1 bg-gradient-to-r from-blue-100 to-indigo-100 dark:from-blue-800 dark:to-indigo-800 text-blue-700 dark:text-blue-200 rounded-lg shadow-sm">
                                        📋 +{{ count($citasPorDia[$dia])-3 }} citas más
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endfor

                {{-- huecos después --}}
                @for ($i=0;$i<$diasRest;$i++)
                    <div class="h-32 p-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-lg opacity-60"></div>
                @endfor
            </div>
        </div>
    </div>

    {{-- 3. Incluir la modal de citas del día --}}
    @include('filament.widgets.modal-citas-del-dia')
    
    {{-- 5. Estilos extra (mínimos) --}}
    <style>
        .dia-calendario{transition:all .2s}
        .dia-calendario:hover{box-shadow:inset 0 0 0 2px rgba(59,130,246,.5)}
        .cita-preview{cursor:pointer;transition:opacity .2s}
        .cita-preview:hover{opacity:.8}
        
        /* Estilos para números de días - más específicos */
        .dia-calendario .w-7.h-7.rounded-full {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-weight: bold !important;
            font-size: 0.875rem !important;
        }
        
        /* Día actual (hoy) - azul */
        .dia-calendario .w-7.h-7.bg-gradient-to-r.from-blue-500 {
            background: linear-gradient(to right, #3b82f6, #2563eb) !important;
            color: white !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        
        /* Días con citas - verde */
        .dia-calendario .w-7.h-7.bg-gradient-to-r.from-green-500 {
            background: linear-gradient(to right, #10b981, #059669) !important;
            color: white !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        
        /* Días sin citas - gris claro */
        .dia-calendario .w-7.h-7:not(.bg-gradient-to-r) {
            background: transparent !important;
            color: #374151 !important;
            text-shadow: none;
        }
        
        /* Modo oscuro - días sin citas */
        .dark .dia-calendario .w-7.h-7:not(.bg-gradient-to-r) {
            color: #d1d5db !important;
        }
        
        /* Hover para días sin citas */
        .dia-calendario:hover .w-7.h-7:not(.bg-gradient-to-r) {
            background: rgba(156, 163, 175, 0.1) !important;
        }
        
        .dark .dia-calendario:hover .w-7.h-7:not(.bg-gradient-to-r) {
            background: rgba(75, 85, 99, 0.3) !important;
        }
    </style>
</x-filament-widgets::widget>