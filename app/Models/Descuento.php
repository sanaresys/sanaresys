<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Descuento extends ModeloBase
{
    use HasFactory, SoftDeletes;
    // TenantScoped NO se usa - el contexto del tenant define el centro

    protected $fillable = [
        'nombre',
        'tipo',
        'valor',
        'aplica_desde',
        'aplica_hasta',
        'activo',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // TenantScoped NO se usa - el contexto del tenant define el centro

    protected $dates = [
        'aplica_desde',
        'aplica_hasta',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    // TenantScoped NO se usa - el contexto del tenant define el centro

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($model) {
            if (auth()->check() && empty($model->centro_id)) {
                $user = auth()->user();
                if ($user && isset($user->centro_id)) {
                    $model->centro_id = $user->centro_id;
                }
            }
        });
    }
}