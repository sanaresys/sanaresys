<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant Model
    |--------------------------------------------------------------------------
    */
    'tenant_model' => \App\Models\Tenant::class,

    /*
    |--------------------------------------------------------------------------
    | Central Connection
    |--------------------------------------------------------------------------
    |
    | Base connection used to create tenant databases.
    |
    */
    'central_connection' => env('TENANCY_CENTRAL_CONNECTION', env('DB_CONNECTION', 'mysql')),

    /*
    |--------------------------------------------------------------------------
    | Tenant Connection
    |--------------------------------------------------------------------------
    |
    | Runtime connection name used for tenant operations.
    |
    */
    'tenant_connection' => env('TENANCY_TENANT_CONNECTION', 'tenant'),

    /*
    |--------------------------------------------------------------------------
    | Tenancy Bootstrappers
    |--------------------------------------------------------------------------
    | Executed when tenancy is initialized to make Laravel features tenant-aware.
    */
    'bootstrappers' => [
        Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
        Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class,
        Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
        Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    */
    'database' => [
        'central_connection' => env('DB_CONNECTION', 'mysql'),
        'prefix' => env('TENANCY_DATABASE_PREFIX', ''),
        'suffix' => env('TENANCY_DATABASE_SUFFIX', ''),
        'managers' => [
            'mysql' => Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Seeders
    |--------------------------------------------------------------------------
    |
    | Seeders executed right after tenant migrations.
    | Los roles y permisos están en la BD central, no en tenants.
    |
    */
    'seeders' => [
        // Vacío - Los datos del tenant se crean desde la aplicación
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Parameters
    |--------------------------------------------------------------------------
    |
    | Parameters used by the tenants:migrate command.
    | Cada tenant tendrá su copia completa de todas las tablas
    |
    */
    'migration_parameters' => [
        '--force' => true,
        '--path' => [database_path('migrations/tenant')],
        '--realpath' => true,
    ],
];