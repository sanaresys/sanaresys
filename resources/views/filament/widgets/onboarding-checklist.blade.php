<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            @php
                $checklist = $this->getChecklist();
                $progress = $this->getProgress();
            @endphp

            @if(!empty($checklist))
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            🎉 ¡Bienvenido a Sanaresys!
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Completa estas tareas opcionales para aprovechar al máximo tu clínica digital
                        </p>
                    </div>
                    
                    <button
                        wire:click="dismiss"
                        type="button"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"
                        title="Ocultar este checklist"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Barra de progreso -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Progreso</span>
                        <span class="font-semibold text-primary-600 dark:text-primary-400">{{ $progress }}%</span>
                    </div>
                    <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div 
                            class="h-full bg-gradient-to-r from-primary-500 to-primary-600 transition-all duration-500 ease-out"
                            style="width: {{ $progress }}%"
                        ></div>
                    </div>
                </div>

                <!-- Lista de tareas -->
                <div class="grid gap-3 sm:grid-cols-2 mt-6">
                    @foreach($checklist as $item)
                        <a 
                            href="{{ $item['url'] }}"
                            class="group relative flex items-start gap-3 p-4 rounded-lg border-2 transition-all
                                   {{ $item['completed'] 
                                      ? 'border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-950/20' 
                                      : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 hover:border-'.$item['color'].'-300 dark:hover:border-'.$item['color'].'-700' }}"
                        >
                            <!-- Icono -->
                            <div class="flex-shrink-0 mt-0.5">
                                @if($item['completed'])
                                    <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-{{ $item['color'] }}-100 dark:bg-{{ $item['color'] }}-900/30 flex items-center justify-center group-hover:bg-{{ $item['color'] }}-200 dark:group-hover:bg-{{ $item['color'] }}-900/50 transition">
                                        <x-dynamic-component 
                                            :component="'heroicon-o-' . explode('-o-', $item['icon'])[1]"
                                            class="w-5 h-5 text-{{ $item['color'] }}-600 dark:text-{{ $item['color'] }}-400"
                                        />
                                    </div>
                                @endif
                            </div>

                            <!-- Contenido -->
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-sm
                                           {{ $item['completed'] 
                                              ? 'text-green-900 dark:text-green-100 line-through' 
                                              : 'text-gray-900 dark:text-white group-hover:text-'.$item['color'].'-700 dark:group-hover:text-'.$item['color'].'-300' }}">
                                    {{ $item['title'] }}
                                </h4>
                                <p class="mt-0.5 text-xs
                                          {{ $item['completed'] 
                                             ? 'text-green-600 dark:text-green-400' 
                                             : 'text-gray-500 dark:text-gray-400' }}">
                                    {{ $item['description'] }}
                                </p>
                            </div>

                            <!-- Flecha -->
                            @if(!$item['completed'])
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-{{ $item['color'] }}-500 transition-transform group-hover:translate-x-1" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>

                <!-- Mensaje motivacional -->
                @if($progress === 100)
                    <div class="mt-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950/20 dark:to-emerald-950/20 rounded-lg border border-green-200 dark:border-green-900">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-green-900 dark:text-green-100">
                                    ¡Excelente trabajo!
                                </h4>
                                <p class="text-sm text-green-700 dark:text-green-300">
                                    Has completado todas las tareas sugeridas. Tu clínica está lista para comenzar. 🚀
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
