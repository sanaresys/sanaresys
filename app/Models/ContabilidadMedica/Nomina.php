<?php

namespace App\Models\ContabilidadMedica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ModeloBase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Nomina extends ModeloBase
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'nominas';

    protected $fillable = [
        'empresa',
        'año',
        'mes',
        'tipo_pago',
        'quincena',
        'descripcion',
        'cerrada',
        'estado',
        'centro_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'cerrada' => 'boolean',
        'año' => 'integer',
        'mes' => 'integer',
    ];

    protected $attributes = [
        'cerrada' => false,
        'estado' => 'abierta',
        'tipo_pago' => 'mensual',
    ];

    /**
     * Relación con los detalles de nómina
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleNomina::class, 'nomina_id');
    }

    /**
     * Scope para nóminas abiertas
     */
    public function scopeAbiertas(Builder $query): Builder
    {
        return $query->where('cerrada', false);
    }

    /**
     * Scope para nóminas cerradas
     */
    public function scopeCerradas(Builder $query): Builder
    {
        return $query->where('cerrada', true);
    }

    /**
     * Scope para filtrar por año
     */
    public function scopePorAño(Builder $query, int $año): Builder
    {
        return $query->where('año', $año);
    }

    /**
     * Scope para filtrar por mes
     */
    public function scopePorMes(Builder $query, int $mes): Builder
    {
        return $query->where('mes', $mes);
    }

    /**
     * Obtener el nombre del mes
     */
    public function getNombreMesAttribute(): string
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        return $meses[$this->mes] ?? '';
    }

    /**
     * Obtener el total de la nómina
     */
    public function getTotalNominaAttribute(): float
    {
        return $this->detalles()->sum('total_pagar');
    }

    /**
     * Obtener el número de médicos en la nómina
     */
    public function getNumeroEmpleadosAttribute(): int
    {
        return $this->detalles()->count();
    }

    /**
     * Cerrar la nómina
     */
    public function cerrar(): bool
    {
        return $this->update([
            'cerrada' => true,
            'estado' => 'cerrada'
        ]);
    }

    /**
     * Abrir la nómina
     */
    public function abrir(): bool
    {
        return $this->update([
            'cerrada' => false,
            'estado' => 'abierta'
        ]);
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->created_by && Auth::check()) {
                $model->created_by = Auth::id();
            }
        });
    }
}
