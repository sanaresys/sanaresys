<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\CentroStatsWidget;
use App\Filament\Widgets\CalendarioCitasWidget;
use App\Filament\Widgets\CitasPieChart;
use App\Filament\Widgets\CuentasPorCobrarStatsWidget;
use App\Filament\Widgets\RecetarioStatsOverview;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [];
    }

    public function getVisibleWidgets(): array
    {
        return [];
    }

    public function getHeading(): string
    {
        return '';
    }

    public function getColumns(): int|string|array
    {
        return 1;
    }
}