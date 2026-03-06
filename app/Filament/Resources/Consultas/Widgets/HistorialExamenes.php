<?php

namespace App\Filament\Resources\Consultas\Widgets;

use Filament\Widgets\Widget;
use App\Models\Examenes;
use App\Models\Consulta;

class HistorialExamenes extends Widget
{
    protected static string $view = 'filament.resources.consultas.widgets.historial-examenes';

    public ?Consulta $record = null;

    protected int | string | array $columnSpan = 'full';

    public function getPacienteId()
    {
        // Si tenemos un record (en edición), usar su paciente_id
        if ($this->record && $this->record->paciente_id) {
            return $this->record->paciente_id;
        }
        
        // Si estamos creando, intentar obtener el paciente_id de la URL o formulario
        $pacienteId = request()->get('paciente_id');
        if ($pacienteId) {
            return $pacienteId;
        }
        
        // Como último recurso, intentar obtenerlo del objeto de la página actual
        $livewire = \Livewire\Livewire::current();
        if ($livewire && method_exists($livewire, 'data') && isset($livewire->data['paciente_id'])) {
            return $livewire->data['paciente_id'];
        }
        
        return null;
    }

    public function getExamenesAnteriores()
    {
        $pacienteId = $this->getPacienteId();
        
        if (!$pacienteId) {
            return collect();
        }

        // Obtener todos los exámenes del paciente usando el scope que creamos
        return Examenes::examenesPrevios($pacienteId)
            ->with(['medico.user', 'consulta'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function marcarComoNoPresentado($examenId)
    {
        $examen = Examenes::find($examenId);
        if ($examen && auth()->user()->can('update', $examen)) {
            $examen->update(['estado' => 'No presentado']);
            
            $this->dispatch('$refresh');
            
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Examen marcado como no presentado'
            ]);
        }
    }
}
