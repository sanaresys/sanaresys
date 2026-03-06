<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\CentroStatsWidget;
use App\Filament\Widgets\CalendarioCitasWidget;
use App\Filament\Widgets\CitasPieChart;
use App\Filament\Widgets\RecetarioStatsOverview;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            RecetarioStatsOverview::class,
            CentroStatsWidget::class,
            CalendarioCitasWidget::class,
            CitasPieChart::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 1; // Una sola columna para mejor control del layout
    }
}