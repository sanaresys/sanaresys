<?php

namespace App\Services;

use App\Models\{Factura, PagosFactura, Cuentas_Por_Cobrar};
use Illuminate\Support\Facades\DB;

class FacturaPagoService
{
    /** Registra un pago y actualiza factura + cuenta por cobrar */
    public static function registrarPago(
        Factura $factura,
        float   $montoRecibido,
        ?int    $tipoPagoId = null,
        ?int    $usuarioId  = null
    ): PagosFactura {

        return DB::transaction(function () use ($factura, $montoRecibido, $tipoPagoId, $usuarioId) {

            // 1. Crear el pago
            $pago = PagosFactura::create([
                'factura_id'      => $factura->id,
                'paciente_id'     => $factura->paciente_id,
                'centro_id'       => $factura->centro_id,
                'tipo_pago_id'    => $tipoPagoId,
                'monto_recibido'  => $montoRecibido,
                'monto_devolucion'=> max(0, $montoRecibido - $factura->saldoPendiente()),
                'fecha_pago'      => now(),
                'created_by'      => $usuarioId,
            ]);

            // 2. Recalcular saldo y estado de factura
            $factura->actualizarEstadoPago();

            // 3. Actualizar / crear cuenta por cobrar
            if ($factura->estado === 'PAGADA') {
                Cuentas_Por_Cobrar::where('factura_id', $factura->id)
                    ->update(['saldo_pendiente' => 0, 'estado_cuentas_por_cobrar' => 'CERRADA']);
            } else {
                Cuentas_Por_Cobrar::updateOrCreate(
                    ['factura_id' => $factura->id],
                    [
                        'paciente_id'   => $factura->paciente_id,
                        'pagos_factura_id'=> $pago->id,
                        'saldo_pendiente'=> $factura->saldoPendiente(),
                        'fecha_vencimiento'=> now()->addDays(30),
                        'estado_cuentas_por_cobrar'=> $factura->saldoPendiente() > 0 ? 'ABIERTA' : 'CERRADA',
                        'centro_id'     => $factura->centro_id,
                        'created_by'    => $usuarioId,
                    ]
                );
            }

            return $pago;
        });
    }
}
