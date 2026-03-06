<?php

namespace App\Http\Controllers;

use App\Models\ContabilidadMedica\ContratoMedico;
use App\Models\ContabilidadMedica\Nomina;
use App\Models\Medico;
use App\Models\ContabilidadMedica\LiquidacionHonorario;
use App\Models\ContabilidadMedica\PagoHonorario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class NominaController extends Controller
{
    /**
     * Generar la nómina médica en PDF
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarPDF(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'medico_id' => 'nullable|exists:medicos,id',
            'incluir_pagados' => 'boolean',
            'incluir_pendientes' => 'boolean',
        ]);

        $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
        $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();
        $medicoId = $request->medico_id;
        $incluirPagados = $request->incluir_pagados ?? true;
        $incluirPendientes = $request->incluir_pendientes ?? false;

        // Obtener médicos según el filtro
        $medicos = $medicoId 
            ? Medico::where('id', $medicoId)->get() 
            : Medico::all();

        $resultados = [];
        $totalGeneral = 0;
        $totalRetenciones = 0;
        $totalNeto = 0;

        foreach ($medicos as $medico) {
            // Obtener el contrato vigente del médico
            $contrato = ContratoMedico::where('medico_id', $medico->id)
                ->where('fecha_inicio', '<=', $fechaFin)
                ->where(function ($query) use ($fechaInicio) {
                    $query->where('fecha_fin', '>=', $fechaInicio)
                        ->orWhereNull('fecha_fin');
                })
                ->orderBy('fecha_inicio', 'desc')
                ->first();

            // Liquidaciones del periodo
            $liquidacionesQuery = LiquidacionHonorario::where('medico_id', $medico->id)
                ->where('created_at', '>=', $fechaInicio)
                ->where('created_at', '<=', $fechaFin);
            
            if (!$incluirPendientes) {
                $liquidacionesQuery->where('estado', '!=', 'pendiente');
            }
            
            $liquidaciones = $liquidacionesQuery->get();

            // Pagos del periodo
            $pagosQuery = PagoHonorario::where('medico_id', $medico->id)
                ->where('fecha_pago', '>=', $fechaInicio)
                ->where('fecha_pago', '<=', $fechaFin);
            
            if (!$incluirPagados) {
                $pagosQuery->where('estado', '!=', 'pagado');
            }
            
            $pagos = $pagosQuery->get();

            // Calcular totales
            $totalLiquidaciones = $liquidaciones->sum('monto_total');
            $totalPagado = $pagos->sum('monto');
            $totalRetencionesMedico = $pagos->sum('retencion_isr_monto');
            $montoNeto = $totalPagado - $totalRetencionesMedico;
            $totalPendiente = $totalLiquidaciones - $totalPagado;

            // Acumular totales generales
            $totalGeneral += $totalPagado;
            $totalRetenciones += $totalRetencionesMedico;
            $totalNeto += $montoNeto;

            // Verificar si hay datos para este médico
            if ($liquidaciones->count() > 0 || $pagos->count() > 0) {
                $resultados[] = [
                    'medico' => $medico,
                    'nombre_medico' => $medico->nombre . ' ' . $medico->apellido,
                    'especialidad' => $medico->especialidad->nombre ?? 'No especificada',
                    'centro' => $medico->centro->nombre ?? 'No especificado',
                    'contrato' => $contrato,
                    'porcentaje_medico' => $contrato ? $contrato->porcentaje_medico : 0,
                    'liquidaciones' => $liquidaciones,
                    'pagos' => $pagos,
                    'total_liquidaciones' => $totalLiquidaciones,
                    'total_pagado' => $totalPagado,
                    'total_retenciones' => $totalRetencionesMedico,
                    'monto_neto' => $montoNeto,
                    'total_pendiente' => $totalPendiente
                ];
            }
        }

        // Generar el PDF
        $data = [
            'resultados' => $resultados,
            'periodo_inicio' => $fechaInicio->format('d/m/Y'),
            'periodo_fin' => $fechaFin->format('d/m/Y'),
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i'),
            'total_general' => $totalGeneral,
            'total_retenciones' => $totalRetenciones,
            'total_neto' => $totalNeto
        ];

        $pdf = PDF::loadView('pdf.nomina', $data);
        
        // Establecer opciones de PDF
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOption('margin-top', 10);
        $pdf->setOption('margin-right', 10);
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-left', 10);
        
        $filename = 'nomina_medica_' . $fechaInicio->format('d-m-Y') . '_' . $fechaFin->format('d-m-Y') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Generar PDF de una nómina específica
     *
     * @param Nomina $nomina
     * @return \Illuminate\Http\Response
     */
    public function generarPDFNomina(Nomina $nomina)
    {
        // Cargar relaciones necesarias
        $nomina->load(['detalles.medico.persona']);

        // Obtener el nombre del mes
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        $mesNombre = $meses[$nomina->mes] ?? '';
        
        // Determinar el período completo basado en el tipo de pago
        $periodo = $mesNombre . ' ' . $nomina->año;
        $tituloNomina = 'Nómina del mes de ' . $mesNombre . ' ' . $nomina->año;
        $nombreArchivo = "nomina_{$mesNombre}_{$nomina->año}";
        
        if ($nomina->tipo_pago === 'quincenal' && $nomina->quincena) {
            $quincenaTexto = $nomina->quincena == 1 ? 'Primera Quincena' : 'Segunda Quincena';
            $periodo = $quincenaTexto . ' de ' . $mesNombre . ' ' . $nomina->año;
            $tituloNomina = 'Nómina ' . $quincenaTexto . ' de ' . $mesNombre . ' ' . $nomina->año;
            $nombreArchivo = "nomina_" . ($nomina->quincena == 1 ? 'primera' : 'segunda') . "_quincena_{$mesNombre}_{$nomina->año}";
        }

        // Obtener información del centro médico
        $centroMedico = null;
        if ($nomina->centro_id) {
            $centroMedico = \App\Models\Centros_Medico::find($nomina->centro_id);
        }

        $data = [
            'nomina' => $nomina,
            'detalles' => $nomina->detalles,
            'mesNombre' => $mesNombre,
            'periodo' => $periodo,
            'tituloNomina' => $tituloNomina,
            'fechaGeneracion' => Carbon::now()->format('d/m/Y H:i'),
            'totalNomina' => $nomina->detalles->sum('total_pagar'),
            'numeroEmpleados' => $nomina->detalles->count(),
            'centroMedico' => $centroMedico,
        ];

        $pdf = PDF::loadView('pdf.nomina-medica', $data);
        
        // Establecer opciones de PDF
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOption('margin-top', 10);
        $pdf->setOption('margin-right', 10);
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-left', 10);
        
        return $pdf->download("{$nombreArchivo}.pdf");
    }
}
