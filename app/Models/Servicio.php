<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Servicio extends ModeloBase
{
    use HasFactory, SoftDeletes;
    // El contexto tenant define el centro

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'precio_unitario',
        'impuesto_id',
        'es_exonerado',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Relaciones
    public function Centros_Medico(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function impuesto(): BelongsTo
    {
        return $this->belongsTo(Impuesto::class);
    }

    public function facturasDetalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class);
    }

    // El contexto tenant define el centro

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];


    // MÃ©todos auxiliares
    public function calcularImpuesto(?float $subtotal = null): float
    {
        if ($this->es_exonerado === 'SI') {
            return 0.0;
        }

        $monto = $subtotal ?? $this->precio_unitario;

        // Solo usar el impuesto de la relaciÃ³n
        if ($this->impuesto && $this->impuesto->porcentaje > 0) {
            return ($monto * $this->impuesto->porcentaje) / 100;
        }

        return 0.0;
    }

    public function getPrecioConImpuestoAttribute(): float
    {
        return $this->precio_unitario + $this->calcularImpuesto();
    }
    
    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    // El contexto tenant define el centro

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($model) {
            // Solo agregar centro_id si NO estamos en contexto de tenant
            // (las bases de tenants no tienen columna centro_id)
            if (!tenancy()->initialized && auth()->check() && empty($model->centro_id)) {
                $user = auth()->user();
                if ($user && isset($user->centro_id)) {
                    $model->centro_id = $user->centro_id;
                }
            }
        });

        static::creating(function ($model) {

            if (empty($model->codigo)) {

                $centroBase = explode('-', (string) $model->centro_id)[0];   // "12"
                $prefijo    = 'SER' . $centroBase;                           // "SER12"

                $ultimoCodigo = static::query()
                    ->where('codigo', 'like', $prefijo . '%')   // SER12%
                    ->orderBy('codigo', 'desc')
                    ->value('codigo');                          // p.e.  SER120007

                $siguienteNumero = $ultimoCodigo
                    ? (int) substr($ultimoCodigo, strlen($prefijo)) + 1
                    : 1;

                $model->codigo = $prefijo . str_pad($siguienteNumero, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
