<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
class Examenes extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\ExamenesFactory> */
    use HasFactory;
    use SoftDeletes;
    // El contexto tenant define el centro

    protected $table = 'examenes';
    protected $fillable = [
        'paciente_id',
        'consulta_id',
        'medico_id',
        'tipo_examen',
        'observaciones',
        'estado',
        'imagen_resultado',
        'fecha_completado',
        'fecha_resultado',
    ];

    protected $casts = [
        'fecha_completado' => 'datetime',
        'fecha_resultado' => 'date',
    ];

    protected $attributes = [
        'estado' => 'Solicitado',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($examen) {
            // Asegurar que siempre tenga un estado por defecto
            if (empty($examen->estado)) {
                $examen->estado = 'Solicitado';
            }
        });
    }

    // Relaciones
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

    public function centro()
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    // Accessor para la URL de la imagen
    public function getImagenUrlAttribute()
    {
        if ($this->imagen_resultado) {
            return Storage::disk('public')->url($this->imagen_resultado);
        }
        return null;
    }

    // MÃ©todo para obtener el color del estado
    public function getColorEstadoAttribute()
    {
        return match($this->estado) {
            'Solicitado' => 'warning',
            'Completado' => 'success',
            'No presentado' => 'danger',
            default => 'secondary'
        };
    }

    // Scope para filtrar por mÃ©dico
    public function scopePorMedico($query, $medicoId)
    {
        return $query->where('medico_id', $medicoId);
    }

    // Scope para filtrar por estado
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    // Scope para exÃ¡menes que deben aparecer en nuevas consultas
    public function scopeParaNuevasConsultas($query)
    {
        return $query->whereIn('estado', ['Solicitado', 'Completado']);
    }

    // Scope para exÃ¡menes previos de un paciente (excluyendo la consulta actual)
    public function scopeExamenesPrevios($query, $pacienteId, $consultaActualId = null)
    {
        $query = $query->where('paciente_id', $pacienteId)
                      ->paraNuevasConsultas()
                      ->with(['medico.persona', 'consulta']);
        
        if ($consultaActualId) {
            $query = $query->where('consulta_id', '!=', $consultaActualId);
        }
        
        return $query->orderBy('created_at', 'desc');
    }

    // MÃ©todo para verificar si se puede subir imagen
    public function puedeSubirImagen()
    {
        return $this->estado === 'Solicitado';
    }

    // MÃ©todo para verificar si se puede cambiar a "No presentado"
    public function puedeMarcarNoPresent()
    {
        return in_array($this->estado, ['Solicitado', 'Completado']);
    }

    // MÃ©todo para completar examen con imagen
    public function completarConImagen($rutaImagen)
    {
        $this->update([
            'imagen_resultado' => $rutaImagen,
            'estado' => 'Completado',
            'fecha_completado' => now(),
            'fecha_resultado' => now()->toDateString()
        ]);
    }

    // MÃ©todo para marcar como no presentado
    public function marcarNoPresent()
    {
        $this->update([
            'estado' => 'No presentado'
        ]);
    }

    // MÃ©todo para eliminar imagen anterior si existe
    public function eliminarImagenAnterior()
    {
        if ($this->imagen_resultado && Storage::disk('public')->exists($this->imagen_resultado)) {
            Storage::disk('public')->delete($this->imagen_resultado);
        }
    }
}

