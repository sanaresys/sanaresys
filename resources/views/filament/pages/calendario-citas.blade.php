<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Cabecera del calendario -->
        <div class="p-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <button type="button" wire:click="mesAnterior" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white capitalize">{{ $mesActual }} {{ $anio }}</h2>
                    <button type="button" wire:click="mesSiguiente" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
                <div>
                    <button type="button" wire:click="hoy" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                        Hoy
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Cuerpo del calendario -->
        <div class="p-4">
            <!-- Días de la semana -->
            <div class="grid grid-cols-7 gap-px mb-2">
                @php
                    $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                @endphp
                
                @foreach($diasSemana as $dia)
                    <div class="py-2 text-center font-medium text-gray-700 dark:text-gray-300">
                        {{ $dia }}
                    </div>
                @endforeach
            </div>
            
            <!-- Días del mes -->
            <div class="grid grid-cols-7 gap-px">
                @php
                    $primerDia = Carbon\Carbon::createFromDate($anio, $mes, 1);
                    $ultimoDia = Carbon\Carbon::createFromDate($anio, $mes, 1)->endOfMonth();
                    
                    // Ajustar para que el primer día de la semana sea lunes (1)
                    $diasVacios = ($primerDia->dayOfWeek - 1) % 7;
                    if ($diasVacios < 0) $diasVacios += 7;
                    
                    $totalDias = $ultimoDia->day;
                @endphp
                
                <!-- Celdas vacías antes del primer día -->
                @for($i = 0; $i < $diasVacios; $i++)
                    <div class="h-24 p-1 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800"></div>
                @endfor
                
                <!-- Días del mes -->
                @for($dia = 1; $dia <= $totalDias; $dia++)
                    @php
                        $esDiaActual = Carbon\Carbon::now()->day == $dia && 
                                      Carbon\Carbon::now()->month == $mes && 
                                      Carbon\Carbon::now()->year == $anio;
                        
                        $tieneCitas = isset($citasPorDia[$dia]) && count($citasPorDia[$dia]) > 0;
                    @endphp
                    
                    <div class="h-32 p-1 border {{ $esDiaActual ? 'border-primary-500 dark:border-primary-500' : 'border-gray-200 dark:border-gray-700' }} 
                              {{ $tieneCitas ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-white dark:bg-gray-800' }} 
                              overflow-hidden cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                        <div class="flex justify-between">
                            <!-- Número del día -->
                            <div class="w-6 h-6 flex items-center justify-center {{ $esDiaActual ? 'bg-primary-500 text-white rounded-full' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $dia }}
                            </div>
                            
                            <!-- Indicador de cantidad de citas -->
                            @if($tieneCitas)
                                <div class="text-xs font-medium px-1.5 py-0.5 bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100 rounded-full">
                                    {{ count($citasPorDia[$dia]) }}
                                </div>
                            @endif
                        </div>
                        
                        <!-- Listado de citas para este día -->
                        @if($tieneCitas)
                            <div class="mt-1 space-y-1 max-h-24 overflow-y-auto">
                                @foreach(array_slice($citasPorDia[$dia], 0, 3) as $cita)
                                    <div class="p-1 text-xs rounded-md" style="background-color: {{ $cita['color'] }}20; border-left: 3px solid {{ $cita['color'] }};">
                                        <a href="/admin/citas/citas/{{ $cita['id'] }}/edit" class="block hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded">
                                            <div class="font-medium text-gray-800 dark:text-gray-200 truncate">
                                                {{ $cita['hora'] }} - {{ $cita['paciente'] }}
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                                
                                @if(count($citasPorDia[$dia]) > 3)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                        +{{ count($citasPorDia[$dia]) - 3 }} más
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endfor
                
                <!-- Celdas vacías después del último día -->
                @php
                    $diasRestantes = 42 - ($diasVacios + $totalDias);
                @endphp
                
                @for($i = 0; $i < $diasRestantes; $i++)
                    <div class="h-24 p-1 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800"></div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Modal de citas -->
    <div 
        x-data="{ 
            showModal: false, 
            dia: null, 
            citas: [],
            actualizarEstadoCita(citaId, nuevoEstado) {
                // Encontrar la cita en el arreglo y actualizar su estado
                const citaIndex = this.citas.findIndex(c => c.id === citaId);
                if (citaIndex !== -1) {
                    this.citas[citaIndex].estado = nuevoEstado;
                }
            }
        }"
        @mostrar-citas-dia.window="
            showModal = true;
            dia = $event.detail.dia;
            citas = $event.detail.citas;
        "
    >
        <div 
            x-show="showModal" 
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div 
                class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                @click.away="showModal = false"
            >
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Citas del día <span x-text="dia"></span>
                    </h3>
                    <button @click="showModal = false" type="button" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Cerrar</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-4 py-2 max-h-[60vh] overflow-y-auto">
                    <template x-if="citas.length === 0">
                        <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                            No hay citas programadas para este día.
                        </div>
                    </template>
                    
                    <template x-for="(cita, index) in citas" :key="index">
                        <div class="py-3 border-b border-gray-200 dark:border-gray-700 last:border-0">
                            <div class="flex justify-between items-start space-x-4">
                                <div>
                                    <div class="flex items-center">
                                        <span x-text="cita.hora" class="font-medium mr-2"></span>
                                        <span x-text="cita.paciente" class="font-medium text-gray-800 dark:text-gray-200"></span>
                                    </div>
                                    <p x-text="cita.motivo" class="text-sm text-gray-600 dark:text-gray-400 mt-1"></p>
                                    <div class="mt-1">
                                        <span 
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/30 dark:text-yellow-200': cita.estado === 'Pendiente',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-800/30 dark:text-blue-200': cita.estado === 'Confirmado',
                                                'bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-200': cita.estado === 'Realizada',
                                                'bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-200': cita.estado === 'Cancelado',
                                                'bg-gray-100 text-gray-800 dark:bg-gray-800/30 dark:text-gray-200': cita.estado !== 'Pendiente' && cita.estado !== 'Confirmado' && cita.estado !== 'Realizada' && cita.estado !== 'Cancelado'
                                            }"
                                            x-text="cita.estado"
                                        ></span>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <!-- CASO 1: Cita pendiente - Mostrar botón confirmar -->
                                    <template x-if="cita.estado === 'Pendiente'">
                                        <button 
                                            @click.prevent="$wire.confirmarCita(cita.id).then(result => { 
                                                if (result && result.estado) { 
                                                    cita.estado = result.estado;
                                                    $parent.actualizarEstadoCita(cita.id, result.estado);
                                                }
                                            })"
                                            class="px-3 py-1 text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 border border-blue-300 hover:border-blue-500 rounded"
                                        >
                                            Confirmar cita
                                        </button>
                                    </template>
                                    
                                    <!-- CASO 2: Cita confirmada - Mostrar botones de cancelar y crear consulta -->
                                    <template x-if="cita.estado === 'Confirmado'">
                                        <button 
                                            @click.prevent="
                                                // Mostrar indicador de carga
                                                $el.disabled = true;
                                                $el.innerHTML = 'Redirigiendo...';
                                                
                                                // Llamar al método y esperar respuesta
                                                $wire.crearConsulta(cita.id).catch(error => {
                                                    console.error('Error al crear consulta:', error);
                                                    $el.disabled = false;
                                                    $el.innerHTML = 'Crear consulta';
                                                });
                                            "
                                            class="px-3 py-1 text-sm font-medium text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 border border-green-300 hover:border-green-500 rounded"
                                        >
                                            Crear consulta
                                        </button>
                                    </template>
                                    
                                    <!-- Cancelar para estados Pendiente y Confirmado -->
                                    <template x-if="cita.estado === 'Pendiente' || cita.estado === 'Confirmado'">
                                        <button 
                                            @click.prevent="$wire.cancelarCita(cita.id).then(result => {
                                                if (result && result.estado) {
                                                    cita.estado = result.estado;
                                                    $parent.actualizarEstadoCita(cita.id, result.estado);
                                                }
                                            })"
                                            class="px-3 py-1 text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 border border-red-300 hover:border-red-500 rounded"
                                        >
                                            Cancelar cita
                                        </button>
                                    </template>

                                    <!-- Ver detalles para todos los estados -->
                                    <a 
                                        :href="'/admin/citas/citas/' + cita.id + (cita.estado === 'Cancelado' || cita.estado === 'Realizada' ? '/view' : '/edit')"
                                        class="px-3 py-1 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300 border border-gray-300 hover:border-gray-500 rounded text-center"
                                    >
                                        <span x-text="cita.estado === 'Cancelado' || cita.estado === 'Realizada' ? 'Ver detalles' : 'Ver cita'"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 flex justify-end">
                    <button 
                        @click="showModal = false" 
                        type="button" 
                        class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Guardar las citas por día en una variable global
        window.citasPorDia = {!! json_encode($citasPorDia) !!} || {};
        
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener todos los días del calendario (con o sin citas)
            const diasDelMes = document.querySelectorAll('.h-32');
            
            // Añadir evento click a cada día del mes (que tenga número)
            diasDelMes.forEach(function(dia) {
                const diaNumeroElement = dia.querySelector('.w-6.h-6');
                if (!diaNumeroElement) return; // Si no tiene número, es un día fuera del mes
                
                dia.addEventListener('click', function() {
                    // Obtener el número del día y sus citas
                    const diaNumero = diaNumeroElement.textContent.trim();
                    const citasDelDia = window.citasPorDia[diaNumero] || [];
                    
                    // Disparar evento personalizado para mostrar el modal
                    window.dispatchEvent(new CustomEvent('mostrar-citas-dia', {
                        detail: {
                            dia: diaNumero + ' de ' + '{{ $mesActual }} {{ $anio }}',
                            citas: citasDelDia
                        }
                    }));
                });
            });
        });
        
        // Escuchar eventos de Livewire
        document.addEventListener('livewire:initialized', () => {
            // Escuchar actualización de citas después de cancelar o crear consulta
            Livewire.on('citasActualizadas', () => {
                // Ya no cerramos el modal automáticamente, ya que ahora manejamos la actualización en tiempo real
                // Solo actualizamos cuando cambiamos de mes o necesitamos actualizar toda la vista
            });
            
            // Escuchar actualización del mes
            Livewire.on('mesActualizado', () => {
                // Ya no necesitamos recargar la página aquí porque ahora usamos redirecciones con parámetros
                // Esto evita el comportamiento de cambiar y luego volver al mes anterior
            });
            
            // Añadir manejador para redirección a consulta
            Livewire.on('redirigirConsulta', (data) => {
                console.log('Redirigiendo a:', data.url);
                // Redirigir a la URL de creación de consulta
                window.location.href = data.url;
            });
            
            // Solo recargar cuando hay cambios que afectan a todo el calendario
            // Quitamos el hook de refresh para evitar recargas automáticas no deseadas
            if (window.$wireui && window.$wireui.hook) {
                window.$wireui.hook('refresh', () => {
                    // No hacemos nada aquí para evitar recargas automáticas
                    // La navegación entre meses se maneja con redirecciones explícitas
                });
            }
        });
    </script>

    <style>
        /* Estilos para el calendario */
        [x-cloak] { display: none !important; }
    </style>
</x-filament-panels::page>
