<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nacionalidad extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\NacionalidadFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $table = 'nacionalidades';
    protected $fillable = [
        'nacionalidad',
    ];

    public function personas(): HasMany{
        return $this->hasMany(Persona::class);
    }

    
}
