<?php

namespace App\Filament\Resources\Receta\RecetaResource\Pages;

use App\Filament\Resources\Receta\RecetaResource;
use App\Filament\Resources\Consultas\ConsultasResource;
use App\Models\Pacientes;
use App\Models\Consulta;
use App\Models\Medico;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateReceta extends CreateRecord
{
    protected static string $resource = RecetaResource::class;

    public function mount(): void
    {
        parent::mount();

        // Precargar datos desde URL si están disponibles
        $data = [];

        // Precargar paciente
        if (request()->has('paciente_id')) {
            $pacienteId = request()->get('paciente_id');
            $paciente = Pacientes::with('persona')->find($pacienteId);

            if ($paciente && $paciente->persona) {
                $data['paciente_id'] = $pacienteId;

                // Mostrar información del paciente precargado
                Notification::make()
                    ->title('Paciente precargado')
                    ->body("Creando receta para: {$paciente->persona->nombre_completo}")
                    ->success()
                    ->send();
            }
        }

        // Precargar médico
        if (request()->has('medico_id')) {
            $medicoId = request()->get('medico_id');
            $medico = Medico::withoutGlobalScopes()->with('persona')->find($medicoId);

            if ($medico && $medico->persona) {
                $data['medico_id'] = $medicoId;
            }
        }

        // Precargar consulta
        if (request()->has('consulta_id')) {
            $consultaId = request()->get('consulta_id');
            $consulta = Consulta::find($consultaId);

            if ($consulta) {
                $data['consulta_id'] = $consultaId;

                // Mostrar información de la consulta
                Notification::make()
                    ->title('Consulta asociada')
                    ->body("Esta receta será asociada a la Consulta #{$consultaId}")
                    ->info()
                    ->send();
            }
        }

        // Precargar fecha actual
        $data['fecha_receta'] = now()->format('Y-m-d');

        // Llenar el formulario con los datos precargados
        if (!empty($data)) {
            $this->form->fill($data);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Volver al listado')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),

            // Botón para volver a la consulta si venimos de ahí
            Actions\Action::make('back_to_consulta')
                ->label('Volver a Consulta')
                ->url(function () {
                    if (request()->has('consulta_id')) {
                        $consultaId = request()->get('consulta_id');
                        return \App\Filament\Resources\Consultas\ConsultasResource::getUrl('view', ['record' => $consultaId]);
                    }
                    return null;
                })
                ->color('info')
                ->icon('heroicon-o-arrow-left')
                ->visible(fn () => request()->has('consulta_id')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // Si venimos de una consulta, regresar a la vista de esa consulta
        if (request()->has('consulta_id')) {
            $consultaId = request()->get('consulta_id');
            return \App\Filament\Resources\Consultas\ConsultasResource::getUrl('view', ['record' => $consultaId]);
        }

        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Receta creada exitosamente';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Multi-tenant: centro_id no es necesario
        return $data;
    }
}
