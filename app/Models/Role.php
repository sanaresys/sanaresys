<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'guard_name',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            config('permission.table_names.role_has_permissions'),
            config('permission.column_names.role_morph_key'),
            'permission_id'
        );
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Centros_Medico::class, 'centro_id', 'id');
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($model) {
            if (! $model->guard_name) {
                $model->guard_name = 'web';
            }
        });
    }
}
