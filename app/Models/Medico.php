<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Medico extends ModeloBase
{
    use HasFactory;
    use SoftDeletes;

protected $table = 'medicos';

    protected $fillable = [
        'persona_id',
        'numero_colegiacion',
        'horario_entrada',  
        'horario_salida',
    ];

    public function centro()
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }
    public function user()
    {
        return $this->hasOneThrough(User::class, Persona::class, 'id', 'persona_id', 'persona_id', 'id');
    }


    public function centrosMedicos()
    {
        return $this->belongsToMany(Centros_Medico::class, 'centros_medicos_medicos', 'medico_id', 'centro_medico_id');
    }

    public function persona() {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function consultas() {
    return $this->hasMany(Consulta::class, 'medico_id');
    }

    public function recetas() {
    return $this->hasMany(Receta::class, 'medico_id');
    }

    public function recetarios() {
        return $this->hasMany(Recetario::class, 'medico_id');
    }

    public function recetario() {
        return $this->hasOne(Recetario::class, 'medico_id')->latest();
    }

    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'especialidad_medicos', 'medico_id', 'especialidad_id');
    }
    
    public function contratos()
    {
        return $this->hasMany(\App\Models\ContabilidadMedica\ContratoMedico::class, 'medico_id');
    }

    public function contratoActivo()
    {
        return $this->hasOne(\App\Models\ContabilidadMedica\ContratoMedico::class, 'medico_id')
            ->where('activo', true)
            ->whereNull('fecha_fin')
            ->orWhere('fecha_fin', '>=', now())
            ->latest('fecha_inicio');
    }

    public function contratosActivos()
    {
        return $this->hasMany(\App\Models\ContabilidadMedica\ContratoMedico::class, 'medico_id')
            ->where('activo', true);
    }
}
