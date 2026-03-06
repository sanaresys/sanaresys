<?php

namespace App\Filament\Resources\Consultas\ConsultasResource\Pages;

use App\Filament\Resources\Consultas\ConsultasResource;
use App\Filament\Resources\Receta\RecetaResource;
use App\Filament\Resources\Consultas\Widgets\HistorialExamenes;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use App\Models\User;



class EditConsultas extends EditRecord
{
    protected static string $resource = ConsultasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Botón para crear nueva receta directamente
            Actions\Action::make('crear_receta')
                ->label(' Nueva Receta')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->url(function () {
                    return \App\Filament\Resources\Receta\RecetaResource::getUrl('create-simple') .
                           '?paciente_id=' . $this->record->paciente_id .
                           '&consulta_id=' . $this->record->id .
                           '&medico_id=' . $this->record->medico_id;
                })
                ->openUrlInNewTab(false),

            Actions\ViewAction::make()
                ->label(' Ver Consulta')
                ->color('info'),

            Actions\DeleteAction::make()
                ->label(' Eliminar')
                ->requiresConfirmation()
                ->modalHeading('Eliminar Consulta')
                ->modalDescription('¿Estás seguro de que deseas eliminar esta consulta? Esta acción se puede deshacer.')
                ->modalSubmitActionLabel('Sí, eliminar')
                ->color('danger'),

            Actions\Action::make('back')
                ->label(' Volver')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Consulta actualizada')
            ->body('Los cambios han sido guardados exitosamente.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Lógica adicional antes de guardar
        return $data;
    }

    protected function afterSave(): void
    {
        // Lógica adicional después de guardar
        Log::info('Consulta actualizada', [
            'consulta_id' => $this->record->id,
            'updated_by' => \Illuminate\Support\Facades\Auth::id(),
            'changes' => $this->record->getChanges(),
            'recetas_count' => $this->record->recetas()->count(),
        ]);

        // Mostrar notificación adicional si se modificaron recetas
        $recetasCount = $this->record->recetas()->count();
        if ($recetasCount > 0) {
            Notification::make()
                ->success()
                ->title('Recetas actualizadas')
                ->body("Se gestionaron {$recetasCount} receta(s) asociada(s) a esta consulta.")
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label(' Guardar Cambios')
                ->color('success')
                ->icon('heroicon-o-check'),

            $this->getCancelFormAction()
                ->label(' Cancelar')
                ->color('gray')
                ->icon('heroicon-o-x-mark')
                ->url($this->getResource()::getUrl('view', ['record' => $this->record])),

            // Botón adicional para ir a ver la consulta
            Actions\Action::make('view_after_save')
                ->label(' Ver Consulta')
                ->color('info')
                ->url($this->getResource()::getUrl('view', ['record' => $this->record]))
                ->icon('heroicon-o-eye'),
        ];
    }

    protected function configureDeleteAction(Actions\DeleteAction $action): void
    {
        $action
            ->after(function () {
                Notification::make()
                    ->success()
                    ->title('Consulta eliminada')
                    ->body('La consulta ha sido enviada a la papelera.')
                    ->send();
            });
    }

    protected function getFooterWidgets(): array
    {
        return [
            HistorialExamenes::class,
        ];
    }
}
