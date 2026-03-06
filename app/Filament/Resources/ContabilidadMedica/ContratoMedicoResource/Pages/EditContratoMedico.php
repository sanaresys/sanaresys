<?php

namespace App\Filament\Resources\ContabilidadMedica\ContratoMedicoResource\Pages;

use App\Filament\Resources\ContabilidadMedica\ContratoMedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContratoMedico extends EditRecord
{
    protected static string $resource = ContratoMedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
