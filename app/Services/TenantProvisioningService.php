<?php

namespace App\Services;

use App\Models\Centros_Medico;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\EnfermedadeSeeder;
use Database\Seeders\EspecialidadSeeder;
use Database\Seeders\NacionalidadSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class TenantProvisioningService
{
    public function __construct(
        protected TenantIdentityService $identityService,
    ) {
    }

    /**
     * @param array{name:string,email:string,password:string} $creatorPayload
     */
    public function provisionNewCenter(Centros_Medico $centro, array $creatorPayload): ProvisionResult
    {
        if (($centro->tenancy_mode ?? 'legacy') !== 'domain') {
            throw ValidationException::withMessages([
                'tenancy_mode' => 'El centro debe estar en modo domain para este flujo.',
            ]);
        }

        if (! $centro->slug) {
            $centro->slug = $this->identityService->generateSlug($centro->nombre_centro);
            $centro->save();
        }

        $this->identityService->validateSlugAvailable($centro->slug, $centro->id);

        $primaryDomain = $this->identityService->buildPrimaryDomain($centro->slug);
        $databaseName = $centro->slug;

        $tenant = Tenant::where('centro_id', $centro->id)->first();

        if (! $tenant) {
            $tenant = new Tenant();
            $tenant->forceFill([
                'id' => 'centro_' . $centro->id,
                'centro_id' => $centro->id,
                'tenancy_db_name' => $databaseName,
                'tenancy_primary_domain' => $primaryDomain,
                'tenancy_mode' => 'domain',
            ]);
            $tenant->save();
        } else {
            $tenant->forceFill([
                'tenancy_db_name' => $databaseName,
                'tenancy_primary_domain' => $primaryDomain,
                'tenancy_mode' => 'domain',
            ]);
            $tenant->save();
        }

        if (! $tenant->domains()->where('domain', $primaryDomain)->exists()) {
            $tenant->createDomain($primaryDomain);
        }

        $adminUserId = $this->createTenantAdminUser($tenant, $creatorPayload);

        // ⚠️ NO marcar onboarding como completado aquí
        // El onboarding_completed_at debe permanecer NULL hasta que el usuario complete el wizard
        // $centro->forceFill([
        //     'onboarding_completed_at' => now(),
        // ]);
        // $centro->save();

        Log::info('Tenant provisionado para centro en modo domain.', [
            'centro_id' => $centro->id,
            'tenant_id' => $tenant->id,
            'database' => $databaseName,
            'domain' => $primaryDomain,
            'admin_user_id' => $adminUserId,
        ]);

        return new ProvisionResult($tenant, $primaryDomain, $databaseName, $adminUserId);
    }

    /**
     * @param array{name:string,email:string,password:string} $creatorPayload
     */
    protected function createTenantAdminUser(Tenant $tenant, array $creatorPayload): int
    {
        try {
            tenancy()->initialize($tenant);
            $this->ensureTenantAclBaseline();
            $this->ensureTenantCatalogBaseline();

            /** @var User $user */
            $user = User::query()->where('email', $creatorPayload['email'])->first();

            if (! $user) {
                $user = User::query()->create([
                    'name' => $creatorPayload['name'],
                    'email' => $creatorPayload['email'],
                    // User model uses hashed cast for password.
                    'password' => $creatorPayload['password'],
                ]);
            } else {
                $user->name = $creatorPayload['name'];
                // User model uses hashed cast for password.
                $user->password = $creatorPayload['password'];
                $user->save();
            }

            $role = Role::query()->firstOrCreate([
                'name' => 'administrador',
                'guard_name' => 'web',
            ]);

            if (! $user->hasRole('administrador')) {
                $user->assignRole($role);
            }

            return $user->id;
        } finally {
            tenancy()->end();
        }
    }

    protected function ensureTenantAclBaseline(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        app(RolesAndPermissionsSeeder::class)->run();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function ensureTenantCatalogBaseline(): void
    {
        if (DB::table('nacionalidades')->count() === 0) {
            app(NacionalidadSeeder::class)->run();
        }

        if (DB::table('especialidads')->count() === 0) {
            app(EspecialidadSeeder::class)->run();
        }

        if (DB::table('enfermedades')->count() === 0) {
            app(EnfermedadeSeeder::class)->run();
        }
    }

    public function emailExistsInAnyTenant(string $email): bool
    {
        if (User::on('mysql')->where('email', $email)->exists()) {
            return true;
        }

        $tenantIds = Tenant::query()->pluck('id');

        foreach ($tenantIds as $tenantId) {
            $tenant = Tenant::find($tenantId);
            if (! $tenant) {
                continue;
            }

            try {
                tenancy()->initialize($tenant);

                if (User::query()->where('email', $email)->exists()) {
                    return true;
                }
            } catch (\Throwable $e) {
                DB::purge('tenant');
                Log::warning('No se pudo verificar email en un tenant durante onboarding.', [
                    'tenant_id' => $tenantId,
                    'error' => $e->getMessage(),
                ]);
            } finally {
                tenancy()->end();
            }
        }

        return false;
    }
}
