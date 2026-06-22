<?php

namespace App\Filament\Resources\CAIAutorizaciones\CAIAutorizacionesResource\Pages;

use App\Filament\Resources\CAIAutorizaciones\CAIAutorizacionesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCAIAutorizaciones extends ListRecords
{
    protected static string $resource = CAIAutorizacionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
