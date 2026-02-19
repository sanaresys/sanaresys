<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
class Receta extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\RecetaFactory> */
    use HasFactory;
    use SoftDeletes;
    // TenantScoped NO se usa - el contexto del tenant define el centro

    protected $table = 'recetas';

    protected $fillable = [
        'medicamentos',
        'indicaciones',
        'paciente_id',
        'consulta_id',
        'medico_id',
        'fecha_receta', // Nueva columna para la fecha de la receta
    ];

    protected $casts = [
        'fecha_receta' => 'date',
    ];

    

    public function paciente()
    {
        return $this->belongsTo(Pacientes::class, 'paciente_id');
    }

    public function consulta()
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }
}
