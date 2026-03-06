<?php

namespace App\Filament\Resources\ContabilidadMedica\NominaResource\Pages;

use App\Filament\Resources\ContabilidadMedica\NominaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNominas extends ListRecords
{
    protected static string $resource = NominaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nueva Nómina')
                ->icon('heroicon-o-plus'),
        ];
    }
}
