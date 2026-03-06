<?php

namespace App\Livewire;

use App\Models\FacturaDiseno;
use Livewire\Component;

class FacturaVistaPrevia extends Component
{
    public $disenoId;
    public $datosFactura;

    public $color_primario = '#1e40af';
    public $color_secundario = '#64748b';
    public $color_acento = '#059669';
    public $color_texto = '#1f2937';

    public $fuente_titulo = 'Arial Black';
    public $fuente_texto = 'Arial';
    public $tamaÃ±o_titulo = 18;
    public $tamaÃ±o_texto = 12;
    public $tamaÃ±o_subtitulo = 14;

    public $mostrar_logo = true;
    public $posicion_logo = 'izquierda';
    public $tamaÃ±o_logo_ancho = 120;
    public $tamaÃ±o_logo_alto = 80;

    public $mostrar_titulo_factura = true;
    public $texto_titulo_factura = 'FACTURA';
    public $mostrar_numero_factura = true;
    public $mostrar_fecha_emision = true;

    public $mostrar_info_centro = true;
    public $mostrar_direccion_centro = true;
    public $mostrar_telefono_centro = true;
    public $mostrar_rtn_centro = true;

    public $mostrar_cai = true;
    public $posicion_cai = 'superior';
    public $mostrar_rango_cai = true;
    public $mostrar_fecha_limite_cai = true;

    public $mostrar_info_paciente = true;
    public $etiqueta_cliente = 'FACTURAR A:';
    public $mostrar_direccion_paciente = true;
    public $mostrar_telefono_paciente = true;
    public $mostrar_rtn_paciente = true;

    public $color_encabezado_tabla = '#f3f4f6';
    public $alternar_color_filas = true;
    public $color_fila_alterna = '#f9fafb';

    public $mostrar_subtotal = true;
    public $mostrar_descuentos = true;
    public $mostrar_impuestos = true;
    public $mostrar_total = true;
    public $posicion_totales = 'derecha';
    public $resaltar_total = true;

    public $mostrar_pie_pagina = true;
    public $texto_pie_pagina = 'Gracias por confiar en nuestros servicios medicos';
    public $mostrar_qr_pago = false;
    public $posicion_qr = 'derecha';

    public $css_personalizado = '';

    protected $listeners = ['actualizarVista', 'cargarDisenoId', 'recargarDatos'];

    public function recargarDatos()
    {
        $this->cargarDatosReales();
        $this->emit('vistaActualizada');
    }

    public function mount($disenoId = null)
    {
        $this->disenoId = $disenoId;
        $this->cargarDatosReales();

        if ($disenoId) {
            $this->cargarDiseno($disenoId);
        }
    }

    private function cargarDatosReales(): void
    {
        if ($this->disenoId) {
            $facturaEjemplo = $this->obtenerFacturaEjemplo();
            if ($facturaEjemplo) {
                $this->cargarDatosDeFactura($facturaEjemplo);
                return;
            }
        }

        $this->cargarDatosGenericos();
    }

    private function obtenerFacturaEjemplo()
    {
        $facturaConDiseno = \App\Models\Factura::where('factura_diseno_id', $this->disenoId)
            ->with([
                'paciente.persona',
                'medico.persona',
                'centro',
                'detalles.servicio',
                'caiCorrelativo.caiAutorizacion',
            ])
            ->latest()
            ->first();

        if ($facturaConDiseno) {
            return $facturaConDiseno;
        }

        return \App\Models\Factura::query()
            ->with([
                'paciente.persona',
                'medico.persona',
                'centro',
                'detalles.servicio',
                'caiCorrelativo.caiAutorizacion',
            ])
            ->latest()
            ->first();
    }

