<?php

namespace App\Filament\Resources\Descuentos\DescuentosResource\Pages;

use App\Filament\Resources\Descuentos\DescuentosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDescuentos extends EditRecord
{
    protected static string $resource = DescuentosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
