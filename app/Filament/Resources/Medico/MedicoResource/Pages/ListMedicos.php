<?php

namespace App\Filament\Resources\Medico\MedicoResource\Pages;

use App\Filament\Resources\Medico\MedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedicos extends ListRecords
{
    protected static string $resource = MedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuevo Médico') // Cambiado a "Nuevo Médico",
                ->icon('heroicon-o-plus') // Icono de más
                ->color('primary') // Color consistente con el tema
                ->tooltip('Crear un nuevo médico'), // Tooltip para mayor claridad
        ];
    }



}