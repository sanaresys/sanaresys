<?php

namespace App\Filament\Resources\Examenes\ExamenesResource\Pages;

use App\Filament\Resources\Examenes\ExamenesResource;
use App\Filament\Resources\Examenes\Widgets\ExamenesStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExamenes extends ListRecords
{
    protected static string $resource = ExamenesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No agregar botón de crear - los exámenes solo se crean desde consultas
        ];
    }

    public function getTitle(): string
    {
        return 'Reportes de Exámenes';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ExamenesStatsWidget::class,
        ];
    }
}
