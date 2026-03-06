<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CuentasPorCobrar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cuentas_por_cobrars';

    protected $fillable = [
        'factura_id',
        'saldo_pendiente',
        'fecha_vencimiento',
        'estado_cuentas_por_cobrar',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = [
        'fecha_vencimiento',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'saldo_pendiente' => 'decimal:2',
        'fecha_vencimiento' => 'date',
    ];

    // Relaciones
    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }

    // Relación a través de factura - el paciente se obtiene de la factura
    public function paciente(): HasOneThrough
    {
        return $this->hasOneThrough(
            Pacientes::class,
            Factura::class,
            'id', // Foreign key en factura
            'id', // Foreign key en pacientes
            'factura_id', // Local key en cuentas_por_cobrar
            'paciente_id' // Local key en factura
        );
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    // Obtener todos los pagos de la factura asociada
    public function pagosFactura(): HasMany
    {
    return $this->hasMany(PagosFactura::class, 'factura_id', 'factura_id');
    }

    // Métodos
    public function estaPendiente(): bool
    {
        return in_array($this->estado_cuentas_por_cobrar, ['PENDIENTE', 'PARCIAL']) && $this->saldo_pendiente > 0;
    }

    public function estaVencida(): bool
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento->isPast() && $this->estaPendiente();
    }

    public function estaPagada(): bool
    {
        return $this->estado_cuentas_por_cobrar === 'PAGADA' || $this->saldo_pendiente <= 0;
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($model) {
            // Solo agregar centro_id si NO estamos en contexto de tenant
            if (!tenancy()->initialized && Auth::check() && empty($model->centro_id)) {
                $user = Auth::user();
                if ($user && isset($user->centro_id)) {
                    $model->centro_id = $user->centro_id;
                }
            }

            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check() && empty($model->updated_by)) {
                $model->updated_by = Auth::id();
            }
        });
    }
}
