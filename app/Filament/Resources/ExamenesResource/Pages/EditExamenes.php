<?php

namespace App\Filament\Resources\ExamenesResource\Pages;
use App\Filament\Resources\ExamenesResource\ExamenesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExamenes extends EditRecord
{
    protected static string $resource = ExamenesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
