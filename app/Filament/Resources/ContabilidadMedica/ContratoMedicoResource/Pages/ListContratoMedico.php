<?php

namespace App\Filament\Resources\ContabilidadMedica\ContratoMedicoResource\Pages;

use App\Filament\Resources\ContabilidadMedica\ContratoMedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContratoMedico extends ListRecords
{
    protected static string $resource = ContratoMedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
