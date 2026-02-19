<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Receta;
use Illuminate\Support\Facades\Auth;

class RecetarioStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        // Solo mostrar si hay tenant activo y usuario es médico
        return tenancy()->initialized && Auth::user()?->medico !== null;
    }

    protected function getHeading(): string
    {
        return 'Estadísticas del Recetario';
    }

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = Auth::user();
        if (!$user->medico) {
            return [];
        }

        $query = Receta::query()->where('medico_id', $user->medico->id);
        
        // Clonamos la consulta para cada estadística para evitar interferencias
        $totalQuery = clone $query;
        $mesQuery = clone $query;
        $hoyQuery = clone $query;
        
        return [
            Stat::make('Total Recetas', $totalQuery->count() ?: 0)
                ->icon('heroicon-o-document-text')
                ->color('primary'),
                
            Stat::make('Este Mes', $mesQuery->whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])->count() ?: 0)
                ->icon('heroicon-o-clock')
                ->color('primary'),
                
            Stat::make('Hoy', $hoyQuery->whereDate('created_at', now())->count() ?: 0)
                ->icon('heroicon-o-calendar')
                ->color('primary'),
        ];
    }
}
