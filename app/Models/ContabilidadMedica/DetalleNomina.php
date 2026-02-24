<?php

namespace App\Models\ContabilidadMedica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Medico;

class DetalleNomina extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'detalle_nominas';

    protected $fillable = [
        'nomina_id',
        'medico_id',
        'medico_nombre',
        'salario_base',
        'deducciones',
        'percepciones',
        'total_pagar',
        'deducciones_detalle',
        'percepciones_detalle',
        'centro_id',
    ];

    protected $guarded = [
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'salario_base' => 'decimal:2',
        'deducciones' => 'decimal:2',
        'percepciones' => 'decimal:2',
        'total_pagar' => 'decimal:2',
    ];

    /**
     * Relacion con la nomina
     */
    public function nomina(): BelongsTo
    {
        return $this->belongsTo(Nomina::class, 'nomina_id');
    }

    /**
     * Relacion con el medico
     */
    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    /**
     * Obtener el salario neto calculado
     */
    public function getSalarioNetoAttribute(): float
    {
        return $this->salario_base + $this->percepciones - $this->deducciones;
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Calcular el total automaticamente
        static::saving(function ($detalle) {
            $detalle->total_pagar = $detalle->salario_base + $detalle->percepciones - $detalle->deducciones;
        });
    }

    /**
     * Sobreescribir create para filtrar created_by
     */
    public static function create(array $attributes = [])
    {
        unset($attributes['created_by']);
        unset($attributes['updated_by']);
        unset($attributes['deleted_by']);

        return static::query()->create($attributes);
    }
}
