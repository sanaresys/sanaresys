<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TipoPago extends ModeloBase        
{
    use HasFactory, SoftDeletes;       
    // El contexto tenant define el centro
    protected $fillable = [
        'nombre',
        'descripcion',
        'created_by',   
        'updated_by',    
        'deleted_by',   
    ];

}

