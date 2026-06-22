<?php

namespace App\Filament\Resources\CitasResource\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Citas;

class CitasPieChart extends ChartWidget
{
    protected static ?string $heading = 'Estado de las Citas';
    protected static ?int $sort = 2;
    
    protected function getData(): array
    {
        // Obtener las citas agrupadas por estado
        $pendientes = Citas::where('estado', 'Pendiente')->count();
        $confirmadas = Citas::where('estado', 'Confirmado')->count();
        $canceladas = Citas::where('estado', 'Cancelado')->count();
        $realizadas = Citas::where('estado', 'Realizada')->count();
        
        return [
            'datasets' => [
                [
                    'label' => 'Citas por Estado',
                    'data' => [$pendientes, $confirmadas, $canceladas, $realizadas],
                    'backgroundColor' => [
                        '#fbbf24', // Amarillo para pendientes
                        '#10b981', // Verde para confirmadas
                        '#ef4444', // Rojo para canceladas
                        '#3b82f6', // Azul para realizadas
                    ],
                    'borderColor' => [
                        '#f59e0b',
                        '#059669',
                        '#dc2626',
                        '#2563eb',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Pendientes', 'Confirmadas', 'Canceladas', 'Realizadas'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Distribución de Citas por Estado',
                ],
            ],
        ];
    }
}
