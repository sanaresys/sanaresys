<?php

namespace App\Filament\Resources\CAICorrelativos\CAICorrelativosResource\Pages;

use App\Filament\Resources\CAICorrelativos\CAICorrelativosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCAICorrelativos extends EditRecord
{
    protected static string $resource = CAICorrelativosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
