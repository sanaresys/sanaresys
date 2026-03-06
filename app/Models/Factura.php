<?php

namespace App\Models;

use App\Services\CaiNumerador;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
class Factura extends ModeloBase
{
    use HasFactory;
    use SoftDeletes;
    // El contexto tenant define el centro

    protected $fillable = [
        'paciente_id',
        'cita_id',
        'consulta_id',
        'medico_id',
        'fecha_emision',
        'subtotal',
        'descuento_total',
        'impuesto_total',
        'total',
        'estado',
        'observaciones',
        'cai_autorizacion_id',
        'descuento_id',
        'cai_correlativo_id',
        'usa_cai',
        'factura_diseno_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'subtotal' => 'decimal:2',
        'descuento_total' => 'decimal:2',
        'impuesto_total' => 'decimal:2',
        'total' => 'decimal:2',
        'usa_cai' => 'boolean',
    ];

    // Relaciones
    public function facturaDiseno(): BelongsTo
    {
        return $this->belongsTo(FacturaDiseno::class, 'factura_diseno_id');
    }

    public function caiCorrelativo(): BelongsTo
    {
        return $this->belongsTo(CAI_Correlativos::class, 'cai_correlativo_id');
    }
    
    public function caiAutorizacion(): BelongsTo
    {
        return $this->belongsTo(CAIAutorizaciones::class, 'cai_autorizacion_id');
    }
    
    public function paciente(): BelongsTo 
    { 
        return $this->belongsTo(Pacientes::class); 
    }
    
    public function descuento(): BelongsTo 
    { 
        return $this->belongsTo(Descuento::class); 
    }
    
    public function cita(): BelongsTo 
    { 
        return $this->belongsTo(Citas::class); 
    }
    
