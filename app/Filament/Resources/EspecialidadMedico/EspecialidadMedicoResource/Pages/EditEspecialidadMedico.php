<?php

namespace App\Filament\Resources\EspecialidadMedico\EspecialidadMedicoResource\Pages;

use App\Filament\Resources\EspecialidadMedico\EspecialidadMedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEspecialidadMedico extends EditRecord
{
    protected static string $resource = EspecialidadMedicoResource::class;

    protected static ?string $title = 'Editar Especialidad'; // Título personalizado

    protected function getHeaderActions(): array
    {
        return []; // Eliminamos todas las acciones del header
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Guardar cambios')
                ->submit('save')
                ->icon('heroicon-o-check-circle')
                ->color('primary'),
                
            Actions\Action::make('cancel')
                ->label('Cancelar')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Especialidad médica actualizada exitosamente';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}