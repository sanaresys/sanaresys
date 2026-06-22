<?php

namespace App\Filament\Resources\Especialidad\EspecialidadResource\Pages;

use App\Filament\Resources\Especialidad\EspecialidadResource;
use Filament\Actions;
use Filament\Actions\Action; 
use Filament\Resources\Pages\CreateRecord;

class CreateEspecialidad extends CreateRecord
{
    protected static string $resource = EspecialidadResource::class;
    protected static ?string $title = 'Crear Especialidad'; 


    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Crear Especialidad')
                ->submit('create')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->keyBindings(['mod+s']) // Permite guardar con Ctrl+S o Cmd+
                ->action(function () {
                    $this->create();
                    $this->redirect($this->getRedirectUrl());
                }),
                
            Actions\Action::make('cancel')
                ->label('Cancelar')
                ->icon('heroicon-o-x-mark')

                ->url($this->getResource()::getUrl('index'))
                ->color('danger')
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Especialidad creada exitosamente';
    }
}