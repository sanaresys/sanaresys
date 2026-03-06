<?php

namespace App\Filament\Resources\Citas\CitasResource\Pages;

use App\Filament\Resources\Citas\CitasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;
use Filament\Notifications\Notification;

class EditCitas extends EditRecord
{
    protected static string $resource = CitasResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);
        
        // Verificar si el usuario tiene permisos para editar esta cita
        if (!Gate::allows('update', $this->record)) {
            Notification::make()
                ->title('Sin permisos')
                ->body('No tienes permisos para editar esta cita.')
                ->danger()
                ->send();
            
            $this->redirect(static::getResource()::getUrl('index'));
            return;
        }
    }

    /**
     * Después de guardar cambios, redirige al listado de Citas.
     */
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
