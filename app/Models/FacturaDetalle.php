<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaDetalle extends Model
{
    protected $table = 'factura_detalles';
    
    protected $fillable = [
        'factura_id',
        'consulta_id',
        'servicio_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'impuesto_id',
        'impuesto_monto',
        'descuento_monto',
        'total_linea',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'impuesto_monto' => 'decimal:2',
        'descuento_monto' => 'decimal:2',
        'total_linea' => 'decimal:2',
    ];

    // ✅ NUEVO: Boot method para asegurar total_linea
    protected static function booted(): void
    {
        parent::booted();
        
        static::creating(function (self $detalle) {
            if (!$detalle->total_linea || $detalle->total_linea <= 0) {
                $detalle->total_linea = $detalle->subtotal + ($detalle->impuesto_monto ?? 0) - ($detalle->descuento_monto ?? 0);
            }
        });
        
        static::updating(function (self $detalle) {
            if (!$detalle->total_linea || $detalle->total_linea <= 0) {
                $detalle->total_linea = $detalle->subtotal + ($detalle->impuesto_monto ?? 0) - ($detalle->descuento_monto ?? 0);
            }
        });
    }

    // Relaciones
    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
    }

    public function impuesto(): BelongsTo
    {
        return $this->belongsTo(Impuesto::class);
    }
}