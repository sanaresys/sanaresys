<?php

namespace App\Filament\Resources\FacturaDetalles\FacturaDetallesResource\Pages;

use App\Filament\Resources\FacturaDetalles\FacturaDetallesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFacturaDetalles extends EditRecord
{
    protected static string $resource = FacturaDetallesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
