<?php

namespace App\Models\ContabilidadMedica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\ModeloBase;
use App\Models\Medico;
use App\Models\Centros_Medico;
use Illuminate\Database\Eloquent\Builder;


class ContratoMedico extends ModeloBase
{
    use HasFactory;
    use SoftDeletes;
    
    protected static function booted()
    {
        parent::booted();
        static::addGlobalScope('relaciones', function (Builder $builder) {
            $builder->with(['centro', 'medico.persona']);
        });
        // La validación se maneja en el formulario para mejor experiencia de usuario
    }

    protected $table = 'contratos_medicos';

    protected $fillable = [
        'medico_id',
        'salario_quincenal',
        'salario_mensual',
        'porcentaje_servicio',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'observaciones',
        'centro_id',
    ];

    protected $casts = [
        'porcentaje_servicio' => 'decimal:2',
        'salario_quincenal' => 'decimal:2',
        'salario_mensual' => 'decimal:2',
        'activo' => 'boolean',
    ];

    protected $attributes = [
        'porcentaje_servicio' => 0,
        'salario_quincenal' => 0,
        'salario_mensual' => 0,
    ];

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    // public function cargos(): HasMany
    // {
    //     return $this->hasMany(CargoMedico::class, 'contrato_id');
    // }
    
    /**
     * Determina si el contrato es solo por porcentaje de servicio
     *
     * @return bool
     */
    public function esSoloPorcentaje(): bool
    {
        return ($this->porcentaje_servicio > 0) && 
               ($this->salario_quincenal == 0 && $this->salario_mensual == 0);
    }
    
    /**
     * Determina si el contrato es solo por salario
     *
     * @return bool
     */
    public function esSoloSalario(): bool
    {
        return ($this->porcentaje_servicio == 0) && 
               (($this->salario_quincenal > 0) || ($this->salario_mensual > 0));
    }
    
    /**
     * Determina si el contrato es mixto (porcentaje y salario)
     *
     * @return bool
     */
    public function esMixto(): bool
    {
        return ($this->porcentaje_servicio > 0) && 
               (($this->salario_quincenal > 0) || ($this->salario_mensual > 0));
    }
    
    /**
     * Obtiene el tipo de contrato en formato legible
     *
     * @return string
     */
    public function getTipoContratoAttribute(): string
    {
        if ($this->esSoloPorcentaje()) {
            return "Solo por porcentaje de servicio ({$this->porcentaje_servicio}%)";
        } elseif ($this->esSoloSalario()) {
            return "Solo por salario";
        } elseif ($this->esMixto()) {
            return "Mixto (salario + {$this->porcentaje_servicio}%)";
        } else {
            return "No especificado";
        }
    }
}
