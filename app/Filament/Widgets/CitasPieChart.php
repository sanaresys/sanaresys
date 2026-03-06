<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Citas;
use Illuminate\Support\Facades\Auth;

class CitasPieChart extends ChartWidget
{
    protected static ?string $heading = '📊 Estado de Citas Hoy';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;
    protected static ?string $maxHeight = '250px';
    
    public static function canView(): bool
    {
        // Solo mostrar si hay tenant activo
        return tenancy()->initialized;
    }
    
    protected function getData(): array
    {
        $user = Auth::user();
        
        $query = Citas::query();
        
        // En multi-tenant, el contexto ya define el centro
        // No es necesario filtrar por centro_id
        
        // Solo citas de hoy para simplificar
        $query->whereDate('fecha', today());
        
        $pendientes = $query->clone()->where('estado', 'Pendiente')->count();
        $confirmadas = $query->clone()->where('estado', 'Confirmado')->count();
        $realizadas = $query->clone()->where('estado', 'Realizada')->count();
        $canceladas = $query->clone()->where('estado', 'Cancelado')->count();
        
        return [
            'datasets' => [
                [
                    'label' => 'Citas de Hoy',
                    'data' => [$pendientes, $confirmadas, $realizadas, $canceladas],
                    'backgroundColor' => [
                        '#fbbf24',  // Amarillo para pendientes
                        '#22c55e',  // Verde para confirmadas  
                        '#3b82f6',  // Azul para realizadas
                        '#ef4444',  // Rojo para canceladas
                    ],
                    'borderColor' => [
                        '#f59e0b',
                        '#16a34a',
                        '#2563eb',
                        '#dc2626',
                    ],
                    'borderWidth' => 2,
                    'hoverOffset' => 6,
                ],
            ],
            'labels' => [
                "⏳ Pendientes ({$pendientes})",
                "✅ Confirmadas ({$confirmadas})", 
                "✨ Realizadas ({$realizadas})",
                "❌ Canceladas ({$canceladas})"
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
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
                        'padding' => 12,
                        'font' => [
                            'size' => 10
                        ]
                    ]
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleColor' => 'white',
                    'bodyColor' => 'white',
                    'borderColor' => 'rgba(255, 255, 255, 0.1)',
                    'borderWidth' => 1
                ]
            ],
            'cutout' => '60%',
            'animation' => [
                'animateRotate' => true,
                'animateScale' => false
            ]
        ];
    }
}