<?php

namespace App\Filament\Resources\CentrosMedico\CentrosMedicoResource\Pages;

use App\Filament\Resources\CentrosMedico\CentrosMedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCentrosMedicos extends ListRecords
{
    protected static string $resource = CentrosMedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
