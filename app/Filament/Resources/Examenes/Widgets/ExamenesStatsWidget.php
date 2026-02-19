<?php

namespace App\Filament\Resources\Examenes\Widgets;

use App\Models\Examenes;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ExamenesStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $query = Examenes::query();

        // Multi-tenant: filtrar por rol pero no por centro_id
        if ($user->roles->contains('name', 'root') || $user->roles->contains('name', 'administrador')) {
            $query->withoutGlobalScopes();
        } elseif ($user->roles->contains('name', 'medico')) {
            $query->where('medico_id', $user->medico?->id);
        }

        $total = $query->count();
        $solicitados = (clone $query)->where('estado', 'Solicitado')->count();
        $completados = (clone $query)->where('estado', 'Completado')->count();
        $noPresentados = (clone $query)->where('estado', 'No presentado')->count();

        $stats = [
            Stat::make('Total de Exámenes', $total)
                ->description('Todos los exámenes')
                ->descriptionIcon('heroicon-o-clipboard-document-check')
                ->color('primary'),

            Stat::make('Exámenes Solicitados', $solicitados)
                ->description('Pendientes de realizar')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Exámenes Completados', $completados)
                ->description('Con resultados subidos')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('No Presentados', $noPresentados)
                ->description('Exámenes no realizados')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
        ];

        return $stats;
    }
}
