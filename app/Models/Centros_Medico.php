<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Centros_Medico extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\CentrosMedicoFactory> */
    use HasFactory;
    use SoftDeletes;

    // Siempre usar la conexión central (no tenant)
    protected $connection = 'mysql';

    protected $table = 'centros_medicos';

    protected $fillable = [
        'nombre_centro',
        'slug',
        'tenancy_mode',
        'onboarding_completed_at',
        'direccion',
        'telefono',
        'rtn',
        'fotografia',
    ];

    protected $casts = [
        'onboarding_completed_at' => 'datetime',
    ];

    public function centro_medico_medico() {
        return $this->hasMany(Centros_Medicos_Medico::class, 'centro_medico_id');
    }
    
    public function medicos()
    {
        return $this->hasMany(
            \App\Models\Centros_Medicos_Medico::class,
            'centro_medico_id'
        );
    }

    // Accesores
    public function getNombreAttribute()
    {
        return $this->nombre_centro ?? 'Sin nombre';
    }

    protected static function booted()
    {
        static::creating(function (self $centro) {
            if (! $centro->tenancy_mode) {
                $centro->tenancy_mode = 'legacy';
            }
        });
    }

    // Método para usar en selects de Filament
    public static function getSelectOptions()
    {
        return static::all()->mapWithKeys(function ($centro) {
            return [$centro->id => $centro->nombre_centro ?? "Centro ID: {$centro->id}"];
        })->toArray();
    }
}
