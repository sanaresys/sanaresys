<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Recetario extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\RecetarioFactory> */
    use HasFactory;
    use SoftDeletes;
    // El contexto tenant define el centro

    protected $table = 'recetarios';

    protected $fillable = [
        'medico_id',
        'consulta_id',
        'numero_recetario',
        'observaciones_generales',
        'estado',
        'fecha_emision',
        'fecha_vencimiento',
        'tiene_recetario',
        'logo',
        'color_primario',
        'color_secundario',
        'fuente_familia',
        'fuente_tamano',
        'mostrar_logo',
        'mostrar_especialidades',
        'mostrar_telefono',
        'mostrar_direccion',
        'titulo',
        'nombre_mostrar',
        'telefono_mostrar',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'tiene_recetario' => 'boolean',
        'mostrar_logo' => 'boolean',
        'mostrar_especialidades' => 'boolean',
        'mostrar_telefono' => 'boolean',
        'mostrar_direccion' => 'boolean',
        'configuracion_avanzada' => 'array'
    ];

    // No usamos accessors/mutators para evitar problemas
    // En su lugar, manualmente manejamos el logo en el controlador

    public function consulta()
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }
    public function recetas()
    {
        return $this->hasMany(Receta::class, 'recetario_id');
    }

    public function centro()
    {
        return $this->belongsTo(CentroMedico::class, 'centro_id');
    }

    // Accessor para datos del paciente (muy Ãºtil)
    public function getDatosPacienteAttribute(): ?object
    {
        if (!$this->consulta || !$this->consulta->paciente) {
            return null;
        }

        $paciente = $this->consulta->paciente;
        $persona = $paciente->persona;
        
        return (object) [
            'id' => $paciente->id,
            'nombre_completo' => trim("{$persona->primer_nombre} {$persona->segundo_nombre} {$persona->primer_apellido} {$persona->segundo_apellido}"),
            'dni' => $persona->dni,
            'telefono' => $persona->telefono,
            'edad' => $persona->fecha_nacimiento?->age ?? null,
            'sexo' => $persona->sexo,
        ];
    }

    // Accessor para datos del mÃ©dico
    public function getDatosMedicoAttribute(): ?object
    {
        if (!$this->medico || !$this->medico->persona) {
            return null;
        }

        $persona = $this->medico->persona;
        
        return (object) [
            'nombre_completo' => trim("{$persona->primer_nombre} {$persona->segundo_nombre} {$persona->primer_apellido} {$persona->segundo_apellido}"),
            'especialidad' => $this->medico->especialidad,
            'dni' => $persona->dni,
        ];
    }

}

