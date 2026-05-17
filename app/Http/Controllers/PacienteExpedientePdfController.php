<?php

namespace App\Http\Controllers;

use App\Models\Centros_Medico;
use App\Models\Pacientes;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PacienteExpedientePdfController extends Controller
{
    public function generarPdf(Request $request, Pacientes $paciente)
    {
        $tipo = $request->query('tipo', 'completo');
        if (! in_array($tipo, ['resumen', 'completo'], true)) {
            $tipo = 'completo';
        }

        $paciente->load([
            'persona.nacionalidad',
            'enfermedades',
            'consultas.medico.persona',
            'consultas.recetas',
            'consultas.examenes',
        ]);

        $consultas = $paciente->consultas
            ->sortByDesc('created_at')
            ->values();

        $totalConsultas = $consultas->count();
        $ultimaConsulta = $consultas->first();
        $medicosDistintos = $consultas
            ->pluck('medico_id')
            ->filter()
            ->unique()
            ->count();

        $medicamentosActivos = $consultas
            ->flatMap(function ($consulta) {
                return $consulta->recetas
                    ->pluck('medicamentos')
                    ->filter();
            })
            ->flatMap(function ($texto) {
                return preg_split('/[\n,;]+/', (string) $texto) ?: [];
            })
            ->map(fn ($medicamento) => trim($medicamento))
            ->filter()
            ->unique()
            ->values();

        $edad = null;
        if ($paciente->persona?->fecha_nacimiento) {
            $edad = Carbon::parse($paciente->persona->fecha_nacimiento)->age;
        }

        $tenant = tenancy()->tenant;
        $centro = null;

        if ($tenant?->centro_id) {
            $centro = Centros_Medico::on('mysql')->find($tenant->centro_id);
        }

        $numeroExpediente = 'EXP-' . str_pad((string) $paciente->id, 6, '0', STR_PAD_LEFT);

        $pdf = Pdf::loadView('pdf.expediente-paciente', [
            'tipo' => $tipo,
            'paciente' => $paciente,
            'consultas' => $consultas,
            'totalConsultas' => $totalConsultas,
            'ultimaConsulta' => $ultimaConsulta,
            'medicosDistintos' => $medicosDistintos,
            'medicamentosActivos' => $medicamentosActivos,
            'edad' => $edad,
            'centro' => $centro,
            'numeroExpediente' => $numeroExpediente,
            'fechaEmision' => now(),
        ])
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'Arial',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isJavascriptEnabled' => false,
                'isFontSubsettingEnabled' => true,
            ]);

        $nombrePaciente = trim((string) ($paciente->persona?->primer_nombre . '_' . $paciente->persona?->primer_apellido));
        $nombrePaciente = preg_replace('/\s+/', '_', $nombrePaciente ?: 'Paciente');
        $prefijo = $tipo === 'resumen' ? 'Resumen_Clinico' : 'Expediente_Completo';
        $archivo = $prefijo . '_' . $numeroExpediente . '_' . $nombrePaciente . '.pdf';

        return $pdf->stream($archivo);
    }
}
