<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\TenantScoped;

class CAIAutorizaciones extends ModeloBase
{
    use HasFactory, SoftDeletes;
    // TenantScoped NO se usa - el contexto del tenant define el centro

    protected $table = 'cai_autorizaciones';

    // TenantScoped NO se usa - el contexto del tenant define el centro

    protected string $tenantKeyName = 'centro_id';

    // TenantScoped NO se usa - el contexto del tenant define el centro

    protected $fillable = [
        'rtn',
        'cai_codigo',
        'cantidad',
        'rango_inicial',
        'rango_final',
        'numero_actual',
        'fecha_limite',
        'estado',
        'centro_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // TenantScoped NO se usa - el contexto del tenant define el centro

    protected $casts = [
        'fecha_limite' => 'date',
        'cantidad' => 'integer',
        'rango_inicial' => 'integer',
        'rango_final' => 'integer',
        'numero_actual' => 'integer',
    ];

    // Relaciones
    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function caiCorrelativos(): HasMany
    {
        return $this->hasMany(CAI_Correlativos::class, 'autorizacion_id');
    }

    public function numerosDisponibles(): int
    {
        // Si numero_actual es null, significa que no se ha usado ninguno
        if (is_null($this->numero_actual)) {
            return $this->cantidad;
        }
        
        // Los números disponibles son desde numero_actual hasta rango_final (inclusive)
        return max(0, $this->rango_final - $this->numero_actual + 1);
    }

    public function porcentajeUtilizado(): float
    {
        if ($this->cantidad <= 0) return 0;
        
        // Si numero_actual es null, no se ha usado ninguno
        if (is_null($this->numero_actual)) {
            return 0;
        }
        
        // Los números utilizados son desde rango_inicial hasta numero_actual - 1
        // porque numero_actual apunta al SIGUIENTE número a usar
        $utilizados = $this->numero_actual - $this->rango_inicial;
        return ($utilizados / $this->cantidad) * 100;
    }

    public function incrementarNumero(): bool
    {
        if (is_null($this->numero_actual))
            $this->numero_actual = $this->rango_inicial;
        else
            $this->increment('numero_actual');

        if (!$this->esValida()) {
            return false;
        }

        $this->increment('numero_actual');
        
        // Verificar si se agotó
        if ($this->numero_actual > $this->rango_final) {
            $this->update(['estado' => 'AGOTADA']);
        }

        return true;
    }

    // Agregar estos métodos al modelo CAIAutorizaciones existente

    public function obtenerSiguienteNumero(): ?int
    {
        if (!$this->esValida()) {
            return null;
        }

        return $this->numero_actual;
    }

    public function consumirNumero(): bool
    {
        if (!$this->esValida()) {
            return false;
        }

        $this->increment('numero_actual');
        
        // Verificar si se agotó
        if ($this->numero_actual > $this->rango_final) {
            $this->update(['estado' => 'AGOTADA']);
        }

        return true;
    }

    public function esValida(): bool
    {
        return $this->estado === 'ACTIVA' 
            && $this->fecha_limite >= now()->toDateString()
            && $this->numero_actual <= $this->rango_final;
    }

    // TenantScoped NO se usa - el contexto del tenant define el centro

    protected static function booted(): void
    {
        parent::booted();

        // ── 1. Al CREAR/ACTUALIZAR recalculamos «cantidad» ───────────────────────
        static::saving(function ($model) {
            $model->cantidad = max(
                0,
                (int) $model->rango_final - (int) $model->rango_inicial + 1
            );
        });

        // ── 2. Cuando el número sube, verificamos agotado o vencido ──────────────
        static::updated(function ($model) {
            $model->refrescarEstado();   // método nuevo que vemos abajo
        });

                static::creating(function ($model) {
            if (auth()->check() && empty($model->centro_id)) {
                $user = auth()->user();
                if ($user && isset($user->centro_id)) {
                    $model->centro_id = $user->centro_id;
                }
            }
        });

        // Verificar fecha de vencimiento
        static::updating(function ($model) {
            if ($model->fecha_limite < now()->toDateString() && $model->estado === 'ACTIVA') {
                $model->estado = 'VENCIDA';
            }
        });
    }

    /** Marca VENCIDA o AGOTADA según corresponda.  */
    public function refrescarEstado(): void
    {
        $hoy      = now()->toDateString();
        $excedido = $this->numero_actual > $this->rango_final;

        $nuevoEstado = match (true) {
            $this->fecha_limite < $hoy       => 'VENCIDA',
            $excedido                        => 'AGOTADA',
            default                          => 'ACTIVA',
        };

        if ($nuevoEstado !== $this->estado) {
            $this->estado = $nuevoEstado;
            $this->saveQuietly();
        }
    }

}
