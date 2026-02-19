<?php

namespace App\Filament\Widgets;

use App\Models\Citas;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Filament\Notifications\Notification;

class CalendarioCitasWidget extends Widget
{
    protected static string $view = 'filament.widgets.calendario-citas-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '30s';
    protected static ?int $sort = 2;
    
    public static function canView(): bool
    {
        // Solo mostrar si hay tenant activo
        return tenancy()->initialized;
    }
    
    // Propiedades para el calendario
    public array $citas = [];
    public array $citasPorDia = [];
    public string $mes;
    public string $anio;
    public string $mesActual;
    
    // Propiedades para el modal
    public ?int $citaSeleccionadaId = null;
    public ?string $diaSeleccionado = null;
    public array $citasDelDia = [];
    public ?string $fechaSeleccionadaUrl = null;

    public function mount(): void
    {
        $this->mes = session('calendario_mes', Carbon::now()->format('m'));
        $this->anio = session('calendario_anio', Carbon::now()->format('Y'));
        $this->mesActual = Carbon::createFromDate($this->anio, $this->mes, 1)->locale('es')->monthName;
        
        session(['calendario_mes' => $this->mes]);
        session(['calendario_anio' => $this->anio]);
        
        $this->cargarCitas();
    }

    public static function getSort(): int
    {
        return 2;
    }
    
    /**
     * Mostrar modal con citas del día
     */
    public function mostrarCitasDelDia(string $dia, ?int $citaId = null): void
    {
        $this->diaSeleccionado = $dia . ' de ' . $this->mesActual . ' ' . $this->anio;
        
        $fechaSeleccionada = Carbon::createFromDate($this->anio, $this->mes, intval($dia));
        $this->fechaSeleccionadaUrl = $fechaSeleccionada->format('Y-m-d');
        
        if ($citaId !== null) {
            foreach ($this->citasPorDia as $citas) {
                foreach ($citas as $cita) {
                    if ($cita['id'] == $citaId) {
                        $this->citasDelDia = [$cita];
                        break 2;
                    }
                }
            }
        } else {
            $this->citasDelDia = $this->citasPorDia[intval($dia)] ?? [];
        }
        
        $this->dispatch('open-modal', id: 'citas-del-dia-modal');
    }
    
    /**
     * Actualiza el estado de una cita en memoria
     */
    private function actualizarEstadoCitaEnMemoria(int $id, string $nuevoEstado): void
    {
        foreach ($this->citasPorDia as $dia => $citas) {
            foreach ($citas as $idx => $citaData) {
                if ($citaData['id'] == $id) {
                    $this->citasPorDia[$dia][$idx]['estado'] = $nuevoEstado;
                    $this->citasPorDia[$dia][$idx]['color'] = $this->getColorForEstado($nuevoEstado);
                }
            }
        }
        
        foreach ($this->citasDelDia as $idx => $citaData) {
            if ($citaData['id'] == $id) {
                $this->citasDelDia[$idx]['estado'] = $nuevoEstado;
                $this->citasDelDia[$idx]['color'] = $this->getColorForEstado($nuevoEstado);
            }
        }
    }

    /**
     * Obtener color según estado de la cita
     */
    protected function getColorForEstado(string $estado): string
    {
        return match($estado) {
            'Confirmado' => '#3b82f6', // blue-500
            'Pendiente' => '#f97316',  // orange-500
            'Cancelado' => '#ef4444',  // red-500
            'Realizada' => '#22c55e',  // green-500
            default => '#6b7280',      // gray-500
        };
    }
    
