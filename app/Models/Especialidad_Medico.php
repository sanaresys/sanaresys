<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Especialidad_Medico extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\EspecialidadMedicoFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $table = 'especialidad_medicos';
    protected $fillable = [
        'medico_id',
        'especialidad_id',
    ];
    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    

    public static function newFactory()
    {
    return \Database\Factories\EspecialidadMedicoFactory::new();
    }
}
