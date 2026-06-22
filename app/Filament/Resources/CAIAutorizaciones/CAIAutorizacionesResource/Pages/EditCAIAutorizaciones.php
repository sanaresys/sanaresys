<?php

namespace App\Filament\Resources\CAIAutorizaciones\CAIAutorizacionesResource\Pages;

use App\Filament\Resources\CAIAutorizaciones\CAIAutorizacionesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCAIAutorizaciones extends EditRecord
{
    protected static string $resource = CAIAutorizacionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