    private function cargarDatosDeFactura($factura): void
    {
        $this->datosFactura = [
            'numero_factura' => $factura->usa_cai && $factura->caiCorrelativo
                ? $factura->caiCorrelativo->numero_factura
                : $factura->generarNumeroSinCAI(),
            'fecha_emision' => $factura->fecha_emision->format('d/m/Y'),
            'fecha_vencimiento' => $factura->fecha_emision->addDays(30)->format('d/m/Y'),
            'centro' => [
                'nombre' => $factura->centro->nombre,
                'direccion' => $factura->centro->direccion,
                'telefono' => $factura->centro->telefono,
                'email' => $factura->centro->email,
                'rtn' => $factura->centro->rtn,
                'logo_url' => '/images/logo-clinica.png',
            ],
            'medico' => [
                'nombre' => ($factura->medico->persona->primer_nombre ?? '') . ' ' . ($factura->medico->persona->primer_apellido ?? ''),
                'especialidad' => $factura->medico->especialidades->first()->nombre ?? 'Medicina General',
                'numero_colegiacion' => $factura->medico->numero_colegiacion,
            ],
            'paciente' => [
                'nombre' => ($factura->paciente->persona->primer_nombre ?? '') . ' ' . ($factura->paciente->persona->primer_apellido ?? ''),
                'direccion' => $factura->paciente->persona->direccion ?? null,
                'telefono' => $factura->paciente->persona->telefono ?? null,
                'rtn' => $factura->paciente->persona->identidad ?? null,
            ],
            'cai' => $factura->caiCorrelativo ? [
                'codigo' => $factura->caiCorrelativo->caiAutorizacion->codigo_cai ?? null,
                'rango_desde' => $factura->caiCorrelativo->caiAutorizacion->numero_desde ?? null,
                'rango_hasta' => $factura->caiCorrelativo->caiAutorizacion->numero_hasta ?? null,
                'fecha_limite' => optional($factura->caiCorrelativo->caiAutorizacion->fecha_limite_emision)->format('d/m/Y'),
            ] : null,
            'servicios' => $factura->detalles->map(function ($detalle) {
                return [
                    'cantidad' => $detalle->cantidad,
                    'descripcion' => $detalle->servicio->nombre,
                    'precio_unitario' => $detalle->precio_unitario,
                    'subtotal' => $detalle->subtotal,
                ];
            })->toArray(),
            'subtotal' => $factura->subtotal,
            'descuento_total' => $factura->descuento_total,
            'impuesto_total' => $factura->impuesto_total,
            'total' => $factura->total,
        ];
    }

    private function cargarDatosGenericos(): void
    {
        $centroId = $this->getCurrentTenantCentroId();
        $centro = \App\Models\Centros_Medico::on('mysql')->find($centroId);

        $usuario = auth()->user();
        $medico = null;
        $especialidad = 'Medicina General';

        if ($usuario && $usuario->persona_id) {
            $medico = \App\Models\Medico::where('persona_id', $usuario->persona_id)
                ->with(['persona', 'especialidades'])
                ->first();
        }

        if (! $medico) {
            $medico = \App\Models\Medico::with(['persona', 'especialidades'])->first();
        }

        if ($medico && $medico->especialidades->count() > 0) {
            $especialidad = $medico->especialidades->first()->nombre;
        }

        $paciente = \App\Models\Pacientes::with('persona')->first();
        if (! $paciente) {
            $pacienteData = [
                'nombre' => 'Paciente de Ejemplo',
                'identidad' => '0801-1985-12345',
                'telefono' => '(504) 000-0000',
                'direccion' => 'Direccion de ejemplo',
            ];
        } else {
            $pacienteData = [
                'nombre' => $paciente->persona->nombre_completo,
                'identidad' => $paciente->persona->dni ?? '0000-0000-00000',
                'telefono' => $paciente->persona->telefono ?? 'No disponible',
                'direccion' => $paciente->persona->direccion ?? 'Direccion no disponible',
            ];
        }

        $caiAutorizacion = \App\Models\CAIAutorizaciones::query()
            ->where('estado', 'ACTIVA')
            ->orderBy('created_at', 'desc')
            ->first();

        $this->datosFactura = [
            'numero_factura' => $this->generarNumeroFactura($caiAutorizacion),
            'fecha_emision' => now()->format('d/m/Y'),
            'centro' => [
                'nombre' => $centro ? $centro->nombre : 'Centro Medico',
                'direccion' => $centro ? $centro->direccion : 'Direccion no disponible',
                'telefono' => $centro ? "Tel: {$centro->telefono}" : 'Telefono no disponible',
                'rtn' => $centro && $centro->rtn ? "RTN: {$centro->rtn}" : 'RTN: No disponible',
                'email' => $centro ? $centro->email : 'email@centromedico.com',
            ],
            'cai' => $caiAutorizacion ? [
                'numero' => $caiAutorizacion->cai,
                'rango_inicial' => $caiAutorizacion->rango_inicial,
                'rango_final' => $caiAutorizacion->rango_final,
                'fecha_limite' => $caiAutorizacion->fecha_limite
                    ? $caiAutorizacion->fecha_limite->format('d/m/Y')
                    : now()->addYear()->format('d/m/Y'),
            ] : [
                'numero' => '4E2A5B1F-8C9D-4A3B-9E2F-1C8D7A5B9E3F',
                'rango_inicial' => '001-001-01-00000001',
                'rango_final' => '001-001-01-99999999',
                'fecha_limite' => now()->addYear()->format('d/m/Y'),
            ],
            'paciente' => $pacienteData,
            'medico' => [
                'nombre' => $medico ? $medico->persona->nombre_completo : 'Dr. Medico de Ejemplo',
                'especialidad' => $especialidad,
            ],
            'servicios' => [[
                'descripcion' => 'Consulta Medica General',
                'cantidad' => 1,
                'precio_unitario' => 380.00,
                'total' => 380.00,
            ]],
            'subtotal' => 380.00,
            'descuento_total' => 0.00,
            'impuesto_total' => 57.00,
            'total' => 437.00,
            'estado' => 'PENDIENTE',
            'historial_pagos' => [[
                'fecha' => now()->subDays(1)->format('d/m/Y'),
                'monto' => 200.00,
                'estado' => 'Efectivo',
            ]],
            'total_pagado' => 200.00,
            'saldo_pendiente' => 237.00,
        ];
    }

