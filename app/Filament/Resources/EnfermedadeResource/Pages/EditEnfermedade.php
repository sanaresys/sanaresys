<?php

namespace App\Filament\Resources\EnfermedadeResource\Pages;

use App\Filament\Resources\EnfermedadeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnfermedade extends EditRecord
{
    protected static string $resource = EnfermedadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
