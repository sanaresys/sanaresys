<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
class Enfermedades__Paciente extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\EnfermedadesPacienteFactory> */
    use HasFactory;
    use SoftDeletes;
    // El contexto tenant define el centro

    protected $table = 'enfermedades_pacientes';

    protected $fillable = [
        'paciente_id',
        'enfermedad_id',
        'fecha_diagnostico',
        'tratamiento',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'fecha_diagnostico' => 'date',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Pacientes::class, 'paciente_id');
    }

    public function enfermedad(): BelongsTo
    {
        return $this->belongsTo(Enfermedade::class, 'enfermedad_id');
    }

    // Relaciones para mostrar quiÃ©n creÃ³/editÃ³
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

   
}
