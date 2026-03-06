<?php

use Stancl\Tenancy\Database\Models\Domain;

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant Model
    |--------------------------------------------------------------------------
    */
    'tenant_model' => \App\Models\Tenant::class,
    'domain_model' => Domain::class,

    /*
    |--------------------------------------------------------------------------
    | Central Domains & Base Domain
    |--------------------------------------------------------------------------
    |
    | Dominios centrales de la app (sin contexto tenant) y dominio base para
    | construir subdominios de centros.
    |
    */
    'central_domains' => array_values(array_filter(array_map(
        static fn (string $domain): string => strtolower(trim($domain)),
        explode(',', env('TENANCY_CENTRAL_DOMAINS', 'sanaresys.com,localhost,127.0.0.1'))
    ))),
    'base_domain' => env('TENANCY_BASE_DOMAIN', 'sanaresys.com'),
    'tenant_scheme' => env('TENANCY_TENANT_SCHEME', 'https'),

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
    'bootstrappers' => array_values(array_filter([
        Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
        env('TENANCY_CACHE_BOOTSTRAPPER', false)
            ? Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class
            : null,
        env('TENANCY_FILESYSTEM_BOOTSTRAPPER', false)
            ? Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class
            : null,
        Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
    ])),

    /*
    |--------------------------------------------------------------------------
    | Optional Features
    |--------------------------------------------------------------------------
    */
    'features' => [
        Stancl\Tenancy\Features\UserImpersonation::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    */
    'database' => [
        'central_connection' => env('DB_CONNECTION', 'mysql'),
        'template_tenant_connection' => null,
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
