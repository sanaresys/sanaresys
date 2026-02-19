<?php

namespace App\Filament\Widgets;

use App\Models\CuentasPorCobrar;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CuentasPorCobrarStatsWidget extends BaseWidget
{
    public static function canView(): bool
    {
        // Solo mostrar si hay tenant activo
        return tenancy()->initialized;
    }

    protected function getStats(): array
    {
        // Multi-tenant: los datos ya están filtrados por el tenant automáticamente
        
        // Estadísticas de cuentas pendientes
        $cuentasPendientes = CuentasPorCobrar::where('saldo_pendiente', '>', 0)
            ->whereIn('estado_cuentas_por_cobrar', ['PENDIENTE', 'PARCIAL', 'VENCIDA'])
            ->count();
            
        $totalSaldoPendiente = CuentasPorCobrar::where('saldo_pendiente', '>', 0)
            ->whereIn('estado_cuentas_por_cobrar', ['PENDIENTE', 'PARCIAL', 'VENCIDA'])
            ->sum('saldo_pendiente');
            
        $cuentasVencidas = CuentasPorCobrar::where('fecha_vencimiento', '<', now())
            ->where('saldo_pendiente', '>', 0)
            ->whereIn('estado_cuentas_por_cobrar', ['PENDIENTE', 'PARCIAL', 'VENCIDA'])
            ->count();
            
        $totalVencido = CuentasPorCobrar::where('fecha_vencimiento', '<', now())
            ->where('saldo_pendiente', '>', 0)
            ->whereIn('estado_cuentas_por_cobrar', ['PENDIENTE', 'PARCIAL', 'VENCIDA'])
            ->sum('saldo_pendiente');

        return [
            Stat::make('Cuentas Pendientes', $cuentasPendientes)
                ->description('Total de facturas con saldo pendiente')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
                
            Stat::make('Saldo Total Pendiente', 'L ' . number_format($totalSaldoPendiente, 2))
                ->description('Monto total por cobrar')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),
                
            Stat::make('Cuentas Vencidas', $cuentasVencidas)
                ->description('L ' . number_format($totalVencido, 2) . ' vencido')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($cuentasVencidas > 0 ? 'danger' : 'success'),
        ];
    }
}