    private function generarNumeroFactura($caiAutorizacion)
    {
        if ($caiAutorizacion) {
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

    public function cargarDiseno($disenoId)
    {
        $diseno = FacturaDiseno::find($disenoId);
        if ($diseno) {
            foreach ($diseno->toArray() as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    public function actualizarVistaPrevia($datos)
    {
        foreach ($datos as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function render()
    {
        $diseno = null;

        if ($this->disenoId) {
            $diseno = FacturaDiseno::find($this->disenoId);
        }

        if (! $diseno) {
            $diseno = FacturaDiseno::query()
                ->where('activo', true)
                ->where('es_predeterminado', true)
                ->first();
        }

        if (! $diseno) {
            $diseno = $this->crearDisenoBasico();
        }

        return view('livewire.factura-vista-previa', [
            'diseno' => $diseno,
        ]);
    }

    private function crearDisenoBasico()
    {
        return (object) [
            'color_primario' => '#1e40af',
            'color_secundario' => '#64748b',
            'color_acento' => '#059669',
            'color_texto' => '#1f2937',
            'fuente_titulo' => 'Arial Black',
            'fuente_texto' => 'Arial',
            'tamaÃ±o_titulo' => 18,
            'tamaÃ±o_texto' => 12,
            'tamaÃ±o_subtitulo' => 14,
            'mostrar_logo' => true,
            'logo_url' => null,
            'posicion_logo' => 'izquierda',
            'tamaÃ±o_logo_ancho' => 120,
            'tamaÃ±o_logo_alto' => 80,
            'mostrar_titulo_factura' => true,
            'texto_titulo_factura' => 'FACTURA',
            'mostrar_numero_factura' => true,
            'mostrar_fecha_emision' => true,
            'mostrar_fecha_vencimiento' => false,
            'mostrar_info_centro' => true,
            'mostrar_direccion_centro' => true,
            'mostrar_telefono_centro' => true,
            'mostrar_email_centro' => true,
            'mostrar_rtn_centro' => true,
            'mostrar_cai' => true,
            'mostrar_rango_cai' => true,
            'mostrar_fecha_limite_cai' => true,
            'mostrar_info_paciente' => true,
            'mostrar_direccion_paciente' => true,
            'mostrar_telefono_paciente' => true,
            'mostrar_rtn_paciente' => true,
            'mostrar_medico' => true,
            'mostrar_email' => true,
            'color_borde' => null,
            'color_titulo' => null,
            'color_texto_primario' => null,
            'color_fondo_secundario' => null,
            'color_fondo_tabla' => null,
            'color_texto_secundario' => null,
            'margenes' => ['top' => 20, 'right' => 15, 'bottom' => 20, 'left' => 15],
            'espaciado_lineas' => 5,
            'espaciado_secciones' => 15,
            'mostrar_tabla_servicios' => true,
            'mostrar_columna_cantidad' => true,
            'mostrar_columna_descripcion' => true,
            'mostrar_columna_precio_unitario' => true,
            'mostrar_columna_total' => true,
            'color_encabezado_tabla' => null,
            'alternar_color_filas' => true,
            'color_fila_alterna' => null,
            'mostrar_subtotal' => true,
            'mostrar_descuentos' => true,
            'mostrar_impuestos' => true,
            'mostrar_total' => true,
            'posicion_totales' => 'derecha',
            'resaltar_total' => true,
            'mostrar_pie_pagina' => true,
            'texto_pie_pagina' => 'Gracias por su preferencia',
            'mostrar_firma_medico' => false,
            'mostrar_sello_centro' => false,
            'mostrar_qr_pago' => false,
            'posicion_qr' => 'derecha',
            'mostrar_watermark' => false,
            'texto_watermark' => null,
            'color_watermark' => '#e5e7eb',
            'opacidad_watermark' => 10,
            'posicion_watermark' => 'centro',
            'mostrar_historial_pagos' => true,
            'mostrar_estado_factura' => true,
            'mostrar_saldo_pendiente' => true,
        ];
    }

    private function getCurrentTenantCentroId(): int
    {
        $centroId = tenancy()->initialized ? tenancy()->tenant?->centro_id : null;

        if (! $centroId) {
            throw new \RuntimeException('No hay tenant inicializado para la vista previa de factura.');
        }

        return (int) $centroId;
    }
}

