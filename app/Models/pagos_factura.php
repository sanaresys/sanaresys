<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagosFactura extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pagos_facturas';

    protected $fillable = [
        'factura_id',
        'paciente_id',
        'centro_id',
        'tipo_pago_id',
        'monto_recibido',
        'monto_devolucion',
        'fecha_pago',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['fecha_pago','created_at','updated_at','deleted_at'];

    /* ─────────────  R E L A C I O N E S  ───────────── */
    public function paciente()  : BelongsTo { return $this->belongsTo(Pacientes::class); }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }

    public function tipoPago(): BelongsTo
    {
        return $this->belongsTo(TipoPago::class, 'tipo_pago_id');
    }

    protected static function booted(): void
    {
        parent::booted();

        // Actualizar estado de factura cuando se crea/actualiza/elimina un pago
        static::created(function (self $pago) {
            $pago->factura?->actualizarEstadoPago();
        });

        static::updated(function (self $pago) {
            $pago->factura?->actualizarEstadoPago();
        });

               static::deleted(function (self $pago) {
            $pago->factura?->actualizarEstadoPago();
        });
    }

}
