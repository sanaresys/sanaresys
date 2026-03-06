<?php

namespace App\Filament\Resources\Examenes\ExamenesResource\Pages;

use App\Filament\Resources\Examenes\ExamenesResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExamenes extends ViewRecord
{
    protected static string $resource = ExamenesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => auth()->user()->can('update', $this->record)),
        ];
    }
}
