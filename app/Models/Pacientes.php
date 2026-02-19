<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pacientes extends ModeloBase
{
    use HasFactory, SoftDeletes;
    // TenantScoped NO se usa - el contexto del tenant define el centro

    protected $fillable = [
        'persona_id',
        'grupo_sanguineo',
        'contacto_emergencia',
        // centro_id removido - la BD del tenant ya define el centro
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Scope para filtrar pacientes por el centro del usuario autenticado
     */
    public function scopeForCurrentUser($query)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && $user->centro_id) {
            return $query->where('centro_id', $user->centro_id);
        }
        return $query;
    }

    public function centro()
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function persona(){
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Citas::class, 'paciente_id');
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class, 'paciente_id');
    }

    // Relación muchos a muchos con Enfermedade
    public function enfermedades()
    {
        return $this->belongsToMany(
            Enfermedade::class,
            'enfermedades_pacientes',
            'paciente_id',
            'enfermedad_id'
        )->withPivot(['fecha_diagnostico', 'tratamiento']);
    }
}