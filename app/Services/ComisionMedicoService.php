<?php

namespace App\Services;

use App\Models\Factura;
use App\Models\Medico;
use App\Models\ContabilidadMedica\ContratoMedico;
use App\Models\FacturaDetalle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ComisionMedicoService
{
    /**
     * Calcula la comisión por servicios de un médico en un periodo específico
     * 
     * @param int $medicoId ID del médico
     * @param int $año Año del periodo
     * @param int $mes Mes del periodo
     * @param int|null $quincena Número de quincena (opcional)
     * @return array Datos de comisión y facturas
     */
    public function calcularComision(int $medicoId, int $año, int $mes, ?int $quincena = null): array
    {
        // Obtener el médico y su contrato activo
        $medico = Medico::with(['contratoActivo', 'persona'])->find($medicoId);
        
        if (!$medico || !$medico->contratoActivo) {
            return [
                'medico' => $medico,
                'porcentaje_servicio' => 0,
                'total_facturado' => 0,
                'total_comision' => 0,
                'facturas' => collect([]),
                'detalle' => []
            ];
        }
        
        // Obtener el porcentaje de comisión del contrato
        $porcentaje = $medico->contratoActivo->porcentaje_servicio ?? 0;
        
        if ($porcentaje <= 0) {
            return [
                'medico' => $medico,
                'porcentaje_servicio' => $porcentaje,
                'total_facturado' => 0,
                'total_comision' => 0,
                'facturas' => collect([]),
                'detalle' => []
            ];
        }
        
        // Construir la consulta base para facturas del médico en el periodo
        $query = Factura::where('medico_id', $medicoId)
            ->whereYear('fecha_emision', $año)
            ->whereMonth('fecha_emision', $mes)
            ->where('estado', '!=', 'ANULADA')
            ->with(['detalles.servicio', 'paciente.persona']);
        
        // Filtrar por quincena si es necesario
        if ($quincena) {
            $diaInicio = $quincena === 1 ? 1 : 16;
            $diaFin = $quincena === 1 ? 15 : 31;
            
            $query->whereBetween(DB::raw('DAY(fecha_emision)'), [$diaInicio, $diaFin]);
        }
        
        // Obtener las facturas
        $facturas = $query->get();
        
        // Calcular totales y comisión
        $totalFacturado = 0;
        $detalleComisiones = [];
        
        foreach ($facturas as $factura) {
            $subtotalFactura = $factura->subtotal; // Usar el subtotal para el cálculo (sin impuestos)
            $comisionFactura = ($subtotalFactura * $porcentaje) / 100;
            
            $totalFacturado += $subtotalFactura;
            
            // Agregar al detalle
            $detalleComisiones[] = [
                'factura_id' => $factura->id,
                'numero_factura' => $factura->numero_factura,
                'fecha' => $factura->fecha_emision->format('d/m/Y'),
                'paciente' => $factura->paciente->persona->nombre_completo ?? 'Paciente',
                'subtotal' => $subtotalFactura,
                'comision' => $comisionFactura,
            ];
        }
        
        $totalComision = ($totalFacturado * $porcentaje) / 100;
        
        return [
            'medico' => $medico,
            'porcentaje_servicio' => $porcentaje,
            'total_facturado' => $totalFacturado,
            'total_comision' => $totalComision,
            'facturas' => $facturas,
            'detalle' => $detalleComisiones
        ];
    }
}
