<?php

namespace App\Filament\Resources\Impuestos\ImpuestosResource\Pages;

use App\Filament\Resources\Impuestos\ImpuestosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditImpuestos extends EditRecord
{
    protected static string $resource = ImpuestosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
