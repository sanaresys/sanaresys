<?php

namespace App\Filament\Resources\EspecialidadMedico\EspecialidadMedicoResource\Pages;

use App\Filament\Resources\EspecialidadMedico\EspecialidadMedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEspecialidadMedico extends CreateRecord
{
    protected static string $resource = EspecialidadMedicoResource::class;

    protected static ?string $title = 'Crear Especialidad - Médico';

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Crear Especialidad')
                ->submit('create')
                ->action(function () {
                    $this->create();
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->color('primary')
                ->icon('heroicon-o-plus'),
                
            Actions\Action::make('cancel')
                ->label('Cancelar')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Médico-Especialidad creada exitosamente';
    }

    protected function getHeaderActions(): array
    {
        return []; // Elimina acciones del header
    }
}