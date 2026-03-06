<?php

namespace App\Filament\Resources\Consultas\ConsultasResource\Pages;

use App\Filament\Resources\Consultas\ConsultasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use App\Models\Citas;



class CreateConsultas extends CreateRecord
{
    protected static string $resource = ConsultasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Consulta creada')
            ->body('La consulta ha sido registrada exitosamente.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ya no necesitamos procesar examenes_data porque ahora usamos relaciones directas
        return $data;
    }
        public function mount(): void
    {
        parent::mount();
        
        // Pre-llenar datos si viene de una cita
        $citaId = request()->get('cita_id');
        $pacienteId = request()->get('paciente_id');
        
        Log::info('Mount CreateConsultas', [
            'cita_id_request' => $citaId,
            'paciente_id_request' => $pacienteId,
            'session_cita' => session('cita_en_consulta'),
            'all_request' => request()->all(),
        ]);
        
        if ($citaId && $pacienteId) {
            $this->form->fill([
                'paciente_id' => $pacienteId,
                'cita_id' => $citaId,
                'fecha_consulta' => now()->format('Y-m-d'),
                'hora_consulta' => now()->format('H:i'),
            ]);
            
            Log::info('Formulario pre-llenado', [
                'paciente_id' => $pacienteId,
                'cita_id' => $citaId,
            ]);
        }
    }

        /**
     * Método fusionado - Se ejecuta después de crear la consulta
     * Maneja tanto el logging como la actualización del estado de la cita
     */
    protected function afterCreate(): void
    {
        Log::info('=== INICIO afterCreate ===');
        
        // Verificar que tenemos un record válido
        if (!$this->record) {
            Log::error('ERROR: $this->record es NULL en afterCreate');
            return;
        }
        
        // 1. LOGGING DE LA CONSULTA (funcionalidad original)
        Log::info('Nueva consulta creada', [
            'consulta_id' => $this->record->id,
            'paciente_id' => $this->record->paciente_id,
            'medico_id' => $this->record->medico_id,
            'cita_id_en_record' => $this->record->cita_id ?? 'NO DEFINIDO EN RECORD',
            'created_by' => $this->record->created_by ?? null,
            'record_completo' => $this->record->toArray(),
        ]);

        // 2. ACTUALIZACIÓN DEL ESTADO DE LA CITA (nueva funcionalidad)
        // Verificar múltiples fuentes para encontrar la cita_id
        $citaIdRecord = $this->record->cita_id ?? null;
        $citaIdRequest = request()->get('cita_id') ?? null;
        $citaIdSession = session('cita_en_consulta') ?? null;
        
        Log::info('Búsqueda de cita_id desde múltiples fuentes', [
            'cita_id_record' => $citaIdRecord,
            'cita_id_request' => $citaIdRequest,
            'cita_id_session' => $citaIdSession,
        ]);
        
        // Usar la primera fuente que tenga valor
        $citaId = $citaIdRecord ?? $citaIdRequest ?? $citaIdSession;
        
        Log::info('Cita ID final seleccionado', [
            'cita_id_final' => $citaId,
            'tipo_fuente' => $citaIdRecord ? 'record' : ($citaIdRequest ? 'request' : ($citaIdSession ? 'session' : 'ninguna')),
        ]);
        
        if ($citaId) {
            try {
                Log::info('Intentando buscar cita con ID: ' . $citaId);
                
                $cita = Citas::find($citaId);
                
                if (!$cita) {
                    Log::error('CITA NO ENCONTRADA en la base de datos', [
                        'cita_id_buscado' => $citaId,
                        'consulta_id' => $this->record->id,
                    ]);
                    
                    Notification::make()
                        ->title('⚠️ Advertencia')
                        ->body('Consulta creada pero no se pudo encontrar la cita asociada')
                        ->warning()
                        ->send();
                        
                    return;
                }
                
                Log::info('Cita encontrada exitosamente', [
                    'cita_id' => $cita->id,
                    'estado_actual' => $cita->estado,
                    'paciente_id' => $cita->paciente_id,
                    'medico_id' => $cita->medico_id,
                ]);
                
                if ($cita->estado !== 'Realizado') {
                    $estadoAnterior = $cita->estado;
                    
                    Log::info('Actualizando estado de cita', [
                        'cita_id' => $cita->id,
                        'estado_anterior' => $estadoAnterior,
                        'estado_nuevo' => 'Realizado',
                    ]);
                    
                    $cita->estado = 'Realizado';
                    $guardado = $cita->save();
                    
                    Log::info('Resultado del guardado', [
                        'guardado_exitoso' => $guardado,
                        'estado_despues_guardar' => $cita->fresh()->estado,
                    ]);
                    
                    if ($guardado) {
                        Log::info('✅ Estado de cita actualizado EXITOSAMENTE', [
                            'cita_id' => $citaId,
                            'estado_anterior' => $estadoAnterior,
                            'estado_nuevo' => 'Realizado',
                            'consulta_id' => $this->record->id,
                        ]);
                        
                        Notification::make()
                            ->title('✅ Consulta creada exitosamente')
                            ->body('La consulta se ha guardado y la cita #' . $citaId . ' ha sido marcada como Realizado')
                            ->success()
                            ->duration(5000)
                            ->send();
                    } else {
                        Log::error('ERROR: No se pudo guardar el cambio de estado');
                        
                        Notification::make()
                            ->title('⚠️ Error parcial')
                            ->body('Consulta creada pero no se pudo actualizar el estado de la cita')
                            ->warning()
                            ->send();
                    }
                } else {
                    Log::info('Cita ya estaba Realizado', [
                        'cita_id' => $citaId,
                        'estado_actual' => $cita->estado,
                    ]);
                    
                    Notification::make()
                        ->title('✅ Consulta creada')
                        ->body('La consulta se ha guardado (la cita ya estaba marcada como Realizado)')
                        ->success()
                        ->send();
                }
            } catch (\Exception $e) {
                Log::error('EXCEPCIÓN al actualizar estado de cita', [
                    'cita_id' => $citaId,
                    'consulta_id' => $this->record->id,
                    'error_message' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'stack_trace' => $e->getTraceAsString(),
                ]);
                
                Notification::make()
                    ->title('⚠️ Error al actualizar cita')
                    ->body('La consulta se guardó correctamente pero hubo un error al actualizar el estado de la cita: ' . $e->getMessage())
                    ->warning()
                    ->duration(7000)
                    ->send();
            }
            
            // Limpiar la sesión
            session()->forget(['cita_en_consulta']);
        } else {
            Log::info('No se encontró cita_id en ninguna fuente', [
                'consulta_id' => $this->record->id,
                'sources_checked' => ['record->cita_id', 'request->cita_id', 'session->cita_en_consulta'],
            ]);
            
            Notification::make()
                ->title('✅ Consulta creada')
                ->body('La consulta ha sido registrada exitosamente (sin cita asociada)')
                ->success()
                ->send();
        }
        
        // Ya no necesitamos crear exámenes manualmente porque ahora se crean automáticamente via relaciones
        
        Log::info('=== FIN afterCreate ===');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Crear Consulta'),

            $this->getCreateAnotherFormAction()
                ->label('Crear y Agregar Otra'),

            $this->getCancelFormAction()
                ->label('Cancelar'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Volver al Listado')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray'),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\Consultas\Widgets\HistorialExamenes::class,
        ];
    }
  
}
