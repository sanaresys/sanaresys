<?php

namespace App\Filament\Resources\ExamenesResource\Pages;
use App\Filament\Resources\ExamenesResource\ExamenesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExamenes extends ListRecords
{
    protected static string $resource = ExamenesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
