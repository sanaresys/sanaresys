<?php

namespace App\Filament\Resources\Examenes\ExamenesResource\Pages;

use App\Filament\Resources\Examenes\ExamenesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class EditExamenes extends EditRecord
{
    protected static string $resource = ExamenesResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);
        
        // Verificar permisos
        if (!Gate::allows('update', $this->record)) {
            Notification::make()
                ->title('Sin permisos')
                ->body('No tienes permisos para editar este examen.')
                ->danger()
                ->send();
            
            $this->redirect(static::getResource()::getUrl('index'));
            return;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => Auth::user()->can('delete', $this->record)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si se sube una imagen, actualizar estado y fecha
        if (isset($data['imagen_resultado']) && $data['imagen_resultado']) {
            $data['estado'] = 'Completado';
            $data['fecha_completado'] = now();
        }

        return $data;
    }
}
