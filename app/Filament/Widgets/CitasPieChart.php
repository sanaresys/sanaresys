<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Citas;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class CitasPieChart extends ChartWidget
{
    protected static ?string $heading = 'Estado de Citas Hoy';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';
    protected static ?string $pollingInterval = '15s';

    #[On('refreshChart')]
    public function forceUpdateChart(): void
    {
        $this->cachedData = null;
        $this->dataChecksum = '';
        $this->dispatch('updateChartData', data: $this->getCachedData());
    }

    public function updateChartData(): void
    {
        $this->cachedData = null;
        $this->dataChecksum = '';
        parent::updateChartData();
    }

    public static function canView(): bool
    {
        return tenancy()->initialized;
    }

    protected function getData(): array
    {
        $query = Citas::query()->whereDate('fecha', today());

        $pendientes = $query->clone()->where('estado', 'Pendiente')->count();
        $confirmadas = $query->clone()->where('estado', 'Confirmado')->count();
        $realizadas = $query->clone()->where('estado', 'Realizado')->count();
        $canceladas = $query->clone()->where('estado', 'Cancelado')->count();

        return [
            'datasets' => [
                [
                    'label' => "Pendientes ({$pendientes})",
                    'data' => [0, $pendientes * 0.3, $pendientes * 0.7, $pendientes, $pendientes * 0.8, $pendientes * 0.4, 0],
                    'backgroundColor' => 'rgba(251, 146, 60, 0.5)',
                    'borderColor' => 'rgb(251, 146, 60)',
                    'pointBackgroundColor' => 'rgb(251, 146, 60)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 0,
                ],
                [
                    'label' => "Confirmadas ({$confirmadas})",
                    'data' => [0, $confirmadas * 0.5, $confirmadas, $confirmadas * 0.9, $confirmadas * 0.6, $confirmadas * 0.2, 0],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 0,
                ],
                [
                    'label' => "Realizadas ({$realizadas})",
                    'data' => [0, $realizadas * 0.2, $realizadas * 0.5, $realizadas * 0.8, $realizadas, $realizadas * 0.6, 0],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'pointBackgroundColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 0,
                ],
                [
                    'label' => "Canceladas ({$canceladas})",
                    'data' => [0, $canceladas * 0.1, $canceladas * 0.3, $canceladas * 0.5, $canceladas * 0.7, $canceladas, 0],
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'pointBackgroundColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 0,
                ],
            ],
            'labels' => ['', '1', '', '2', '', '3', ''],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 15,
                        'font' => ['size' => 11],
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => ['display' => false],
                    'ticks' => ['font' => ['size' => 11]],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'grid' => ['color' => 'rgba(0, 0, 0, 0.05)'],
                    'ticks' => ['font' => ['size' => 11]],
                ],
            ],
        ];
    }
}