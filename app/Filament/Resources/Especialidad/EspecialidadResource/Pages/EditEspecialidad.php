<?php

namespace App\Filament\Resources\Especialidad\EspecialidadResource\Pages;

use App\Filament\Resources\Especialidad\EspecialidadResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditEspecialidad extends EditRecord
{
    protected static string $resource = EspecialidadResource::class;

    protected static ?string $title = 'Editar Especialidad'; // Título personalizado en la página


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Especialidad actualizada correctamente';
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label('Guardar cambios')
            ->submit('save')
            ->keyBindings(['mod+s']);
            
    }


        protected function getCancelFormAction(): Action
    {
        return Action::make('Cancelar')
            ->label('Cancelar')
            ->submit('cancel')
            ->icon('heroicon-o-x-mark')
            ->color('danger');
    }
}