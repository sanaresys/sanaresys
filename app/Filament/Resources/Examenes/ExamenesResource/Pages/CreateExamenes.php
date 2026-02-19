<?php

namespace App\Filament\Resources\Examenes\ExamenesResource\Pages;

use App\Filament\Resources\Examenes\ExamenesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateExamenes extends CreateRecord
{
    protected static string $resource = ExamenesResource::class;

    protected static ?string $title = 'Nuevo Examen Médico';

    protected function getRedirectUrl(): string
    {
        // Redirigir de vuelta a la vista de la consulta si viene desde ahí
        $consultaId = request()->get('consulta_id') ?? $this->record->consulta_id;
        if ($consultaId) {
            return \App\Filament\Resources\Consultas\ConsultasResource::getUrl('view', ['record' => $consultaId]);
        }
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('🔬 Examen médico creado')
            ->body('El examen ha sido solicitado exitosamente y se ha asociado a la consulta.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar datos del usuario autenticado y la consulta
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Datos del usuario y centro
        $data['centro_id'] = $user->centro_id;
        $data['medico_id'] = $data['medico_id'] ?? $user->medico?->id ?? request()->get('medico_id');
        
        // Datos de la consulta (vienen desde la URL)
        $data['consulta_id'] = $data['consulta_id'] ?? request()->get('consulta_id');
        $data['paciente_id'] = $data['paciente_id'] ?? request()->get('paciente_id');
        
        // Estado por defecto
        $data['estado'] = 'Solicitado';
        
        // La fecha de completado se asigna cuando se sube la imagen
        $data['fecha_completado'] = null;

        Log::info('Creando examen médico', [
            'consulta_id' => $data['consulta_id'],
            'paciente_id' => $data['paciente_id'],
            'medico_id' => $data['medico_id'],
            'tipo_examen' => $data['tipo_examen'],
            'centro_id' => $data['centro_id'],
        ]);

        return $data;
    }

    public function mount(): void
    {
        parent::mount();
        
        // Pre-llenar datos si viene de una consulta
        $consultaId = request()->get('consulta_id');
        $pacienteId = request()->get('paciente_id');
        $medicoId = request()->get('medico_id');
        
        if ($consultaId && $pacienteId) {
            // Multi-tenant: centro_id no es necesario
            $formData = [
                'consulta_id' => $consultaId,
                'paciente_id' => $pacienteId,
                'medico_id' => $medicoId,
                'estado' => 'Solicitado',
            ];
            
            $this->form->fill($formData);
            
            Log::info('Formulario de examen pre-llenado desde consulta', [
                'consulta_id' => $consultaId,
                'paciente_id' => $pacienteId,
                'medico_id' => $medicoId,
            ]);
        }
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('✅ Solicitar Examen')
                ->color('success'),

            $this->getCreateAnotherFormAction()
                ->label('➕ Solicitar y Agregar Otro')
                ->color('info'),

            $this->getCancelFormAction()
                ->label('❌ Cancelar')
                ->color('gray')
                ->url(function () {
                    $consultaId = request()->get('consulta_id');
                    if ($consultaId) {
                        return \App\Filament\Resources\Consultas\ConsultasResource::getUrl('view', ['record' => $consultaId]);
                    }
                    return $this->getResource()::getUrl('index');
                }),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('← Volver a la Consulta')
                ->url(function () {
                    $consultaId = request()->get('consulta_id');
                    if ($consultaId) {
                        return \App\Filament\Resources\Consultas\ConsultasResource::getUrl('view', ['record' => $consultaId]);
                    }
                    return $this->getResource()::getUrl('index');
                })
                ->icon('heroicon-o-arrow-left')
                ->color('gray'),
        ];
    }

    public function getSubheading(): ?string
    {
        $pacienteId = request()->get('paciente_id');
        $consultaId = request()->get('consulta_id');
        
        if ($pacienteId && $consultaId) {
            $paciente = \App\Models\Pacientes::with('persona')->find($pacienteId);
            if ($paciente && $paciente->persona) {
                return "Solicitando examen para: {$paciente->persona->nombre_completo} | Consulta #{$consultaId}";
            }
        }
        
        return 'Complete los detalles del examen médico que desea solicitar';
    }
}
