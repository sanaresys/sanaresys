<?php

namespace App\Filament\Resources\Nacionalidad\NacionalidadResource\Pages;

use App\Filament\Resources\Nacionalidad\NacionalidadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNacionalidads extends ListRecords
{
    protected static string $resource = NacionalidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
