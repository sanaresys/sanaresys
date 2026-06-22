<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Descuento extends ModeloBase
{
    use HasFactory, SoftDeletes;
    // El contexto tenant define el centro

    protected $fillable = [
        'nombre',
        'tipo',
        'valor',
        'aplica_desde',
        'aplica_hasta',
        'activo',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // El contexto tenant define el centro

    protected $dates = [
        'aplica_desde',
        'aplica_hasta',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // El contexto tenant define el centro

    
}
