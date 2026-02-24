<?php

namespace App\Http\Controllers;

use App\Models\FacturaDiseno;
use App\Models\Factura;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FacturaController extends Controller
{
    /**
     * Generar vista previa de factura en PDF
     */
    public function generarPDF(FacturaDiseno $diseno, Request $request)
    {
        // Obtener datos reales del sistema
        $datosFactura = $this->obtenerDatosReales();

        $pdf = Pdf::loadView('pdf.factura-template', [
            'diseno' => $diseno,
            'datosFactura' => $datosFactura
        ]);

        $pdf->setPaper($diseno->tamaño_papel ?? 'A4', $diseno->orientacion_papel ?? 'portrait');

        return $pdf->stream('factura-preview.pdf');
    }
    
    /**
     * Generar PDF de una factura específica
     */
    public function generarPDFFactura(Factura $factura)
    {
        // Cargar relaciones necesarias
        $factura->load([
            'facturaDiseno',
            'paciente.persona',
            'medico.persona',
            'centro',
            'detalles.servicio',
            'caiCorrelativo.caiAutorizacion'
        ]);
        
        // Usar el diseño específico de la factura o el predeterminado del centro
        $diseno = $factura->facturaDiseno;
        if (!$diseno) {
            $diseno = FacturaDiseno::where('activo', true)
                ->where('es_predeterminado', true)
                ->first();
        }
        
        if (!$diseno) {
            return response()->json(['error' => 'No se encontró un diseño válido para esta factura'], 404);
        }
        
        // Obtener datos específicos de esta factura
        $datosFactura = $this->obtenerDatosDeFactura($factura);

        $pdf = Pdf::loadView('pdf.factura-template', [
            'diseno' => $diseno,
            'datosFactura' => $datosFactura
        ]);

        $pdf->setPaper($diseno->tamaño_papel ?? 'A4', $diseno->orientacion_papel ?? 'portrait');

        $numeroFactura = $factura->usa_cai && $factura->caiCorrelativo 
            ? $factura->caiCorrelativo->numero_factura 
            : $factura->generarNumeroSinCAI();

        return $pdf->stream('factura-' . $numeroFactura . '.pdf');
    }
    
    /**
     * Obtener datos reales del sistema para la vista previa
     */
    private function obtenerDatosReales()
    {
        // Obtener el centro médico actual
        $centroId = $this->getCurrentTenantCentroId();
        $centro = \App\Models\Centros_Medico::on('mysql')->find($centroId);
        
        // Obtener el usuario actual y su médico asociado
        $usuario = auth()->user();
        $medico = null;
        $especialidad = 'Medicina General';
        
        if ($usuario) {
            // Buscar si el usuario actual es un médico usando persona_id
            $medico = null;
            if ($usuario->persona_id) {
                $medico = \App\Models\Medico::where('persona_id', $usuario->persona_id)
                    ->with(['persona', 'especialidades'])
                    ->first();
            }
            
            if (!$medico) {
                // Si no es médico, tomar el primer médico del centro
                $medico = \App\Models\Medico::query()
                    ->with(['persona', 'especialidades'])
                    ->first();
            }
            
            // Si aún no hay médico, tomar cualquier médico disponible
            if (!$medico) {
                $medico = \App\Models\Medico::with(['persona', 'especialidades'])->first();
            }
            
            if ($medico && $medico->especialidades->count() > 0) {
                $especialidad = $medico->especialidades->first()->nombre;
            }
        }
        
        // Si no hay médico, tomar el primer médico disponible como fallback
        if (!$medico) {
            $medico = \App\Models\Medico::with(['persona', 'especialidades'])->first();
            if ($medico && $medico->especialidades->count() > 0) {
                $especialidad = $medico->especialidades->first()->nombre;
            }
        }
        
        // Obtener el primer paciente para la vista previa
        $paciente = \App\Models\Pacientes::with('persona')->first();
        
        // Si no hay pacientes, crear datos de ejemplo
        if (!$paciente) {
            $pacienteData = [
                'nombre' => 'Paciente de Ejemplo',
                'identidad' => '0801-1985-12345',
                'telefono' => '(504) 000-0000',
                'direccion' => 'Dirección de ejemplo'
            ];
        } else {
            $pacienteData = [
                'nombre' => $paciente->persona->nombre_completo,
                'identidad' => $paciente->persona->dni ?? '0000-0000-00000',
                'telefono' => $paciente->persona->telefono ?? 'No disponible',
                'direccion' => $paciente->persona->direccion ?? 'Dirección no disponible'
            ];
        }
        
        // Obtener datos CAI más recientes del centro
        $caiAutorizacion = \App\Models\CAIAutorizaciones::query()
            ->where('estado', 'ACTIVA')
            ->orderBy('created_at', 'desc')
            ->first();
        
        // Datos realistas con información del sistema
        return [
            'numero_factura' => $this->generarNumeroFactura($caiAutorizacion),
            'fecha_emision' => now()->format('d/m/Y'),
            'centro' => [
                'nombre' => $centro ? $centro->nombre : 'Centro Médico',
                'direccion' => $centro ? $centro->direccion : 'Dirección no disponible',
                'telefono' => $centro ? "Tel: {$centro->telefono}" : 'Teléfono no disponible',
                'rtn' => $centro && $centro->rtn ? "RTN: {$centro->rtn}" : 'RTN: No disponible',
                'email' => $centro ? $centro->email : 'email@centromedico.com'
            ],
            'cai' => $caiAutorizacion ? [
                'numero' => $caiAutorizacion->cai,
                'rango_inicial' => $caiAutorizacion->rango_inicial,
                'rango_final' => $caiAutorizacion->rango_final,
                'fecha_limite' => $caiAutorizacion->fecha_limite ? 
                    $caiAutorizacion->fecha_limite->format('d/m/Y') : 
                    now()->addYear()->format('d/m/Y')
            ] : [
                'numero' => '4E2A5B1F-8C9D-4A3B-9E2F-1C8D7A5B9E3F',
                'rango_inicial' => '001-001-01-00000001',
                'rango_final' => '001-001-01-99999999',
                'fecha_limite' => now()->addYear()->format('d/m/Y')
            ],
            'paciente' => $pacienteData,
            'medico' => [
                'nombre' => $medico ? $medico->persona->nombre_completo : 'Dr. Médico de Ejemplo',
                'especialidad' => $especialidad
            ],
            'servicios' => [
                [
                    'descripcion' => 'Consulta Médica General',
                    'cantidad' => 1,
                    'precio_unitario' => 380.00,
                    'total' => 380.00
                ]
            ],
            'subtotal' => 380.00,
            'descuento_total' => 0.00,
            'impuesto_total' => 57.00,
            'total' => 437.00,
            'estado' => 'PENDIENTE',
            'historial_pagos' => [
                [
                    'fecha' => now()->subDays(1)->format('d/m/Y'),
                    'monto' => 200.00,
                    'estado' => 'Efectivo'
                ]
            ],
            'total_pagado' => 200.00,
            'saldo_pendiente' => 237.00
        ];
    }
    
    /**
     * Generar número de factura basado en CAI
     */
    private function generarNumeroFactura($caiAutorizacion)
    {
        if ($caiAutorizacion) {
            // Obtener el último correlativo usado
            $ultimoCorrelativo = \App\Models\CAI_Correlativos::where('cai_autorizacion_id', $caiAutorizacion->id)
                ->orderBy('correlativo_actual', 'desc')
                ->first();
            
            if ($ultimoCorrelativo) {
                $siguienteNumero = $ultimoCorrelativo->correlativo_actual + 1;
                return str_pad($siguienteNumero, 11, '0', STR_PAD_LEFT);
            }
            
            return $caiAutorizacion->rango_inicial;
        }
        
        return '001-001-01-00000001';
    }

    /**
     * Generar factura real con datos de una factura específica
     */
    public function generarFacturaReal(Factura $factura)
    {
        $diseno = $factura->facturaDiseno ?? FacturaDiseno::where('es_predeterminado', true)
                                                      ->first();

        if (!$diseno) {
            return response()->json(['error' => 'No se encontró diseño de factura'], 404);
        }

        // Preparar datos reales de la factura
        $datosFactura = [
            'numero_factura' => $factura->numero_factura ?? 'N/A',
            'fecha_emision' => $factura->fecha_emision->format('d/m/Y'),
            'centro' => [
                'nombre' => $factura->centro->nombre,
                'direccion' => $factura->centro->direccion,
                'telefono' => 'Tel: ' . $factura->centro->telefono,
                'rtn' => 'RTN: ' . $factura->centro->rtn,
                'email' => $factura->centro->email
            ],
            'cai' => $factura->caiAutorizacion ? [
                'numero' => $factura->caiAutorizacion->cai,
                'rango_inicial' => $factura->caiAutorizacion->rango_inicial,
                'rango_final' => $factura->caiAutorizacion->rango_final,
                'fecha_limite' => $factura->caiAutorizacion->fecha_limite->format('d/m/Y')
            ] : null,
        ];
    }
    
    /**
     * Obtener datos específicos de una factura para el PDF
     */
    private function obtenerDatosDeFactura(Factura $factura)
    {
        return [
            // Información de la factura
            'numero_factura' => $factura->usa_cai && $factura->caiCorrelativo 
                ? $factura->caiCorrelativo->numero_factura 
                : $factura->generarNumeroSinCAI(),
            'fecha_emision' => $factura->fecha_emision->format('d/m/Y'),
            'fecha_vencimiento' => $factura->fecha_emision->addDays(30)->format('d/m/Y'),
            
            // Centro médico
            'centro' => [
                'nombre' => $factura->centro->nombre,
                'direccion' => $factura->centro->direccion,
                'telefono' => $factura->centro->telefono,
                'email' => $factura->centro->email,
                'rtn' => $factura->centro->rtn,
                'logo_url' => '/images/logo-clinica.png',
            ],
            
            // Médico
            'medico' => [
                'nombre' => $factura->medico->persona->primer_nombre . ' ' . $factura->medico->persona->primer_apellido,
                'especialidad' => $factura->medico->especialidades->first()->nombre ?? 'Medicina General',
                'numero_colegiacion' => $factura->medico->numero_colegiacion,
            ],
            
            // Paciente
            'paciente' => [
                'nombre' => $factura->paciente->persona->primer_nombre . ' ' . $factura->paciente->persona->primer_apellido,
                'direccion' => $factura->paciente->persona->direccion,
                'telefono' => $factura->paciente->persona->telefono,
                'rtn' => $factura->paciente->persona->identidad,
            ],
            
            // CAI
            'cai' => $factura->caiCorrelativo ? [
                'codigo' => $factura->caiCorrelativo->caiAutorizacion->codigo_cai,
                'rango_desde' => $factura->caiCorrelativo->caiAutorizacion->numero_desde,
                'rango_hasta' => $factura->caiCorrelativo->caiAutorizacion->numero_hasta,
                'fecha_limite' => $factura->caiCorrelativo->caiAutorizacion->fecha_limite_emision->format('d/m/Y'),
            ] : null,
            
            // Servicios/Productos
            'servicios' => $factura->detalles->map(function ($detalle) {
                return [
                    'cantidad' => $detalle->cantidad,
                    'descripcion' => $detalle->servicio->nombre,
                    'precio_unitario' => $detalle->precio_unitario,
                    'subtotal' => $detalle->subtotal,
                ];
            })->toArray(),
            
            // Totales
            'subtotal' => $factura->subtotal,
            'descuento_total' => $factura->descuento_total,
            'impuesto_total' => $factura->impuesto_total,
            'total' => $factura->total,
            'paciente' => [
                'nombre' => $factura->paciente->persona->nombre_completo,
                'identidad' => $factura->paciente->persona->dni,
                'telefono' => $factura->paciente->persona->telefono,
                'direccion' => $factura->paciente->persona->direccion
            ],
            'medico' => [
                'nombre' => $factura->medico->persona->nombre_completo,
                'especialidad' => $factura->medico->especialidades->first()?->nombre ?? 'Medicina General'
            ],
            'servicios' => $factura->detalles->map(function($detalle) {
                return [
                    'descripcion' => $detalle->descripcion,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'total' => $detalle->total
                ];
            })->toArray(),
            'subtotal' => $factura->subtotal,
            'descuento_total' => $factura->descuento_total,
            'impuesto_total' => $factura->impuesto_total,
            'total' => $factura->total,
            'estado' => $factura->estado,
            'historial_pagos' => $factura->pagos->map(function($pago) {
                return [
                    'fecha' => $pago->fecha_pago->format('d/m/Y'),
                    'monto' => $pago->monto,
                    'estado' => $pago->metodo_pago
                ];
            })->toArray(),
            'total_pagado' => $factura->pagos->sum('monto'),
            'saldo_pendiente' => $factura->total - $factura->pagos->sum('monto')
        ];

        $pdf = Pdf::loadView('pdf.factura-template', [
            'diseno' => $diseno,
            'datosFactura' => $datosFactura
        ]);

        $pdf->setPaper($diseno->tamaño_papel ?? 'A4', $diseno->orientacion_papel ?? 'portrait');

        return $pdf->stream("factura-{$factura->id}.pdf");
    }

    /**
     * Mostrar vista previa completa en el navegador
     */
    public function vistaPreviewDemo()
    {
        // Obtener el diseño actual del centro
        $centroId = $this->getCurrentTenantCentroId();

        // Buscar diseño existente para el centro
        $diseno = FacturaDiseno::where('centro_id', $centroId)
            ->where('activo', true)
            ->first();

        // Si no hay diseño, crear uno temporal con valores por defecto
        if (!$diseno) {
            $diseno = new FacturaDiseno([
                'centro_id' => $centroId,
                'nombre' => 'Diseño Principal',
                'descripcion' => 'Diseño principal de facturas para el centro médico',
                'activo' => true,
                'es_predeterminado' => true,
                
                // Colores
                'color_primario' => '#1e40af',
                'color_secundario' => '#64748b',
                'color_acento' => '#059669',
                'color_texto' => '#1f2937',
                'color_borde' => '#e5e7eb',
                'color_titulo' => '#1f2937',
                'color_texto_primario' => '#374151',
                'color_fondo_secundario' => '#f9fafb',
                
                // Tipografía
                'fuente_titulo' => 'Arial Black',
                'fuente_texto' => 'Arial',
                'tamaño_titulo' => 18,
                'tamaño_texto' => 12,
                'tamaño_subtitulo' => 14,
                
                // Logo
                'mostrar_logo' => true,
                'posicion_logo' => 'izquierda',
                'tamaño_logo_ancho' => 120,
                'tamaño_logo_alto' => 80,
                
                // Elementos de la factura
                'mostrar_titulo_factura' => true,
                'texto_titulo_factura' => 'FACTURA',
                'mostrar_numero_factura' => true,
                'mostrar_fecha_emision' => true,
                'mostrar_fecha_vencimiento' => false,
                
                // Información del centro
                'mostrar_info_centro' => true,
                'mostrar_direccion_centro' => true,
                'mostrar_telefono_centro' => true,
                'mostrar_email_centro' => true,
                'mostrar_rtn_centro' => true,
                
                // CAI
                'mostrar_cai' => true,
                'mostrar_rango_cai' => true,
                'mostrar_fecha_limite_cai' => true,
                
                // Información del paciente
                'mostrar_info_paciente' => true,
                'mostrar_direccion_paciente' => true,
                'mostrar_telefono_paciente' => true,
                'mostrar_rtn_paciente' => true,
                
                // Médico
                'mostrar_medico' => true,
                'mostrar_email' => true,
                
                // Tabla de servicios
                'mostrar_tabla_servicios' => true,
                'mostrar_columna_cantidad' => true,
                'mostrar_columna_descripcion' => true,
                'mostrar_columna_precio_unitario' => true,
                'mostrar_columna_total' => true,
                'color_encabezado_tabla' => '#f3f4f6',
                'alternar_color_filas' => true,
                'color_fila_alterna' => '#f9fafb',
                
                // Totales
                'mostrar_subtotal' => true,
                'mostrar_descuentos' => true,
                'mostrar_impuestos' => true,
                'mostrar_total' => true,
                'posicion_totales' => 'derecha',
                'resaltar_total' => true,
                
                // Pie de página
                'mostrar_pie_pagina' => true,
                'texto_pie_pagina' => 'Gracias por su preferencia',
                'mostrar_firma_medico' => false,
                'mostrar_sello_centro' => false,
                
                // Elementos adicionales
                'mostrar_qr_pago' => false,
                'posicion_qr' => 'derecha',
                'mostrar_watermark' => false,
                'texto_watermark' => null,
                'color_watermark' => '#e5e7eb',
                'opacidad_watermark' => 10,
                'posicion_watermark' => 'centro',
                
                // Estados de pago
                'mostrar_historial_pagos' => true,
                'mostrar_estado_factura' => true,
                'mostrar_saldo_pendiente' => true,
            ]);
        }

        // Obtener datos de ejemplo para la factura
        $datosFactura = $this->obtenerDatosReales();

        return view('factura.vista-previa-completa', [
            'diseno' => $diseno,
            'datosFactura' => $datosFactura
        ]);
    }

    private function getCurrentTenantCentroId(): int
    {
        $centroId = tenancy()->initialized ? tenancy()->tenant?->centro_id : null;

        if (! $centroId) {
            throw new \RuntimeException('No hay tenant inicializado para generar vistas de facturas.');
        }

        return (int) $centroId;
    }
}
