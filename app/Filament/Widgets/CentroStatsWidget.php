<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Tenant;
use App\Models\Centros_Medico;
use Filament\Support\Colors\Color;

class CentroStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected string|int|array $columnSpan = 'full';
    protected static ?int $sort = 1;

    public function getGridColumns(): array
    {
        return [
            'default' => 2,
            'sm' => 2,
            'md' => 3,
            'lg' => 4,
        ];
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $stats = [];

        if ($user && $user->hasRole('root')) {
            // Vista global para root - Accediendo a la base de datos central
            $totalCentros = Centros_Medico::on('mysql')->count();
            
            $stats[] = Stat::make('🔧 Vista Global', 'Super Administrador')
                ->description('Acceso a todos los centros')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color(Color::Red);

            $stats[] = Stat::make('🏥 Centros', number_format($totalCentros))
                ->description('Centros médicos activos')
                ->descriptionIcon('heroicon-m-building-office')
                ->color(Color::Green)
                ->url('/admin/centros-medico/centros-medicos');

            $stats[] = Stat::make('👥 Sistema', 'Multi-Tenant')
                ->description('Base de datos por centro')
                ->descriptionIcon('heroicon-m-server-stack')
                ->color(Color::Blue);

            $stats[] = Stat::make('🔐 Tenants', number_format($totalCentros))
                ->description('Bases de datos aisladas')
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color(Color::Purple);

        } else {
            // Verificar que hay un tenant activo
            if (!tenancy()->initialized) {
                return [
                    Stat::make('⚠️ No Conectado', 'Sin Tenant')
                        ->description('Por favor contacte al administrador')
                        ->descriptionIcon('heroicon-m-exclamation-triangle')
                        ->color(Color::Orange)
                ];
            }

            // Estadísticas del tenant actual (centro específico)
            // Multi-tenant: los datos ya están filtrados por el tenant automáticamente
            
            $pacientesCount = \App\Models\Pacientes::count();
            $medicosCount = \App\Models\Medico::count();
            
            $citasHoy = \App\Models\Citas::whereDate('fecha', today());
            $citasPendientes = (clone $citasHoy)->where('estado', 'Pendiente')->count();
            $citasConfirmadas = (clone $citasHoy)->where('estado', 'Confirmado')->count();
            $totalCitasHoy = $citasHoy->count();

            // Obtener nombre del centro actual desde la base de datos central
            $tenant = tenancy()->tenant;
            $centroNombre = 'Centro Médico';
            
            if ($tenant) {
                $centro = Centros_Medico::on('mysql')->find($tenant->centro_id);
                $centroNombre = $centro ? $centro->nombre_centro : 'Centro ' . ($tenant->centro_id ?? $tenant->id);
            }

            // Estadísticas del tenant actual
            $stats[] = Stat::make($centroNombre, 'Centro Actual')
                ->description('Datos del tenant activo')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color(Color::Emerald);

            $stats[] = Stat::make('Pacientes', number_format($pacientesCount))
                ->description('Total registrados')
                ->descriptionIcon('heroicon-m-users')
                ->color(Color::Blue)
                ->url('/admin/pacientes');

            $stats[] = Stat::make('Médicos', number_format($medicosCount))
                ->description('Personal médico')
                ->descriptionIcon('heroicon-m-user-group')
                ->color(Color::Purple)
                ->url('/admin/medico/medicos');

            $stats[] = Stat::make('Citas Hoy', number_format($totalCitasHoy))
                ->description($citasPendientes > 0 ? "{$citasPendientes} pendientes" : ($citasConfirmadas > 0 ? "{$citasConfirmadas} confirmadas" : "Sin citas"))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($totalCitasHoy > 5 ? Color::Orange : ($totalCitasHoy > 0 ? Color::Green : Color::Gray))
                ->url('/admin/citas/citas');
        }

        return $stats;
    }
}
