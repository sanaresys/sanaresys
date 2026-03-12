<x-filament-panels::page>
    {{-- Título del Dashboard --}}
    <div style="margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Dashboard</h1>
    </div>

    {{-- Fila 1: Cuentas por cobrar (3 tarjetas con color de fondo) --}}
    @if(tenancy()->initialized)
    @php
        $cuentasPendientes = \App\Models\CuentasPorCobrar::where('saldo_pendiente', '>', 0)
            ->whereIn('estado_cuentas_por_cobrar', ['PENDIENTE', 'PARCIAL', 'VENCIDA'])
            ->count();
        $totalSaldoPendiente = \App\Models\CuentasPorCobrar::where('saldo_pendiente', '>', 0)
            ->whereIn('estado_cuentas_por_cobrar', ['PENDIENTE', 'PARCIAL', 'VENCIDA'])
            ->sum('saldo_pendiente');
        $cuentasVencidas = \App\Models\CuentasPorCobrar::where('fecha_vencimiento', '<', now())
            ->where('saldo_pendiente', '>', 0)
            ->whereIn('estado_cuentas_por_cobrar', ['PENDIENTE', 'PARCIAL', 'VENCIDA'])
            ->count();
        $totalVencido = \App\Models\CuentasPorCobrar::where('fecha_vencimiento', '<', now())
            ->where('saldo_pendiente', '>', 0)
            ->whereIn('estado_cuentas_por_cobrar', ['PENDIENTE', 'PARCIAL', 'VENCIDA'])
            ->sum('saldo_pendiente');
    @endphp

    <div class="dash-row-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
        {{-- Cuentas Pendientes --}}
        <div style="border-radius: 0.75rem; padding: 1.25rem; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background: linear-gradient(135deg, #4ade80, #16a34a); position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0.75rem; right: 0.75rem; opacity: 0.3;">
                <svg style="width: 2.5rem; height: 2.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p style="font-size: 0.875rem; font-weight: 500; color: #bbf7d0;">Cuentas Pendientes</p>
            <p style="font-size: 2.25rem; font-weight: 700; margin-top: 0.25rem;">{{ $cuentasPendientes }}</p>
            <p style="font-size: 0.875rem; color: #bbf7d0; margin-top: 0.5rem;">L {{ number_format($totalSaldoPendiente, 2) }} total con saldo pendiente</p>
        </div>

        {{-- Saldo Total Pendiente --}}
        <div style="border-radius: 0.75rem; padding: 1.25rem; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background: linear-gradient(135deg, #60a5fa, #2563eb); position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0.75rem; right: 0.75rem; opacity: 0.3;">
                <svg style="width: 2.5rem; height: 2.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p style="font-size: 0.875rem; font-weight: 500; color: #bfdbfe;">Saldo Total Pendiente</p>
            <p style="font-size: 2.25rem; font-weight: 700; margin-top: 0.25rem;">L {{ number_format($totalSaldoPendiente, 2) }}</p>
            <p style="font-size: 0.875rem; color: #bfdbfe; margin-top: 0.5rem;">Monto total por cobrar</p>
        </div>

        {{-- Cuentas Vencidas --}}
        <div style="border-radius: 0.75rem; padding: 1.25rem; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background: linear-gradient(135deg, #f87171, #dc2626); position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0.75rem; right: 0.75rem; opacity: 0.3;">
                <svg style="width: 2.5rem; height: 2.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <p style="font-size: 0.875rem; font-weight: 500; color: #fecaca;">Cuentas Vencidas</p>
            <p style="font-size: 2.25rem; font-weight: 700; margin-top: 0.25rem;">{{ $cuentasVencidas }}</p>
            <p style="font-size: 0.875rem; color: #fecaca; margin-top: 0.5rem;">L {{ number_format($totalVencido, 2) }} vencido</p>
        </div>
    </div>
    @endif

    {{-- Fila 2: Estadísticas del centro (4 tarjetas blancas con íconos) --}}
    @if(tenancy()->initialized)
    @php
        $tenant = tenancy()->tenant;
        $centroNombre = 'Centro Médico';
        if ($tenant) {
            $centro = \App\Models\Centros_Medico::on('mysql')->find($tenant->centro_id);
            $centroNombre = $centro ? $centro->nombre_centro : 'Centro ' . ($tenant->centro_id ?? $tenant->id);
        }
        $pacientesCount = \App\Models\Pacientes::count();
        $medicosCount = \App\Models\Medico::count();
        $totalCitasHoy = \App\Models\Citas::whereDate('fecha', today())->count();
        $citasPendientesHoy = \App\Models\Citas::whereDate('fecha', today())->where('estado', 'Pendiente')->count();
    @endphp

    <div class="dash-row-4" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
        {{-- Centro Actual --}}
        <div class="dash-stat-card" style="background: white; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #f3f4f6;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                <div style="padding: 0.5rem; background: #eff6ff; border-radius: 0.5rem;">
                    <svg style="width: 1.25rem; height: 1.25rem; color: #2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span style="font-size: 0.875rem; font-weight: 500; color: #6b7280;">Centro Actual</span>
            </div>
            <p style="font-size: 1.25rem; font-weight: 700; color: #1f2937;">{{ $centroNombre }}</p>
            <p style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem;">Datos del tenant activo</p>
        </div>

        {{-- Pacientes --}}
        <a href="/admin/pacientes" class="dash-stat-card" style="display: block; text-decoration: none; background: white; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #f3f4f6;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                <div style="padding: 0.5rem; background: #faf5ff; border-radius: 0.5rem;">
                    <svg style="width: 1.25rem; height: 1.25rem; color: #9333ea;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span style="font-size: 0.875rem; font-weight: 500; color: #6b7280;">Pacientes</span>
            </div>
            <p style="font-size: 1.875rem; font-weight: 700; color: #1f2937;">{{ number_format($pacientesCount) }}</p>
            <p style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem;">Total registrados</p>
        </a>

        {{-- Médicos --}}
        <a href="/admin/medico/medicos" class="dash-stat-card" style="display: block; text-decoration: none; background: white; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #f3f4f6;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                <div style="padding: 0.5rem; background: #ecfdf5; border-radius: 0.5rem;">
                    <svg style="width: 1.25rem; height: 1.25rem; color: #059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span style="font-size: 0.875rem; font-weight: 500; color: #6b7280;">Médicos</span>
            </div>
            <p style="font-size: 1.875rem; font-weight: 700; color: #1f2937;">{{ number_format($medicosCount) }}</p>
            <p style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem;">Personal médico</p>
        </a>

        {{-- Citas Hoy --}}
        <a href="/admin/citas/citas" class="dash-stat-card" style="display: block; text-decoration: none; background: white; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #f3f4f6;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                <div style="padding: 0.5rem; background: #fff7ed; border-radius: 0.5rem;">
                    <svg style="width: 1.25rem; height: 1.25rem; color: #ea580c;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span style="font-size: 0.875rem; font-weight: 500; color: #6b7280;">Citas Hoy</span>
            </div>
            <p style="font-size: 1.875rem; font-weight: 700; color: #1f2937;">{{ number_format($totalCitasHoy) }}</p>
            <p style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem;">{{ $citasPendientesHoy > 0 ? $citasPendientesHoy . ' pendientes' : 'Sin citas pendientes' }}</p>
        </a>
    </div>
    @elseif(auth()->user()?->hasRole('root'))
        {{-- Vista Root --}}
        <div style="margin-bottom: 1.5rem;">
            @livewire(\App\Filament\Widgets\CentroStatsWidget::class)
        </div>
    @endif

    {{-- Fila 3: Calendario + Gráfico de Citas --}}
    @if(tenancy()->initialized)
    <div class="dash-bottom-row" style="display: grid; grid-template-columns: 3fr 2fr; gap: 1.5rem;">
        {{-- Calendario --}}
        <div>
            @livewire(\App\Filament\Widgets\CalendarioCitasWidget::class)
        </div>

        {{-- Gráfico Estado de Citas --}}
        <div>
            @livewire(\App\Filament\Widgets\CitasPieChart::class)
        </div>
    </div>
    @endif

    {{-- Widget de recetario para médicos --}}
    @if(tenancy()->initialized && auth()->user()?->medico)
    <div style="margin-top: 1.5rem;">
        @livewire(\App\Filament\Widgets\RecetarioStatsOverview::class)
    </div>
    @endif

    <style>
        /* Ocultar el heading default de Filament */
        .fi-page-header {
            display: none !important;
        }

        /* Dark mode para tarjetas de stats */
        .dark .dash-stat-card {
            background: #1f2937 !important;
            border-color: #374151 !important;
        }
        .dark .dash-stat-card p[style*="color: #1f2937"] {
            color: #f9fafb !important;
        }
        .dark .dash-stat-card span[style*="color: #6b7280"] {
            color: #9ca3af !important;
        }

        /* Hover en tarjetas */
        .dash-stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }

        /* Responsive: en pantallas pequeñas apilar */
        @media (max-width: 768px) {
            .dash-row-3 {
                grid-template-columns: 1fr !important;
            }
            .dash-row-4 {
                grid-template-columns: 1fr !important;
            }
            .dash-bottom-row {
                grid-template-columns: 1fr !important;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .dash-row-4 {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        /* Dark mode para título */
        .dark h1[style*="color: #1f2937"] {
            color: #f9fafb !important;
        }
    </style>
</x-filament-panels::page>
