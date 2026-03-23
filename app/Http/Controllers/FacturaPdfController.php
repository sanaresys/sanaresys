<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FacturaPdfController extends Controller
{
    private function obtenerNumeroFacturaParaArchivo(Factura $factura): string
    {
        $numeroFactura = $factura->usa_cai && $factura->caiCorrelativo
            ? $factura->caiCorrelativo->numero_factura
            : $factura->generarNumeroSinCAI();

        return str_replace(['/', '-', ' '], '_', $numeroFactura);
    }

    /**
     * Generar PDF de la factura
     */
    public function generarPdf(Factura $factura)
    {
        try {
            // Cargar todas las relaciones necesarias
            $factura->load([
                'paciente.persona',
                'medico.persona',
                'medico.especialidades', 
                'centro',
                'cita',
                'consulta',
                'detalles.servicio',
                'pagos.tipoPago',
                'caiAutorizacion',
                'caiCorrelativo',
                'createdByUser',
                'descuento'
            ]);

            // Asegurar que tenemos datos mínimos necesarios
            if (!$factura->paciente || !$factura->paciente->persona) {
                throw new \Exception('La factura no tiene información del paciente');
            }

            // Configurar opciones del PDF con configuración optimizada
            $pdf = Pdf::loadView('pdf.factura', compact('factura'))
                ->setPaper('letter', 'portrait')
                ->setOptions([
                    'dpi' => 150,
                    'defaultFont' => 'Arial',
                    'isRemoteEnabled' => true,
                    'isPhpEnabled' => true,
                    'chroot' => public_path(),
                    'isHtml5ParserEnabled' => true,
                    'isJavascriptEnabled' => false,
                    'isFontSubsettingEnabled' => true,
                ]);

            // Generar nombre del archivo más descriptivo
            $numeroFactura = $this->obtenerNumeroFacturaParaArchivo($factura);
                
            $pacienteNombre = $factura->paciente && $factura->paciente->persona
                ? str_replace([' ', '.'], '_', $factura->paciente->persona->nombre_completo) 
                : 'Sin_Paciente';
            
            $nombreArchivo = "Factura_{$numeroFactura}_{$pacienteNombre}_{$factura->fecha_emision->format('Y-m-d')}.pdf";

            // Log de la generación
            Log::info('PDF de factura generado', [
                'factura_id' => $factura->id,
                'numero_factura' => $numeroFactura,
                'usuario' => Auth::user()?->name ?? 'Anónimo'
            ]);

            // Retornar el PDF para descarga
            return $pdf->download($nombreArchivo);
            
        } catch (\Exception $e) {
            Log::error('Error al generar PDF de factura', [
                'factura_id' => $factura->id ?? 'N/A',
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar PDF en el navegador (preview)
     */
    public function previewPdf(Factura $factura)
    {
        try {
            // Cargar todas las relaciones necesarias
            $factura->load([
                'paciente.persona',
                'medico.persona',
                'centro', 
                'cita',
                'consulta',
                'detalles.servicio',
                'pagos.tipoPago',
                'caiAutorizacion',
                'caiCorrelativo',
                'createdByUser',
                'descuento'
            ]);

            // Configurar opciones del PDF
            $pdf = Pdf::loadView('pdf.factura', compact('factura'))
                ->setPaper('letter', 'portrait')
                ->setOptions([
                    'dpi' => 150,
                    'defaultFont' => 'Arial',
                    'isRemoteEnabled' => true,
                    'isPhpEnabled' => true,
                    'chroot' => public_path(),
                    'isHtml5ParserEnabled' => true,
                    'isJavascriptEnabled' => false,
                    'isFontSubsettingEnabled' => true,
                ]);

            // Generar nombre para preview
            $numeroFactura = $this->obtenerNumeroFacturaParaArchivo($factura);
            $nombreArchivo = "Preview_Factura_{$numeroFactura}.pdf";

            // Mostrar en el navegador
            return $pdf->stream($nombreArchivo);
            
        } catch (\Exception $e) {
            Log::error('Error al generar preview PDF de factura', [
                'factura_id' => $factura->id ?? 'N/A',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Error al generar la vista previa del PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar PDF y guardarlo en storage
     */
    public function guardarPdf(Factura $factura)
    {
        try {
            // Cargar todas las relaciones necesarias
            $factura->load([
                'paciente.persona',
                'medico.persona',
                'centro',
                'cita',
                'consulta',
                'detalles.servicio',
                'pagos.tipoPago',
                'caiAutorizacion',
                'caiCorrelativo',
                'createdByUser',
                'descuento'
            ]);

            // Configurar opciones del PDF
            $pdf = Pdf::loadView('pdf.factura', compact('factura'))
                ->setPaper('letter', 'portrait')
                ->setOptions([
                    'dpi' => 150,
                    'defaultFont' => 'Arial',
                    'isRemoteEnabled' => true,
                    'isPhpEnabled' => true,
                    'chroot' => public_path(),
                    'isHtml5ParserEnabled' => true,
                    'isJavascriptEnabled' => false,
                    'isFontSubsettingEnabled' => true,
                ]);

            // Generar nombre del archivo y ruta
            $numeroFactura = $this->obtenerNumeroFacturaParaArchivo($factura);
            $nombreArchivo = "facturas/Factura_{$numeroFactura}_{$factura->fecha_emision->format('Y-m-d')}.pdf";
            
            // Guardar usando Storage facade
            $pdfContent = $pdf->output();
            Storage::disk('public')->put($nombreArchivo, $pdfContent);

            Log::info('PDF de factura guardado en storage', [
                'factura_id' => $factura->id,
                'archivo' => $nombreArchivo,
                'usuario' => Auth::user()?->name ?? 'Anónimo'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'PDF guardado exitosamente',
                'ruta' => $nombreArchivo,
                'url' => Storage::url($nombreArchivo)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al guardar PDF de factura', [
                'factura_id' => $factura->id ?? 'N/A',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Error al guardar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar múltiples PDFs (por lote)
     */
    public function generarPdfLote(Request $request)
    {
        try {
            $facturaIds = $request->input('factura_ids', []);
            
            if (empty($facturaIds)) {
                return response()->json(['error' => 'No se seleccionaron facturas'], 400);
            }

            // Límite de facturas por lote para evitar problemas de memoria
            if (count($facturaIds) > 50) {
                return response()->json(['error' => 'Máximo 50 facturas por lote'], 400);
            }

            $facturas = Factura::whereIn('id', $facturaIds)
                ->with([
                    'paciente.persona',
                    'medico.persona',
                    'centro',
                    'cita',
                    'consulta',
                    'detalles.servicio',
                    'pagos.tipoPago',
                    'caiAutorizacion',
                    'caiCorrelativo',
                    'createdByUser',
                    'descuento'
                ])
                ->get();

            if ($facturas->isEmpty()) {
                return response()->json(['error' => 'No se encontraron facturas válidas'], 404);
            }

            $zip = new \ZipArchive();
            $zipFileName = 'Facturas_Lote_' . now()->format('Y-m-d_H-i-s') . '.zip';
            $zipPath = storage_path('app/public/' . $zipFileName);

            if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
                return response()->json(['error' => 'No se pudo crear el archivo ZIP'], 500);
            }

            $pdfGenerados = 0;
            foreach ($facturas as $factura) {
                try {
                    $pdf = Pdf::loadView('pdf.factura', compact('factura'))
                        ->setPaper('letter', 'portrait')
                        ->setOptions([
                            'dpi' => 150,
                            'defaultFont' => 'Arial',
                            'isRemoteEnabled' => true,
                            'isPhpEnabled' => true,
                            'chroot' => public_path(),
                            'isHtml5ParserEnabled' => true,
                            'isJavascriptEnabled' => false,
                            'isFontSubsettingEnabled' => true,
                        ]);

                    $numeroFactura = $this->obtenerNumeroFacturaParaArchivo($factura);
                        
                    $pacienteNombre = $factura->paciente && $factura->paciente->persona
                        ? str_replace([' ', '.'], '_', $factura->paciente->persona->nombre_completo) 
                        : 'Sin_Paciente';
                    
                    $nombreArchivo = "Factura_{$numeroFactura}_{$pacienteNombre}_{$factura->fecha_emision->format('Y-m-d')}.pdf";
                    
                    $zip->addFromString($nombreArchivo, $pdf->output());
                    $pdfGenerados++;
                    
                } catch (\Exception $e) {
                    Log::warning('Error al generar PDF individual en lote', [
                        'factura_id' => $factura->id,
                        'error' => $e->getMessage()
                    ]);
                    // Continuar con las demás facturas
                }
            }

            $zip->close();

            if ($pdfGenerados === 0) {
                return response()->json(['error' => 'No se pudo generar ningún PDF'], 500);
            }

            Log::info('Lote de PDFs generado', [
                'total_facturas' => $facturas->count(),
                'pdfs_generados' => $pdfGenerados,
                'archivo_zip' => $zipFileName,
                'usuario' => Auth::user()?->name ?? 'Anónimo'
            ]);

            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Error al generar PDFs en lote', [
                'factura_ids' => $request->input('factura_ids', []),
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error al generar PDFs en lote: ' . $e->getMessage()
            ], 500);
        }
    }
}
