<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Impuesto extends ModeloBase
{
    use HasFactory, SoftDeletes;
    // El contexto tenant define el centro
    
    protected $fillable = [
        'nombre',
        'porcentaje',
        'vigente_desde',
        'vigente_hasta',
        // centro_id removido - la BD del tenant ya define el centro
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($model) {
            // Solo agregar centro_id si NO estamos en contexto de tenant
            if (!tenancy()->initialized && auth()->check() && empty($model->centro_id)) {
                $user = auth()->user();
                if ($user && isset($user->centro_id)) {
                    $model->centro_id = $user->centro_id;
                }
            }
        });
    }
}
