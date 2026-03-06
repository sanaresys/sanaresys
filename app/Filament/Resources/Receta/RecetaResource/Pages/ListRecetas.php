<?php

namespace App\Filament\Resources\Receta\RecetaResource\Pages;

use App\Filament\Resources\Receta\RecetaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRecetas extends ListRecords
{
    protected static string $resource = RecetaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
