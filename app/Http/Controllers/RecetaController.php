<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receta;
use App\Models\Consulta;

class RecetaController extends Controller
{
    public function imprimir(Receta $receta)
    {
        // Cargar todas las relaciones necesarias incluyendo las tablas pivote
        $receta->load([
            'paciente.persona',
            'medico.persona',
            'medico.especialidades',  // Relación many-to-many con tabla pivote
            'medico.centro',          // Relación belongsTo para el centro principal
            'medico.recetarios',      // Cambiado a plural para cargar todos los recetarios
            'consulta'
        ]);

        // También cargar las relaciones del médico específicamente para asegurar que se carguen
        $receta->medico->load(['especialidades', 'centro', 'recetarios']);


        // Buscar recetario por medico y consulta (si existe uno para esta consulta)
        $recetario = $receta->medico->recetarios()
            ->where('consulta_id', $receta->consulta_id)
            ->latest()
            ->first();
        // Si no existe, usar el más reciente del médico
        if (!$recetario) {
            $recetario = $receta->medico->recetarios()->latest()->first();
        }
        $config = $recetario ? $recetario->configuracion : [];

        return view('receta.imprimir', compact('receta', 'config'));
    }

        public function imprimirPorConsulta($consultaId)
    {
        $consulta = Consulta::with('recetas.paciente.persona')->findOrFail($consultaId);
        $medico = $consulta->medico;
        $recetario = $medico->recetarios()->latest()->first();

        // Construir la lista de recetas para la tabla, incluyendo paciente y persona
        $recetasLista = [];
        foreach ($consulta->recetas as $receta) {
            $recetasLista[] = (object)[
                'medicamento' => $receta->medicamentos,
                'indicaciones' => $receta->indicaciones,
                'paciente' => $receta->paciente,
                'persona' => $receta->paciente->persona ?? null,
            ];
        }

        // Usar la configuración del recetario más reciente
        $config = $recetario ? $recetario->configuracion : [];

        // Usar la vista de impresión real y pasar todas las variables necesarias
        $receta = null; // Para evitar error de variable indefinida en la vista
        return view('receta.imprimir', compact('medico', 'recetario', 'recetasLista', 'config', 'receta'));
    }
}
