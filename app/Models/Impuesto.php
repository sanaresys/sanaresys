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

    
}