    /**
     * Actualizar el mes seleccionado
     */
    public function updatedMes()
    {
        try {
            $this->mesActual = Carbon::createFromDate($this->anio, $this->mes, 1)->locale('es')->monthName;
            
            session(['calendario_mes' => $this->mes]);
            
            $this->cargarCitas();
            $this->dispatch('limpiar-modal-calendario');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al cambiar de mes')
                ->body('No se pudo cambiar al mes seleccionado: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Actualizar el año seleccionado
     */
    public function updatedAnio()
    {
        try {
            $this->mesActual = Carbon::createFromDate($this->anio, $this->mes, 1)->locale('es')->monthName;
            
            session(['calendario_anio' => $this->anio]);
            
            $this->cargarCitas();
            $this->dispatch('limpiar-modal-calendario');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al cambiar de año')
                ->body('No se pudo cambiar al año seleccionado: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Navegar al mes anterior (mantener para compatibilidad)
     */
    public function mesAnterior()
    {
        try {
            $fecha = Carbon::createFromDate($this->anio, $this->mes, 1)->subMonth();
            $this->mes = $fecha->format('m');
            $this->anio = $fecha->format('Y');
            $this->mesActual = $fecha->locale('es')->monthName;
            
            session(['calendario_mes' => $this->mes]);
            session(['calendario_anio' => $this->anio]);
            
            $this->cargarCitas();
            $this->dispatch('limpiar-modal-calendario');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al cambiar de mes')
                ->body('No se pudo navegar al mes anterior: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Navegar al mes siguiente (mantener para compatibilidad)
     */
    public function mesSiguiente()
    {
        try {
            $fecha = Carbon::createFromDate($this->anio, $this->mes, 1)->addMonth();
            $this->mes = $fecha->format('m');
            $this->anio = $fecha->format('Y');
            $this->mesActual = $fecha->locale('es')->monthName;
            
            session(['calendario_mes' => $this->mes]);
            session(['calendario_anio' => $this->anio]);
            
            $this->cargarCitas();
            $this->dispatch('limpiar-modal-calendario');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al cambiar de mes')
                ->body('No se pudo navegar al mes siguiente: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Ir al mes actual
     */
    public function irHoy()
    {
        try {
            $fecha = Carbon::now();
            $this->mes = $fecha->format('m');
            $this->anio = $fecha->format('Y');
            $this->mesActual = $fecha->locale('es')->monthName;
            
            session(['calendario_mes' => $this->mes]);
            session(['calendario_anio' => $this->anio]);
            
            $this->cargarCitas();
            $this->dispatch('limpiar-modal-calendario');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al ir al mes actual')
                ->body('No se pudo navegar al mes actual: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Cargar las citas para el mes actual
     */
    protected function cargarCitas()
    {
        $this->citas = [];
        $this->citasPorDia = [];
        
        $user = Auth::user();
        
        if (!$user) {
            return;
        }

        $fechaInicio = Carbon::createFromDate($this->anio, $this->mes, 1)->startOfMonth();
        $fechaFin = Carbon::createFromDate($this->anio, $this->mes, 1)->endOfMonth();
        
        $withRelations = ['paciente.persona', 'medico.persona', 'medico.especialidades'];
        
        $citaModel = new Citas();
        $availableRelations = get_class_methods($citaModel);
        if (in_array('especialidad', $availableRelations)) {
            $withRelations[] = 'especialidad';
        }
        if (in_array('especialidad_medico', $availableRelations)) {
            $withRelations[] = 'especialidad_medico';
        }
        
        // Construir query según el rol del usuario
        $query = Citas::query()
            ->where('estado', '!=', 'Cancelado')
            ->whereBetween('fecha', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')])
            ->with($withRelations);

        // Filtrar según el rol del usuario
        // En multi-tenant, el contexto ya define el centro (no filtrar por centro_id)
        if ($user->roles->contains('name', 'root') || $user->roles->contains('name', 'administrador')) {
            // Root y administradores ven todas las citas del tenant actual
            // Sin filtro adicional - el tenant ya filtra
        } elseif ($user->roles->contains('name', 'medico')) {
            // Médicos solo ven sus propias citas
            if ($user->medico) {
                $query->where('medico_id', $user->medico->id);
            } else {
                // Si no tiene médico asociado, no ver nada
                return;
            }
        } else {
            // Otros roles no ven citas
            return;
        }

        $this->citas = $query->get()
            ->map(function ($cita) {
                $fecha = Carbon::parse($cita->fecha);
                $hora = Carbon::parse($cita->hora)->format('H:i');
                $pacienteNombre = $cita->paciente->persona->nombre_completo ?? 'Paciente sin nombre';
                
                $especialidad = '';
                if (isset($cita->especialidad_id) && isset($cita->especialidad) && is_object($cita->especialidad)) {
                    $especialidad = $cita->especialidad->nombre ?? '';
                } elseif (isset($cita->especialidad_medico) && is_object($cita->especialidad_medico)) {
                    $especialidad = $cita->especialidad_medico->nombre ?? '';
                } elseif (isset($cita->medico) && isset($cita->medico->especialidades) && $cita->medico->especialidades->isNotEmpty()) {
                    $especialidad = $cita->medico->especialidades->first()->nombre ?? '';
                }
                
                return [
                    'id' => $cita->id,
                    'fecha' => $fecha->format('Y-m-d'),
                    'dia' => $fecha->day,
                    'hora' => $hora,
                    'paciente' => $pacienteNombre,
                    'paciente_id' => $cita->paciente_id,
                    'medico_id' => $cita->medico_id,
                    'motivo' => $cita->motivo,
                    'estado' => $cita->estado,
                    'color' => $this->getColorForEstado($cita->estado),
                    'medico' => $cita->medico->persona->nombre_completo ?? 'Médico',
                    'especialidad' => $especialidad,
                ];
            })
            ->toArray();
            
        // Agrupar citas por día
        $this->citasPorDia = collect($this->citas)
            ->groupBy('dia')
            ->map(function ($items) {
                return $items->sortBy('hora')->values()->toArray();
            })
            ->toArray();
    }
    
    /**
     * Cancelar una cita
     */
    public function cancelarCita($citaId)
    {
        try {
            $cita = Citas::find($citaId);
            
            if (!$cita) {
                Notification::make()
                    ->title('Error')
                    ->body('Cita no encontrada')
                    ->danger()
                    ->send();
                return false;
            }

            // Verificar permisos
            $user = Auth::user();
            if (!Gate::allows('cancel', $cita)) {
                Notification::make()
                    ->title('Sin permisos')
                    ->body('No tiene permisos para cancelar esta cita')
                    ->danger()
                    ->send();
                return false;
            }

            $cita->estado = 'Cancelado';
            $cita->save();
            
            // Actualizar en memoria
            $this->actualizarEstadoCitaEnMemoria($citaId, 'Cancelado');
            
            // Recargar las citas
            $this->cargarCitas();
            
            // Limpiar el modal
            $this->citasDelDia = [];
            $this->diaSeleccionado = null;
            $this->citaSeleccionadaId = null;
            
            Notification::make()
                ->title('Cita cancelada')
                ->body('La cita ha sido cancelada correctamente')
                ->success()
                ->send();
            
            return true;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al cancelar cita')
                ->body('No se pudo cancelar la cita: ' . $e->getMessage())
                ->danger()
                ->send();
        }
        
        return false;
    }
    
    /**
     * Confirmar una cita - ARREGLADO
     */
    public function confirmarCita($citaId)
    {
        try {
            $cita = Citas::find($citaId);
            
            if (!$cita) {
                Notification::make()
                    ->title('Error')
                    ->body('Cita no encontrada')
                    ->danger()
                    ->send();
                return false;
            }

            // Verificar permisos
            if (!Gate::allows('confirm', $cita)) {
                Notification::make()
                    ->title('Sin permisos')
                    ->body('No tiene permisos para confirmar esta cita')
                    ->danger()
                    ->send();
                return false;
            }

            $cita->estado = 'Confirmado';
            $cita->save();
            
            // Actualizar en memoria
            $this->actualizarEstadoCitaEnMemoria($citaId, 'Confirmado');
            
            // Recargar las citas
            $this->cargarCitas();
            
            Notification::make()
                ->title('Cita confirmada')
                ->body('La cita ha sido confirmada correctamente')
                ->success()
                ->send();
            
            return true;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al confirmar cita')
                ->body('No se pudo confirmar la cita: ' . $e->getMessage())
                ->danger()
                ->send();
        }
        
        return false;
    }
    public function marcarComoRealizada($citaId)
    {
        try {
            $cita = Citas::find($citaId);
            
            if ($cita) {
                $cita->estado = 'Realizada';
                $cita->save();
                
                // Actualizar en memoria
                $this->actualizarEstadoCitaEnMemoria($citaId, 'Realizada');
                
                // Recargar las citas
                $this->cargarCitas();
                
                Notification::make()
                    ->title('Cita marcada como realizada')
                    ->body('La cita ha sido marcada como realizada correctamente')
                    ->success()
                    ->send();
                
                return true;
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al marcar cita como realizada')
                ->body('No se pudo marcar la cita como realizada: ' . $e->getMessage())
                ->danger()
                ->send();
        }
        
        return false;
    }
    
    /**
     * Crear consulta desde una cita 
     */
    public function crearConsulta($citaId)
    {
        try {
            $cita = Citas::with('paciente')->find($citaId);
            
            if (!$cita) {
                Notification::make()
                    ->title('Error')
                    ->body('Cita no encontrada')
                    ->danger()
                    ->send();
                return false;
            }

            $user = Auth::user();

            // Solo médicos pueden crear consultas, y solo para sus propias citas
            if (!$user->roles->contains('name', 'medico')) {
                Notification::make()
                    ->title('Sin permisos')
                    ->body('Solo los médicos pueden crear consultas')
                    ->danger()
                    ->send();
                return false;
            }

            // Verificar que la cita pertenezca al médico
            if ($user->medico && $cita->medico_id !== $user->medico->id) {
                Notification::make()
                    ->title('Sin permisos')
                    ->body('Solo puede crear consultas para sus propias citas')
                    ->danger()
                    ->send();
                return false;
            }

            session(['cita_en_consulta' => $citaId]);
            
            Notification::make()
                ->title('Redirigiendo...')
                ->body('Creando consulta para ' . ($cita->paciente->persona->nombre_completo ?? 'el paciente'))
                ->success()
                ->send();
            
            // Usar redirect() directo - SIN dispatch
            return redirect("/admin/consultas/consultas/create?paciente_id={$cita->paciente_id}&cita_id={$citaId}");
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al crear consulta')
                ->body('No se pudo crear la consulta: ' . $e->getMessage())
                ->danger()
                ->send();
        }
        
        return false;
    }
    
    /**
     * Método para cerrar modal - NUEVO
     */
    public function cerrarModal()
    {
        $this->citasDelDia = [];
        $this->diaSeleccionado = null;
        $this->citaSeleccionadaId = null;
        $this->fechaSeleccionadaUrl = null;
    }
}