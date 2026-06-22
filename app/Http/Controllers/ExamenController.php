<?php

namespace App\Http\Controllers;

use App\Models\Examenes;
use App\Models\Consulta;
use App\Models\Centros;
use Illuminate\Http\Request;

class ExamenController extends Controller
{
    /**
     * Imprimir un examen individual
     */
    public function imprimir(Examenes $examen)
    {
        // Cargar las relaciones necesarias
        $examen->load([
            'consulta.paciente.persona',
            'medico.persona',
            'centro'
        ]);

        // Validar que el examen existe y tiene las relaciones
        if (!$examen->consulta || !$examen->consulta->paciente || !$examen->medico) {
            abort(404, 'Examen no encontrado o datos incompletos');
        }

        return view('examen.imprimir', compact('examen'));
    }

    /**
     * Imprimir todos los exámenes de una consulta
     */
    public function imprimirPorConsulta(Consulta $consulta)
    {
        // Cargar las relaciones necesarias
        $consulta->load([
            'examenes.medico.persona',
            'paciente.persona',
            'medico.persona',
            'centro'
        ]);

        // Validar que la consulta tiene exámenes
        if ($consulta->examenes->isEmpty()) {
            abort(404, 'No hay exámenes para esta consulta');
        }

        $medico = $consulta->medico;
        
        // Construir la lista de exámenes para la vista
        $examenesLista = [];
        foreach ($consulta->examenes as $examen) {
            $examenesLista[] = (object)[
                'tipo_examen' => $examen->tipo_examen,
                'observaciones' => $examen->observaciones,
                'estado' => $examen->estado,
                'paciente' => $consulta->paciente,
                'persona' => $consulta->paciente->persona ?? null,
            ];
        }

        // Usar la vista de impresión y pasar todas las variables necesarias
        $examen = null; // Para evitar error de variable indefinida en la vista
        return view('examen.imprimir', compact('medico', 'examenesLista', 'examen', 'consulta'));
    }
}
