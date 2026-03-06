<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    <style>
        .centro-scroll {
            scrollbar-width: thin !important;
            scrollbar-color: #9CA3AF #F3F4F6 !important;
            overflow-y: scroll !important;
        }
        .centro-scroll::-webkit-scrollbar {
            width: 12px !important;
            height: 12px !important;
        }
        .centro-scroll::-webkit-scrollbar-track {
            background: #F3F4F6 !important;
            border-radius: 6px !important;
            margin: 2px 0 !important;
        }
        .centro-scroll::-webkit-scrollbar-thumb {
            background-color: #9CA3AF !important;
            border-radius: 6px !important;
            border: 2px solid #F3F4F6 !important;
            min-height: 30px !important;
        }
        .centro-scroll::-webkit-scrollbar-thumb:hover {
            background-color: #6B7280 !important;
        }
        .centro-scroll::-webkit-scrollbar-thumb:active {
            background-color: #4B5563 !important;
        }
        
        /* Tema oscuro */
        .dark .centro-scroll {
            scrollbar-color: #6B7280 #374151 !important;
        }
        .dark .centro-scroll::-webkit-scrollbar-track {
            background: #374151 !important;
        }
        .dark .centro-scroll::-webkit-scrollbar-thumb {
            background-color: #6B7280 !important;
            border: 2px solid #374151 !important;
        }
        .dark .centro-scroll::-webkit-scrollbar-thumb:hover {
            background-color: #9CA3AF !important;
        }
        .dark .centro-scroll::-webkit-scrollbar-thumb:active {
            background-color: #D1D5DB !important;
        }
        
        /* Estilos para el contenedor scrolleable adaptado a temas */
        .scroll-container {
            height: 350px; /* 
                PERSONALIZAR ALTURA DEL DROPDOWN:
                - Para más pequeño: 150px, 160px, 180px
                - Para más grande: 250px, 300px, 350px
                - Para muy grande: 400px, 450px
            */
            overflow-y: scroll;
            border-radius: 8px;
            border: 1px solid #E5E7EB;
            background: #FFFFFF;
        }
        .dark .scroll-container {
            border-color: #4B5563;
            background: #1F2937;
        }
    </style>
    
    @php
        $currentCentro = $availableCentros->where('id', $selectedCentro)->first();
    @endphp
    
    <!-- Botón del selector -->
    <button 
        @click="open = !open"
        type="button"
        class="centro-selector-button flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
        title="{{ $currentCentro ? $currentCentro->nombre_centro : 'Seleccionar centro' }}"
    >
        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        
        <span class="hidden sm:block max-w-32 truncate">
            {{ $currentCentro ? $currentCentro->nombre_centro : 'Seleccionar centro' }}
        </span>
        
        <span class="sm:hidden text-xs">
            Centro
        </span>
        
        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform flex-shrink-0" 
             :class="{ 'rotate-180': open }" 
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown -->
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.away="open = false; $wire.closeDropdown()"
        @keydown.escape.window="open = false; $wire.closeDropdown()"
        class="absolute left-0 mt-2 w-80 sm:w-[400px] lg:w-[450px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl z-50 max-w-[calc(100vw-2rem)]"
        style="transform-origin: top left; max-height: 85vh;"
        {{-- 
            PERSONALIZAR ANCHO DEL DROPDOWN:
            Cambia las clases w-* por:
            - Más pequeño: w-72 sm:w-80 lg:w-96 (288px, 320px, 384px)
            - Más grande: w-96 sm:w-[500px] lg:w-[600px] (384px, 500px, 600px)
            - Muy grande: w-[400px] sm:w-[600px] lg:w-[700px]
        --}}
    >
        <div class="p-4">
            <!-- Encabezado -->
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Centros Médicos
                </h3>
                @if(auth()->user()->hasRole('root'))
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        Admin
                    </span>
                @endif
            </div>

            <!-- Barra de búsqueda -->
            @if($availableCentros->count() > 3)
                <div class="relative mb-3">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live="search"
                        placeholder="Buscar centro..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                </div>
            @endif

            <!-- Lista de centros con scroll adaptativo -->
            <div class="scroll-container centro-scroll">
                @if($availableCentros->count() > 0)
                    <div class="p-2">
                        @foreach($availableCentros as $centro)
                            <form
                                method="POST"
                                action="{{ route('portal.root.enter-tenant', $centro) }}"
                                class="block w-full px-3 py-3 mb-1 text-sm text-left rounded-lg transition-all duration-200 group
                                       {{ $centro->id == $selectedCentro 
                                          ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-800 dark:text-primary-200 ring-2 ring-primary-300 dark:ring-primary-600 shadow-sm' 
                                          : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:shadow-sm' }}"
                            >
                                @csrf
                                <button type="submit" class="w-full text-left">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium truncate">
                                            {{ $centro->nombre_centro }}
                                        </div>
                                        @if($centro->direccion)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate mt-1">
                                                {{ $centro->direccion }}
                                            </div>
                                        @endif
                                    </div>
                                    
                                    @if($centro->id == $selectedCentro)
                                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 flex-shrink-0 ml-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    @endif
                                </div>
                                </button>
                            </form>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-full px-4 py-8 text-center">
                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($search)
                                No se encontraron centros con "<span class="font-medium">{{ $search }}</span>"
                            @else
                                No hay centros disponibles
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            <!-- Footer con información -->
            @if($availableCentros->count() > 0)
                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                        {{ $availableCentros->count() }} 
                        {{ $availableCentros->count() == 1 ? 'centro disponible' : 'centros disponibles' }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
