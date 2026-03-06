<?php

namespace App\Filament\Resources\TipoPagos\TipoPagosResource\Pages;

use App\Filament\Resources\TipoPagos\TipoPagosResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoPagos extends ListRecords
{
    protected static string $resource = TipoPagosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
