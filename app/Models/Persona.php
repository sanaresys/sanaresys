<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use app\Models\Centros_Medicos\Centro;

class Persona extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\PersonaFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'dni',
        'telefono',
        'direccion',
        'sexo',
        'fecha_nacimiento',
        'nacionalidad_id',
        'fotografia',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected static function booted()
    {
        parent::booted();
        
        // Validación de DNI único (funcionalidad específica del modelo Persona)
        static::saving(function ($model) {
            $query = static::where('dni', $model->dni);
            
            if ($model->exists) {
                $query->where('id', '!=', $model->id);
            }
            
            if ($query->exists()) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], []), 
                    ['dni' => 'El DNI ya está en uso.']
                );
            }
        });
    }

    public function getNombreCompletoAttribute()
    {
        return trim("{$this->primer_nombre} {$this->segundo_nombre} {$this->primer_apellido} {$this->segundo_apellido}");
    }

    /**
     * Accesor para nombre simple (solo primer nombre y primer apellido)
     */
    public function getNombreSimpleAttribute()
    {
        return $this->primer_nombre . ' ' . $this->primer_apellido;
    }

    /**
     * Accesor para la URL de la foto
     */
    public function getFotoUrlAttribute()
    {
        if (empty($this->fotografia)) {
            return $this->generateDefaultAvatar();
        }

        // Si ya es una URL completa (como un avatar generado)
        if (filter_var($this->fotografia, FILTER_VALIDATE_URL)) {
            return $this->fotografia;
        }

        // Verificar si el archivo existe en el almacenamiento
        if (Storage::disk('public')->exists($this->fotografia)) {
            return Storage::disk('public')->url($this->fotografia);
        }

        return $this->generateDefaultAvatar();
    }

    /**
     * Mutador para limpieza automática de la ruta de la foto
     */
    public function setFotografiaAttribute($value)
    {
        if (is_array($value) && isset($value['path'])) {
            $value = $value['path'];
        }

        if (is_string($value)) {
            // Limpiar la ruta de prefijos comunes
            $value = str_replace([
                'storage/',
                'public/',
                Storage::disk('public')->url('')
            ], '', $value);

            // Asegurar que comience con 'personas/fotos/'
            if (!str_starts_with($value, 'personas/fotos/')) {
                $value = 'personas/fotos/' . ltrim($value, '/');
            }
        }

        $this->attributes['fotografia'] = $value ?: null;
    }

    /**
     * Mutador para primer_nombre - solo letras
     */
    public function setPrimerNombreAttribute($value)
    {
        // Limpiar y validar que solo contenga letras
        $cleaned = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/', '', $value);
        $this->attributes['primer_nombre'] = trim($cleaned);
    }

    /**
     * Mutador para segundo_nombre - solo letras
     */
    public function setSegundoNombreAttribute($value)
    {
        if (!empty($value)) {
            $cleaned = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/', '', $value);
            $this->attributes['segundo_nombre'] = trim($cleaned);
        } else {
            $this->attributes['segundo_nombre'] = null;
        }
    }

    /**
     * Mutador para primer_apellido - solo letras
     */
    public function setPrimerApellidoAttribute($value)
    {
        $cleaned = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/', '', $value);
        $this->attributes['primer_apellido'] = trim($cleaned);
    }

    /**
     * Mutador para segundo_apellido - solo letras
     */
    public function setSegundoApellidoAttribute($value)
    {
        if (!empty($value)) {
            $cleaned = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/', '', $value);
            $this->attributes['segundo_apellido'] = trim($cleaned);
        } else {
            $this->attributes['segundo_apellido'] = null;
        }
    }

    /**
     * Generar avatar por defecto
     */
    private function generateDefaultAvatar()
    {
        $iniciales = strtoupper(substr($this->primer_nombre, 0, 1) . substr($this->primer_apellido, 0, 1));
        $colores = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8'];
        $color = $colores[array_rand($colores)];
        
        return "https://ui-avatars.com/api/?name={$iniciales}&background=" . substr($color, 1) . "&color=fff&size=100&font-size=0.5";
    }

    /**
     * Relación con nacionalidad
     */
    public function nacionalidad(): BelongsTo
    {
        return $this->belongsTo(Nacionalidad::class);
    }

    /**
     * Relación con paciente
     */
    public function paciente(): HasOne
    {
        return $this->hasOne(Pacientes::class, 'persona_id');
    }

    /**
     * Relación con médico
     */
    public function medico(): HasOne
    {
        return $this->hasOne(Medico::class, 'persona_id');
    }

    /**
     * Relación con usuario
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'persona_id');
    }
   
    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    /**
     * Scope para buscar por nombre completo
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('primer_nombre', 'like', "%{$search}%")
              ->orWhere('segundo_nombre', 'like', "%{$search}%")
              ->orWhere('primer_apellido', 'like', "%{$search}%")
              ->orWhere('segundo_apellido', 'like', "%{$search}%")
              ->orWhere('dni', 'like', "%{$search}%");
        });
    }

    /**
     * Scope para filtrar por centro
     */
    public function scopePorCentro($query, $centroId)
    {
        return $query->where('centro_id', $centroId);
    }

    /**
     * Scope para filtrar por sexo
     */
    public function scopePorSexo($query, $sexo)
    {
        return $query->where('sexo', $sexo);
    }

    /**
     * Limpiar archivo de fotografía anterior cuando se actualiza
     */
    protected static function bootedd()
    {
        static::updating(function ($persona) {
            if ($persona->isDirty('fotografia')) {
                $original = $persona->getOriginal('fotografia');
                if ($original && Storage::disk('public')->exists($original)) {
                    Storage::disk('public')->delete($original);
                }
            }
        });

        static::deleting(function ($persona) {
            if ($persona->fotografia && Storage::disk('public')->exists($persona->fotografia)) {
                Storage::disk('public')->delete($persona->fotografia);
            }
        });
    }
}