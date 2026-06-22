<?php

namespace App\Filament\Resources\EnfermedadeResource\Pages;

use App\Filament\Resources\EnfermedadeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEnfermedade extends ViewRecord
{
    protected static string $resource = EnfermedadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
