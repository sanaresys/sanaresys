<?php

namespace App\Filament\Resources\Persona\PersonaResource\Pages;

use App\Filament\Resources\Persona\PersonaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPersonas extends ListRecords
{
    protected static string $resource = PersonaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
