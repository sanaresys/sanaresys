<?php

namespace Tests\Feature;

use App\Mail\ClinicRegistrationVerificationMail;
use App\Models\ClinicRegistrationRequest;
use App\Models\Centros_Medico;
use App\Models\Tenant;
use App\Services\ProvisionResult;
use App\Services\TenantProvisioningService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class ClinicRegistrationEmailVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'mysql');
        config()->set('database.connections.mysql', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('tenancy.central_domains', ['localhost', '127.0.0.1', 'sanaresys.localhost']);
        config()->set('tenancy.base_domain', 'sanaresys.localhost');
        config()->set('tenancy.tenant_scheme', 'https');

        DB::purge('mysql');
        DB::setDefaultConnection('mysql');
        DB::reconnect('mysql');

        $this->createMinimalSchema();
    }

    public function test_store_creates_pending_request_and_sends_verification_email(): void
    {
        Mail::fake();

        $provisioning = Mockery::mock(TenantProvisioningService::class);
        $provisioning->shouldReceive('emailExistsInAnyTenant')
            ->once()
            ->with('owner@example.com')
            ->andReturn(false);
        $this->app->instance(TenantProvisioningService::class, $provisioning);

        $response = $this->post(route('clinica.registro.store'), [
            'nombre_centro' => 'Clinica Salud Total',
            'direccion' => 'Colonia Palmira',
            'telefono' => '9999-9999',
            'rtn' => '08011999123456',
            'owner_name' => 'Owner Name',
            'owner_email' => 'owner@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $request = ClinicRegistrationRequest::query()->first();

        $response->assertRedirect(route('clinica.registro.waiting', ['publicId' => $request->public_id]));
        $this->assertDatabaseHas('clinic_registration_requests', [
            'id' => $request->id,
            'status' => ClinicRegistrationRequest::STATUS_PENDING_VERIFICATION,
            'owner_email' => 'owner@example.com',
            'slug' => 'clinica-salud-total',
        ], 'mysql');
        $this->assertDatabaseCount('centros_medicos', 0, 'mysql');
        $this->assertDatabaseCount('tenants', 0, 'mysql');
        Mail::assertSent(ClinicRegistrationVerificationMail::class, 1);
    }

    public function test_waiting_screen_loads_request_data(): void
    {
        $registration = $this->createPendingRegistration();

        $response = $this->get(route('clinica.registro.waiting', ['publicId' => $registration->public_id]));

        $response->assertOk();
        $response->assertSee($registration->owner_email);
        $response->assertSee($registration->nombre_centro);
    }

    public function test_resend_updates_expiry_and_sends_email(): void
    {
        Mail::fake();

        $registration = $this->createPendingRegistration([
            'resend_count' => 0,
            'verification_expires_at' => now()->subHour(),
            'status' => ClinicRegistrationRequest::STATUS_EXPIRED,
        ]);

        $response = $this->post(route('clinica.registro.resend', ['publicId' => $registration->public_id]));

        $response->assertRedirect(route('clinica.registro.waiting', ['publicId' => $registration->public_id]));

        $registration->refresh();
        $this->assertSame(1, $registration->resend_count);
        $this->assertSame(ClinicRegistrationRequest::STATUS_PENDING_VERIFICATION, $registration->status);
        $this->assertTrue($registration->verification_expires_at->isFuture());
        Mail::assertSent(ClinicRegistrationVerificationMail::class, 1);
    }

    public function test_verify_with_valid_link_provisions_and_redirects_to_onboarding(): void
    {
        $registration = $this->createPendingRegistration();

        $provisioning = Mockery::mock(TenantProvisioningService::class);
        $provisioning->shouldReceive('emailExistsInAnyTenant')
            ->once()
            ->with($registration->owner_email)
            ->andReturn(false);
        $provisioning->shouldReceive('provisionNewCenter')
            ->once()
            ->andReturnUsing(function (Centros_Medico $centro) {
                $tenant = Tenant::withoutEvents(function () use ($centro) {
                    return Tenant::query()->create([
                        'id' => 'centro_' . $centro->id,
                        'centro_id' => $centro->id,
                        'tenancy_db_name' => $centro->slug,
                        'tenancy_primary_domain' => "{$centro->slug}.sanaresys.localhost",
                        'tenancy_mode' => 'domain',
                    ]);
                });

                $tenant->domains()->create([
                    'domain' => "{$centro->slug}.sanaresys.localhost",
                ]);

                return new ProvisionResult(
                    tenant: $tenant,
                    primaryDomain: "{$centro->slug}.sanaresys.localhost",
                    databaseName: $centro->slug,
                    adminUserId: 10,
                );
            });
        $this->app->instance(TenantProvisioningService::class, $provisioning);

        $url = URL::temporarySignedRoute(
            'clinica.registro.verify',
            now()->addMinutes(10),
            ['publicId' => $registration->public_id]
        );

        $response = $this->get($url);

        $registration->refresh();
        $centro = Centros_Medico::query()->findOrFail($registration->centro_id);

        $this->assertSame(ClinicRegistrationRequest::STATUS_PROVISIONED, $registration->status);
        $this->assertNotNull($registration->onboarding_redirect_url);
        $this->assertStringStartsWith(
            "https://{$centro->slug}.sanaresys.localhost/tenant/impersonate/",
            $registration->onboarding_redirect_url
        );
        $response->assertRedirect($registration->onboarding_redirect_url);
        $this->assertDatabaseCount('centros_medicos', 1, 'mysql');
    }

    public function test_verify_expired_request_marks_expired_without_provisioning(): void
    {
        $registration = $this->createPendingRegistration([
            'verification_expires_at' => now()->subMinute(),
        ]);

        $provisioning = Mockery::mock(TenantProvisioningService::class);
        $provisioning->shouldReceive('emailExistsInAnyTenant')->never();
        $provisioning->shouldReceive('provisionNewCenter')->never();
        $this->app->instance(TenantProvisioningService::class, $provisioning);

        $url = URL::temporarySignedRoute(
            'clinica.registro.verify',
            now()->addMinutes(10),
            ['publicId' => $registration->public_id]
        );

        $response = $this->get($url);

        $registration->refresh();

        $response->assertRedirect(route('clinica.registro.waiting', ['publicId' => $registration->public_id]));
        $this->assertSame(ClinicRegistrationRequest::STATUS_EXPIRED, $registration->status);
        $this->assertDatabaseCount('centros_medicos', 0, 'mysql');
    }

    public function test_verify_conflict_marks_request_failed_and_returns_to_form(): void
    {
        Centros_Medico::query()->create([
            'nombre_centro' => 'Clinica Existente',
            'direccion' => 'Direccion',
            'telefono' => '1111',
            'rtn' => '08011999123456',
            'slug' => 'clinica-existente',
            'tenancy_mode' => 'domain',
            'onboarding_current_step' => 0,
            'onboarding_skipped_cai' => false,
            'onboarding_completed_at' => null,
        ]);

        $registration = $this->createPendingRegistration([
            'rtn' => '08011999123456',
        ]);

        $provisioning = Mockery::mock(TenantProvisioningService::class);
        $provisioning->shouldIgnoreMissing();
        $this->app->instance(TenantProvisioningService::class, $provisioning);

        $url = URL::temporarySignedRoute(
            'clinica.registro.verify',
            now()->addMinutes(10),
            ['publicId' => $registration->public_id]
        );

        $response = $this->get($url);

        $registration->refresh();

        $response->assertRedirect(route('clinica.registro'));
        $response->assertSessionHasErrors(['rtn']);
        $this->assertSame(ClinicRegistrationRequest::STATUS_FAILED, $registration->status);
        $this->assertSame('validation_conflict', $registration->failure_code);
    }

    public function test_verify_is_idempotent_on_second_click(): void
    {
        $registration = $this->createPendingRegistration();

        $provisioning = Mockery::mock(TenantProvisioningService::class);
        $provisioning->shouldReceive('emailExistsInAnyTenant')
            ->once()
            ->andReturn(false);
        $provisioning->shouldReceive('provisionNewCenter')
            ->once()
            ->andReturnUsing(function (Centros_Medico $centro) {
                $tenant = Tenant::withoutEvents(function () use ($centro) {
                    return Tenant::query()->create([
                        'id' => 'centro_' . $centro->id,
                        'centro_id' => $centro->id,
                        'tenancy_db_name' => $centro->slug,
                        'tenancy_primary_domain' => "{$centro->slug}.sanaresys.localhost",
                        'tenancy_mode' => 'domain',
                    ]);
                });

                $tenant->domains()->create([
                    'domain' => "{$centro->slug}.sanaresys.localhost",
                ]);

                return new ProvisionResult(
                    tenant: $tenant,
                    primaryDomain: "{$centro->slug}.sanaresys.localhost",
                    databaseName: $centro->slug,
                    adminUserId: 10,
                );
            });
        $this->app->instance(TenantProvisioningService::class, $provisioning);

        $url = URL::temporarySignedRoute(
            'clinica.registro.verify',
            now()->addMinutes(10),
            ['publicId' => $registration->public_id]
        );

        $first = $this->get($url);
        $registration->refresh();
        $firstRedirect = $registration->onboarding_redirect_url;

        $second = $this->get($url);
        $registration->refresh();

        $first->assertRedirect($firstRedirect);
        $second->assertRedirect($firstRedirect);
        $this->assertSame(1, Centros_Medico::query()->count());
    }

    protected function createPendingRegistration(array $overrides = []): ClinicRegistrationRequest
    {
        return ClinicRegistrationRequest::query()->create(array_merge([
            'public_id' => (string) str()->uuid(),
            'status' => ClinicRegistrationRequest::STATUS_PENDING_VERIFICATION,
            'nombre_centro' => 'Clinica Salud Total',
            'slug' => 'clinica-salud-total',
            'direccion' => 'Colonia Palmira',
            'telefono' => '9999-9999',
            'rtn' => '08011999123456',
            'owner_name' => 'Owner Name',
            'owner_email' => 'owner@example.com',
            'password_encrypted' => Crypt::encryptString('Password123!'),
            'verification_sent_at' => now(),
            'verification_expires_at' => now()->addDay(),
            'resend_count' => 0,
        ], $overrides));
    }

    protected function createMinimalSchema(): void
    {
        $schema = Schema::connection('mysql');

        $schema->create('centros_medicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_centro');
            $table->string('slug')->nullable()->unique();
            $table->string('tenancy_mode')->default('legacy');
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->integer('onboarding_current_step')->default(0);
            $table->boolean('onboarding_skipped_cai')->default(false);
            $table->string('direccion');
            $table->string('telefono');
            $table->string('email')->nullable();
            $table->string('rtn')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        $schema->create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('centro_id')->unique()->constrained('centros_medicos')->onDelete('cascade');
            $table->json('data')->nullable();
            $table->string('tenancy_db_name')->nullable();
            $table->string('tenancy_primary_domain')->nullable();
            $table->string('tenancy_mode')->nullable();
            $table->timestamps();
        });

        $schema->create('domains', function (Blueprint $table) {
            $table->increments('id');
            $table->string('domain')->unique();
            $table->string('tenant_id');
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        $schema->create('tenant_user_impersonation_tokens', function (Blueprint $table) {
            $table->string('token', 128)->primary();
            $table->string('tenant_id');
            $table->string('user_id');
            $table->string('auth_guard')->nullable();
            $table->string('redirect_url');
            $table->timestamp('created_at');

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        $schema->create('clinic_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('status', 40)->index();
            $table->string('nombre_centro');
            $table->string('slug', 63);
            $table->string('direccion');
            $table->string('telefono', 50);
            $table->string('rtn', 100)->index();
            $table->string('owner_name');
            $table->string('owner_email')->index();
            $table->text('password_encrypted')->nullable();
            $table->timestamp('verification_sent_at')->nullable();
            $table->timestamp('verification_expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->unsignedInteger('resend_count')->default(0);
            $table->foreignId('centro_id')->nullable()->constrained('centros_medicos')->nullOnDelete();
            $table->string('tenant_id')->nullable()->index();
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->nullOnDelete();
            $table->string('primary_domain')->nullable();
            $table->text('onboarding_redirect_url')->nullable();
            $table->string('failure_code', 100)->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamps();
        });
    }
}

