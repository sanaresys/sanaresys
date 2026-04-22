<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;


class User extends Authenticatable implements FilamentUser
{
    
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }
    
    public function centro()
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function medico()
    {
        return $this->hasOne(Medico::class, 'persona_id', 'persona_id');
    }
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'persona_id',
        'created_by', // ID del usuario que creó el registro
        'updated_by', // ID del usuario que actualizó el registro
        'centro_id', // ID del centro médico, puede ser nulo
        
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        parent::booted();

         

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
        
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
        
        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->save();
            }
        });
    }

    /**
     * Verifica si el usuario puede acceder a un centro específico
     */
    public function canAccessCentro($centroId): bool
    {
        // Root puede acceder a cualquier centro
        if ($this->hasRole('root')) {
            return true;
        }

        // En modo domain-only solo existe acceso por tenant inicializado.
        if (function_exists('tenancy') && tenancy()->initialized && tenancy()->tenant?->centro_id) {
            return (int) tenancy()->tenant->centro_id === (int) $centroId;
        }

        return false;
    }

    /**
     * Obtiene todos los centros a los que el usuario puede acceder
     */
    public function getAccessibleCentros()
    {
        if ($this->hasRole('root')) {
            return \App\Models\Centros_Medico::query()
                ->where('tenancy_mode', 'domain')
                ->orderBy('nombre_centro')
                ->get();
        }

        if (function_exists('tenancy') && tenancy()->initialized && tenancy()->tenant?->centro_id) {
            return \App\Models\Centros_Medico::query()
                ->where('id', tenancy()->tenant->centro_id)
                ->get();
        }

        return collect();
    }

    /**
     * Obtiene los roles del usuario para un centro específico
     */
    public function getRolesForCentro($centroId)
    {
        // Si es root, tiene todos los permisos
        if ($this->hasRole('root')) {
            return $this->roles;
        }

        // Si no puede acceder al centro, no tiene roles
        if (!$this->canAccessCentro($centroId)) {
            return collect();
        }

        // Retorna los roles del usuario (podrías extender esto para roles específicos por centro)
        return $this->roles;
    }

    public function scopeHideRoot($query)
    {
        if (!auth()->user()?->hasRole('root')) {
            return $query->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'root');
            });
        }
        return $query;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->hasRole('root')) {
            return true;
        }

        if (function_exists('tenancy') && tenancy()->initialized) {
            return $this->roles()->exists();
        }

        return false;
    }

   
}
