<?php

namespace App\Filament\Resources\EnfermedadesPacienteResource\Pages;

use App\Filament\Resources\EnfermedadesPacienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnfermedadesPacientes extends ListRecords
{
    protected static string $resource = EnfermedadesPacienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
