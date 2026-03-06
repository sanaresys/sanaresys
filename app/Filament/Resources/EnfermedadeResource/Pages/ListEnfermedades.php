<?php

namespace App\Filament\Resources\EnfermedadeResource\Pages;

use App\Filament\Resources\EnfermedadeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnfermedades extends ListRecords
{
    protected static string $resource = EnfermedadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