    public function consulta(): BelongsTo 
    { 
        return $this->belongsTo(Consulta::class); 
    }
    
    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class);
    }

    public function pagos(): HasMany
    {
    return $this->hasMany(PagosFactura::class);
    }

    public function cuentasPorCobrar(): HasOne
    {
        return $this->hasOne(CuentasPorCobrar::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // Accessor para nÃºmero de factura
    public function getNumeroFacturaAttribute(): string
    {
        if ($this->usa_cai && $this->caiCorrelativo) {
            return $this->caiCorrelativo->numero_factura;
        }
        
        // Para facturas sin CAI, generar nÃºmero simple
        return $this->generarNumeroSinCAI();
    }

    // Accessor para cÃ³digo CAI
    public function getCodigoCaiAttribute(): ?string
    {
        return $this->usa_cai && $this->caiAutorizacion 
            ? $this->caiAutorizacion->cai_codigo 
            : null;
    }

    public function generarNumeroSinCAI(): string
    {
        // Obtener el Ãºltimo nÃºmero de factura sin CAI del centro actual
        $ultimaFactura = static::where('usa_cai', false)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($ultimaFactura && isset($ultimaFactura->numero_sin_cai)) {
            $ultimoNumero = (int) $ultimaFactura->numero_sin_cai;
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }
        
        // Actualizar el campo numero_sin_cai en la factura actual
        $this->update(['numero_sin_cai' => $nuevoNumero]);
        
        // Formatear como PROV-YYYY-MM-NNNNNN
        return sprintf('PROV-%s-%06d', 
            now()->format('Y-m'), 
            $nuevoNumero
        );
    }
    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $factura) {
            // Establecer centro y auditorÃ­a
            if (Auth::check()) {
                $factura->centro_id ??= Auth::user()->centro_id;
                $factura->created_by ??= Auth::id();
            }

            // Si usa CAI, generar correlativo
            if ($factura->usa_cai) {
                try {
                    Log::info('Generando CAI para factura', [
                        'factura_id' => $factura->id,
                        'centro_id' => $factura->centro_id
                    ]);
                    
                    $cai = CaiNumerador::obtenerCAIDisponible($factura->centro_id);
                    
                    if (!$cai) {
                        throw new \Exception('No hay CAI disponible para este centro');
                    }

                    Log::info('CAI encontrado', ['cai_id' => $cai->id, 'cai_codigo' => $cai->cai_codigo]);

                    $correlativo = CaiNumerador::generar(
                        caiId: $cai->id,
                        usuarioId: Auth::id() ?? 1,
                        centroId: $factura->centro_id
                    );

                    Log::info('Correlativo generado', ['correlativo_id' => $correlativo->id]);

                    $factura->cai_correlativo_id = $correlativo->id;
                    $factura->cai_autorizacion_id = $cai->id;
                    
                } catch (\Exception $e) {
                    // Si falla, convertir a factura sin CAI
                    Log::error('Error generando CAI', ['error' => $e->getMessage()]);
                    $factura->usa_cai = false;
                    $factura->cai_correlativo_id = null;
                    $factura->cai_autorizacion_id = null;
                    
                    // Opcional: registrar el error o notificar
                    Log::warning("No se pudo generar CAI para factura: " . $e->getMessage());
                }
            } else {
                Log::info('Factura creada sin CAI', ['factura_id' => $factura->id]);
            }
        });

        static::created(function (self $factura) {
            // NO crear cuenta por cobrar automÃ¡ticamente
            // Se crearÃ¡ despuÃ©s cuando sea necesario (si queda saldo pendiente)
        });
    }

    // MÃ©todos de pago
    public function montoPagado(): float
    {
        return $this->pagos()
            ->whereNull('deleted_at')
            ->sum('monto_recibido');
    }

    public function saldoPendiente(): float
    {
        return $this->total - $this->montoPagado();
    }

    public function actualizarEstadoPago(): void
    {
        $montoPagado = $this->montoPagado();
        
        // Calcular el total real con descuento aplicado
        $totalConDescuento = $this->subtotal + $this->impuesto_total - $this->descuento_total;
        $saldoPendiente = max(0, $totalConDescuento - $montoPagado);
        
        // Actualizar estado de la factura
        if ($montoPagado == 0) {
            $this->estado = 'PENDIENTE';
        } elseif ($montoPagado >= $totalConDescuento) {
            $this->estado = 'PAGADA';
        } else {
            $this->estado = 'PARCIAL';
        }
        
        $this->save();
        
        // CREAR o ACTUALIZAR cuenta por cobrar SOLO si hay saldo pendiente
        if ($saldoPendiente > 0) {
            // Buscar cuenta por cobrar existente
            $this->load('cuentasPorCobrar');
            $cuentaPorCobrar = $this->cuentasPorCobrar;
            
            if (!$cuentaPorCobrar) {
                // Si no existe, buscar manualmente
                $cuentaPorCobrar = CuentasPorCobrar::where('factura_id', $this->id)->first();
            }
            
            if ($cuentaPorCobrar) {
                // Actualizar cuenta existente
                $estadoCuenta = ($montoPagado > 0) ? 'PARCIAL' : 'PENDIENTE';
                
                $cuentaPorCobrar->update([
                    'saldo_pendiente' => $saldoPendiente,
                    'estado_cuentas_por_cobrar' => $estadoCuenta,
                    'updated_by' => auth()->id(),
                ]);
            } else {
                // Crear nueva cuenta por cobrar solo si hay saldo pendiente
                $estadoCuenta = ($montoPagado > 0) ? 'PARCIAL' : 'PENDIENTE';
                
                CuentasPorCobrar::create([
                    'factura_id' => $this->id,
                    'saldo_pendiente' => $saldoPendiente,
                    'fecha_vencimiento' => now()->addDays(30),
                    'centro_id' => $this->centro_id,
                    'estado_cuentas_por_cobrar' => $estadoCuenta,
                    'created_by' => auth()->id() ?? $this->created_by,
                ]);
            }
        } else {
            // Si no hay saldo pendiente, marcar cuenta existente como PAGADA o eliminarla
            $this->load('cuentasPorCobrar');
            $cuentaPorCobrar = $this->cuentasPorCobrar;
            
            if (!$cuentaPorCobrar) {
                $cuentaPorCobrar = CuentasPorCobrar::where('factura_id', $this->id)->first();
            }
            
            if ($cuentaPorCobrar) {
                $cuentaPorCobrar->update([
                    'saldo_pendiente' => 0,
                    'estado_cuentas_por_cobrar' => 'PAGADA',
                    'updated_by' => auth()->id(),
                ]);
            }
        }
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    /**
     * Generar PDF de la factura
     */
    public function generarPdf()
    {
        return app(\App\Http\Controllers\FacturaPdfController::class)->generarPdf($this);
    }

    /**
     * Obtener el total pagado de la factura
     */
    public function getTotalPagadoAttribute(): float
    {
        return $this->pagos->sum('monto_recibido');
    }

    /**
     * Obtener el saldo pendiente de la factura
     */
    public function getSaldoPendienteAttribute(): float
    {
        return $this->total - $this->total_pagado;
    }

    /**
     * Verificar si la factura estÃ¡ completamente pagada
     */
    public function estaPagada(): bool
    {
        return $this->saldo_pendiente <= 0;
    }

    /**
     * Verificar si la factura tiene pagos parciales
     */
    public function tienePagosParciales(): bool
    {
        return $this->total_pagado > 0 && $this->saldo_pendiente > 0;
    }

    /**
     * Obtener el estado de pago legible
     */
    public function getEstadoPagoAttribute(): string
    {
        if ($this->estaPagada()) {
            return 'PAGADA';
        } elseif ($this->tienePagosParciales()) {
            return 'PARCIAL';
        } else {
            return 'PENDIENTE';
        }
    }
}
