<?php

namespace App\Filament\Resources\TipoPagos\TipoPagosResource\Pages;

use App\Filament\Resources\TipoPagos\TipoPagosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoPagos extends EditRecord
{
    protected static string $resource = TipoPagosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
