<?php

namespace App\Filament\Resources\Impuestos\ImpuestosResource\Pages;

use App\Filament\Resources\Impuestos\ImpuestosResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImpuestos extends ListRecords
{
    protected static string $resource = ImpuestosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
