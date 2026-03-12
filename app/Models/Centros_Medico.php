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
        'billing_status',
        'billing_plan_code',
        'billing_renews_at',
        'billing_last_sync_at',
        'billing_override',
        'onboarding_completed_at',
        'onboarding_current_step',
        'onboarding_skipped_cai',
        'direccion',
        'telefono',
        'email',
        'rtn',
        'fotografia',
    ];

    protected $casts = [
        'onboarding_completed_at' => 'datetime',
        'billing_renews_at' => 'datetime',
        'billing_last_sync_at' => 'datetime',
        'onboarding_current_step' => 'integer',
        'onboarding_skipped_cai' => 'boolean',
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

    public function billingSubscriptions(): HasMany
    {
        return $this->hasMany(BillingSubscription::class, 'centro_id');
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class, 'centro_id');
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

            if (! $centro->billing_status) {
                $centro->billing_status = 'inactive';
            }
        });
    }

    public function isBillingActive(): bool
    {
        return $this->billing_status === 'active';
    }

    // Método para usar en selects de Filament
    public static function getSelectOptions()
    {
        return static::all()->mapWithKeys(function ($centro) {
            return [$centro->id => $centro->nombre_centro ?? "Centro ID: {$centro->id}"];
        })->toArray();
    }
}
