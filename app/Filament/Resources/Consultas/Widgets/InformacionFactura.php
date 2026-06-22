<?php

namespace App\Filament\Resources\Consultas\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class InformacionFactura extends Widget
{
    protected static string $view = 'filament.resources.consultas.widgets.informacion-factura';

    public $record;

    protected function getViewData(): array
    {
        // Información de contexto
        $pacienteNombre = 'Paciente no encontrado';
        $medicoNombre = 'Médico no encontrado';
        $centroNombre = Auth::user()->centro?->nombre_centro ?? 'Centro Médico';
        $fecha = now()->format('d/m/Y');
        $numeroFactura = 'Se generará automáticamente';
        $fechaEmision = now()->format('d/m/Y');
        $estado = 'Pendiente';

        if ($this->record) {
            $consulta = $this->record;
            
            if ($consulta->paciente && $consulta->paciente->persona) {
                $pacienteNombre = $consulta->paciente->persona->nombre_completo;
            }
            
            if ($consulta->medico && $consulta->medico->persona) {
                $medicoNombre = $consulta->medico->persona->nombre_completo;
            }
            
            if ($consulta->centro) {
                $centroNombre = $consulta->centro->nombre_centro;
            }
            
            $fecha = $consulta->created_at->format('d/m/Y');
            
            // Si ya tiene factura, mostrar información de la factura
            $factura = $consulta->facturas()->first();
            if ($factura) {
                if ($factura->usa_cai && $factura->caiCorrelativo) {
                    $numeroFactura = $factura->caiCorrelativo->numero_factura;
                } else {
                    $numeroFactura = $factura->generarNumeroSinCAI();
                }
                $fechaEmision = $factura->fecha_emision->format('d/m/Y');
                $estado = $factura->estado;
            }
        }
        
        return [
            'consulta' => $this->record,
            'pacienteNombre' => $pacienteNombre,
            'medicoNombre' => $medicoNombre,
            'centroNombre' => $centroNombre,
            'fecha' => $fecha,
            'numeroFactura' => $numeroFactura,
            'fechaEmision' => $fechaEmision,
            'estado' => $estado,
        ];
    }
}
