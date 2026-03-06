<?php

namespace App\Filament\Resources\Persona\PersonaResource\Pages;

use App\Filament\Resources\Persona\PersonaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPersona extends EditRecord
{
    protected static string $resource = PersonaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
