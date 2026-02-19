<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Siempre usar la conexión central
     */
    protected $connection = 'mysql';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'id',
        'centro_id',
        'data',
    ];

    /**
     * Columnas personalizadas del tenant
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'centro_id',
        ];
    }

    /**
     * Relación con centro médico
     */
    public function centro()
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    /**
     * Obtiene o crea un tenant para un centro médico
     */
    public static function findOrCreateForCentro(Centros_Medico $centro): self
    {
        return static::firstOrCreate(
            ['centro_id' => $centro->id],
            [
                'id' => 'centro_' . $centro->id,
                'centro_id' => $centro->id,
            ]
        );
    }

    /**
     * Obtiene el nombre de la base de datos del tenant
     */
    public function getTenantKey()
    {
        return $this->id;
    }

    /**
     * Nombre de la base de datos del tenant
     */
    public function getDatabaseName(): string
    {
        return $this->id;    }
}