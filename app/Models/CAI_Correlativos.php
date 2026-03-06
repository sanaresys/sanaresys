<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CAI_Correlativos extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cai_correlativos';

    protected $fillable = [
        'autorizacion_id',
        'numero_correlativo',
        'numero_factura',
        'fecha_emision',
        'usuario_id',
        'centro_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'numero_correlativo' => 'integer',
    ];

    // Relaciones
    public function autorizacion(): BelongsTo
    {
        return $this->belongsTo(CAIAutorizaciones::class, 'autorizacion_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    // Métodos auxiliares
    public function getNumeroFacturaFormateadoAttribute(): string
    {
        return $this->numero_factura;
    }

    public function getCorrelativoFormateadoAttribute(): string
    {
        return str_pad($this->numero_correlativo, 9, '0', STR_PAD_LEFT);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopePorCentro($query, $centroId)
    {
        return $query->where('centro_id', $centroId);
    }
}
