<?php

namespace App\Filament\Resources\CentrosMedico\CentrosMedicoResource\Pages;

use App\Filament\Resources\CentrosMedico\CentrosMedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCentrosMedico extends EditRecord
{
    protected static string $resource = CentrosMedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
