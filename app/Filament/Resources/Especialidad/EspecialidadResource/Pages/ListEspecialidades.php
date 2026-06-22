<?php

namespace App\Filament\Resources\Especialidad\EspecialidadResource\Pages;

use App\Filament\Resources\Especialidad\EspecialidadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEspecialidades extends ListRecords
{
    protected static string $resource = EspecialidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nueva Especialidad')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->tooltip('Crear una nueva especialidad'),
        ];
    }
}
