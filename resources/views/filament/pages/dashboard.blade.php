<x-filament-panels::page>
    {{-- Header personalizado con saludo dinámico --}}
    <div class="mb-6 bg-gradient-to-r from-blue-600 via-purple-600 to-emerald-600 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                @php
                    $hora = now()->format('H');
                    $saludo = match(true) {
                        $hora < 12 => '🌅 Buenos días',
                        $hora < 18 => '☀️ Buenas tardes',
                        default => '🌙 Buenas noches'
                    };
                    $centro = \Spatie\Multitenancy\Models\Tenant::current()?->centro?->nombre_centro ?? session('centro_nombre', 'Sin centro');
                @endphp
                
                <h1 class="text-2xl font-bold mb-1">{{ $saludo }}, {{ auth()->user()->name ?? 'Usuario' }}</h1>
                <p class="text-blue-100 text-sm">🏥 {{ $centro }} • {{ now()->format('l, d \\d\\e F \\d\\e Y') }}</p>
            </div>
            
            <div class="text-right">
                <div class="text-3xl font-bold" id="reloj-tiempo">{{ now()->format('H:i') }}</div>
                <div class="text-sm text-blue-100">Hora actual</div>
            </div>
        </div>
        
        {{-- Accesos rápidos más compactos --}}
        <div class="mt-4 flex gap-2 flex-wrap">
            <a href="/admin/citas/citas/create" 
               class="inline-flex items-center px-3 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-all duration-200 backdrop-blur-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nueva Cita
            </a>
            <a href="/admin/pacientes/create" 
               class="inline-flex items-center px-3 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-all duration-200 backdrop-blur-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                Nuevo Paciente
            </a>
            @if(auth()->user()->hasRole('medico'))
            <a href="/admin/consultas/consultas/create" 
               class="inline-flex items-center px-3 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-all duration-200 backdrop-blur-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Nueva Consulta
            </a>
            @endif
        </div>
    </div>

    {{-- Grid de widgets más organizado --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna 1: Estadísticas (1/3 del ancho) --}}
        <div class="lg:col-span-1 space-y-6">
            @livewire(\App\Filament\Widgets\CentroStatsWidget::class)
            @livewire(\App\Filament\Widgets\CitasPieChart::class)
        </div>
        
        {{-- Columna 2: Calendario (2/3 del ancho) --}}
        <div class="lg:col-span-2">
            @livewire(\App\Filament\Widgets\CalendarioCitasWidget::class)
        </div>
    </div>

    {{-- JavaScript para reloj en tiempo real --}}
    <script>
        function actualizarReloj() {
            const ahora = new Date();
            const horas = String(ahora.getHours()).padStart(2, '0');
            const minutos = String(ahora.getMinutes()).padStart(2, '0');
            const elemento = document.getElementById('reloj-tiempo');
            if (elemento) {
                elemento.textContent = `${horas}:${minutos}`;
            }
        }
        
        // Actualizar cada minuto
        actualizarReloj();
        setInterval(actualizarReloj, 60000);
    </script>

    {{-- Estilos personalizados --}}
    <style>
        /* Hacer las estadísticas más compactas */
        .fi-wi-stats-overview-stat {
            padding: 1rem !important;
            min-height: auto !important;
        }
        
        .fi-wi-stats-overview-stat-value {
            font-size: 1.5rem !important;
        }
        
        .fi-wi-stats-overview-stat-description {
            font-size: 0.8rem !important;
        }
        
        /* Animaciones suaves */
        .fi-wi-stats-overview-stat {
            transition: all 0.3s ease;
        }
        .fi-wi-stats-overview-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
    </style>
</x-filament-panels::page>
