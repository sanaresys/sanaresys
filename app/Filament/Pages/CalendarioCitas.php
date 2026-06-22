<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Citas;
use App\Models\Pacientes;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarioCitas extends Page
{
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    /* ───────── Configuración de la Page ───────── */
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static string $view = 'filament.pages.calendario-citas';

    /* ───────── Datos que pasaremos a la vista ─── */
    public array $citas = [];
    public array $citasPorDia = [];
    public string $mes;
    public string $anio;
    public string $mesActual;
    public ?int $citaIdConfirmacion = null; // ID de cita para confirmar
    public ?int $citaIdCancelacion = null; // ID de cita para cancelar

    /* ───────── Cargar eventos al montar la Page ─ */
    public function mount(): void
    {
        // Asegurarnos de obtener los parámetros de la URL o de la sesión
        $this->mes = request('mes') ?? session('calendario_mes', Carbon::now()->format('m'));
        $this->anio = request('anio') ?? session('calendario_anio', Carbon::now()->format('Y'));
        $this->mesActual = Carbon::createFromDate($this->anio, $this->mes, 1)->locale('es')->monthName;
        
        // Guardar en sesión para persistencia
        session(['calendario_mes' => $this->mes]);
        session(['calendario_anio' => $this->anio]);
        
        // Cargamos las citas utilizando el método reutilizable
        $this->cargarCitas();
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
     * Navegar al mes anterior
     */
    public function mesAnterior()
    {
        try {
            $fecha = Carbon::createFromDate($this->anio, $this->mes, 1)->subMonth();
            $this->mes = $fecha->format('m');
            $this->anio = $fecha->format('Y');
            $this->mesActual = $fecha->locale('es')->monthName;
            
            // Guardar en sesión para persistencia
            session(['calendario_mes' => $this->mes]);
            session(['calendario_anio' => $this->anio]);
            
            // Recargar los datos de citas
            $this->cargarCitas();
            
            // Redireccionar a la misma página con los parámetros de URL explícitos
            return redirect()->to("/admin/calendario-citas?mes={$this->mes}&anio={$this->anio}");
        } catch (\Exception $e) {
            // Si hay un error, mostrar notificación
            \Filament\Notifications\Notification::make()
                ->title('Error al cambiar de mes')
                ->body('No se pudo navegar al mes anterior: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Navegar al mes siguiente
     */
    public function mesSiguiente()
    {
        try {
            $fecha = Carbon::createFromDate($this->anio, $this->mes, 1)->addMonth();
            $this->mes = $fecha->format('m');
            $this->anio = $fecha->format('Y');
            $this->mesActual = $fecha->locale('es')->monthName;
            
            // Guardar en sesión para persistencia
            session(['calendario_mes' => $this->mes]);
            session(['calendario_anio' => $this->anio]);
            
            // Recargar los datos de citas
            $this->cargarCitas();
            
            // Redireccionar a la misma página con los parámetros de URL explícitos
            return redirect()->to("/admin/calendario-citas?mes={$this->mes}&anio={$this->anio}");
        } catch (\Exception $e) {
            // Si hay un error, mostrar notificación
            \Filament\Notifications\Notification::make()
                ->title('Error al cambiar de mes')
                ->body('No se pudo navegar al mes siguiente: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Ir al mes actual
     */
    public function hoy()
    {
        try {
            $fecha = Carbon::now();
            $this->mes = $fecha->format('m');
            $this->anio = $fecha->format('Y');
            $this->mesActual = $fecha->locale('es')->monthName;
            
            // Guardar en sesión para persistencia
            session(['calendario_mes' => $this->mes]);
            session(['calendario_anio' => $this->anio]);
            
            // Recargar los datos de citas
            $this->cargarCitas();
            
            // Redireccionar a la misma página con los parámetros de URL explícitos
            return redirect()->to("/admin/calendario-citas?mes={$this->mes}&anio={$this->anio}");
        } catch (\Exception $e) {
            // Si hay un error, mostrar notificación
            \Filament\Notifications\Notification::make()
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
        
        $medico = Auth::user()->medico;
        
        if ($medico) {
            $fechaInicio = Carbon::createFromDate($this->anio, $this->mes, 1)->startOfMonth();
            $fechaFin = Carbon::createFromDate($this->anio, $this->mes, 1)->endOfMonth();
            
            $this->citas = Citas::query()
                ->where('medico_id', $medico->id)
                ->where('estado', '!=', 'Cancelado')
                ->whereBetween('fecha', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')])
                ->with(['paciente.persona'])
                ->get()
                ->map(function ($cita) {
                    $fecha = Carbon::parse($cita->fecha);
                    $hora = Carbon::parse($cita->hora)->format('H:i');
                    $pacienteNombre = $cita->paciente->persona->nombre_completo ?? 'Paciente sin nombre';
                    
                    return [
                        'id' => $cita->id,
                        'fecha' => $fecha->format('Y-m-d'),
                        'dia' => $fecha->day,
                        'hora' => $hora,
                        'paciente' => $pacienteNombre,
                        'motivo' => $cita->motivo,
                        'estado' => $cita->estado,
                        'color' => $this->getColorForEstado($cita->estado),
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
    }
    
    /**
     * Cancelar una cita
     */
    public function cancelarCita($citaId)
    {
        try {
            $cita = Citas::find($citaId);
            
            if ($cita) {
                // Cambiar el estado a Cancelada usando fill para formato correcto
                $cita->fill(['estado' => 'Cancelado']);
                $cita->save();
                
                // Notificar al usuario
                \Filament\Notifications\Notification::make()
                    ->title('Cita cancelada')
                    ->body('La cita ha sido cancelada correctamente')
                    ->success()
                    ->send();
                    
                // Recargar las citas para actualizar la vista
                $this->cargarCitas();
                
                // Emitir evento para actualizar la interfaz
                $this->dispatch('citasActualizadas');
                
                // Devolver datos para actualización inmediata en el frontend
                return [
                    'id' => $cita->id,
                    'estado' => 'Cancelado'
                ];
            }
        } catch (\Exception $e) {
            // Si hay un error, mostrar notificación
            \Filament\Notifications\Notification::make()
                ->title('Error al cancelar cita')
                ->body('No se pudo cancelar la cita: ' . $e->getMessage())
                ->danger()
                ->send();
        }
        
        return false;
    }
    
    /**
     * Confirmar una cita
     */
    public function confirmarCita($citaId)
    {
        try {
            $cita = Citas::find($citaId);
            
            if ($cita) {
                // Cambiar el estado a Confirmada usando fill para formato correcto
                $cita->fill(['estado' => 'Confirmado']);
                $cita->save();
                
                // Notificar al usuario
                \Filament\Notifications\Notification::make()
                    ->title('Cita confirmada')
                    ->body('La cita ha sido confirmada correctamente')
                    ->success()
                    ->send();
                    
                // Recargar las citas para actualizar la vista
                $this->cargarCitas();
                
                // Emitir evento para actualizar la interfaz
                $this->dispatch('citasActualizadas');
                
                // Devolver datos para actualización inmediata en el frontend
                return [
                    'id' => $cita->id,
                    'estado' => 'Confirmado'
                ];
            }
        } catch (\Exception $e) {
            // Si hay un error, mostrar notificación
            \Filament\Notifications\Notification::make()
                ->title('Error al confirmar cita')
                ->body('No se pudo confirmar la cita: ' . $e->getMessage())
                ->danger()
                ->send();
        }
        
        return false;
    }
    
    /**
     * Redireccionar a la página de creación de consulta con los datos pre-llenados
     */
    public function crearConsulta($citaId)
    {
        try {
            $cita = Citas::with('paciente')->find($citaId);
            
            if ($cita) {
                // Marcamos que esta cita está en proceso de consulta
                session(['cita_en_consulta' => $citaId]);
                
                // Notificar al usuario antes de redireccionar
                \Filament\Notifications\Notification::make()
                    ->title('Redirigiendo a creación de consulta')
                    ->body('Creando consulta para el paciente ' . ($cita->paciente->persona->nombre_completo ?? 'Desconocido'))
                    ->success()
                    ->send();
                    
                // Construir la URL completa para la redirección
                $urlBase = url('/');
                $redirectUrl = "{$urlBase}/admin/consultas/consultas/create?paciente_id={$cita->paciente_id}&cita_id={$citaId}";
                
                // Dispatch un evento para redirigir mediante JavaScript
                $this->dispatch('redirigirConsulta', url: $redirectUrl);
                
                return true;
            }
        } catch (\Exception $e) {
            // Si hay un error, mostrar notificación
            \Filament\Notifications\Notification::make()
                ->title('Error al crear consulta')
                ->body('No se pudo crear la consulta: ' . $e->getMessage())
                ->danger()
                ->send();
        }
        
        return false;
    }
}
