<?php

namespace Tests\Feature;

use App\Mail\ClinicRegistrationVerificationMail;
use App\Models\BillingInvoice;
use App\Models\Centros_Medico;
use App\Models\ClinicRegistrationRequest;
use App\Services\Billing\BillingInvoiceService;
use App\Services\Billing\RegistrationProvisioningService;
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
        config()->set('billing.currency', 'USD');
        config()->set('billing.engine.consent_text_version', 'test-v1');
        config()->set('billing.plans', [
            'monthly' => [
                'code' => 'monthly',
                'name' => 'Plan mensual',
                'price' => 89.99,
                'interval' => 'monthly',
            ],
        ]);

        DB::purge('mysql');
        DB::setDefaultConnection('mysql');
        DB::reconnect('mysql');

        $this->createMinimalSchema();
    }

    public function test_store_creates_pending_request_with_plan_and_sends_verification_email(): void
    {
        Mail::fake();

        $provisioning = Mockery::mock(TenantProvisioningService::class);
        $provisioning->shouldReceive('emailExistsInAnyTenant')
            ->once()
            ->with('owner@example.com')
            ->andReturn(false);
        $this->app->instance(TenantProvisioningService::class, $provisioning);

        $response = $this->post(route('clinica.registro.store'), [
            'plan_code' => 'monthly',
            'nombre_centro' => 'Clinica Salud Total',
            'direccion' => 'Colonia Palmira',
            'telefono' => '9999-9999',
            'rtn' => '08011999123456',
            'owner_name' => 'Owner Name',
            'owner_email' => 'owner@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $registration = ClinicRegistrationRequest::query()->firstOrFail();

        $response->assertRedirect(route('clinica.registro.waiting', ['publicId' => $registration->public_id]));
        $this->assertSame('monthly', $registration->plan_code);
        $this->assertSame('pending_verification', $registration->status);
        $this->assertSame('pending', $registration->payment_status);
        Mail::assertSent(ClinicRegistrationVerificationMail::class, 1);
    }

    public function test_store_allows_empty_rtn_and_saves_null_value(): void
    {
        Mail::fake();

        $provisioning = Mockery::mock(TenantProvisioningService::class);
        $provisioning->shouldReceive('emailExistsInAnyTenant')
            ->once()
            ->with('owner.no.rtn@example.com')
            ->andReturn(false);
        $this->app->instance(TenantProvisioningService::class, $provisioning);

        $response = $this->post(route('clinica.registro.store'), [
            'plan_code' => 'monthly',
            'nombre_centro' => 'Clinica Sin Rtn',
            'direccion' => 'Colonia Palmira',
            'telefono' => '9999-9999',
            'rtn' => '',
            'owner_name' => 'Owner Name',
            'owner_email' => 'owner.no.rtn@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $registration = ClinicRegistrationRequest::query()->firstOrFail();

        $response->assertRedirect(route('clinica.registro.waiting', ['publicId' => $registration->public_id]));
        $this->assertNull($registration->rtn);
        $this->assertSame('pending_verification', $registration->status);
        Mail::assertSent(ClinicRegistrationVerificationMail::class, 1);
    }

    public function test_verify_activates_trial_and_redirects_to_tenant_access(): void
    {
        $registration = $this->createPendingRegistration();

        $billing = Mockery::mock(BillingInvoiceService::class);
        $billing->shouldReceive('activateOnboardingTrial')
            ->once()
            ->andReturnUsing(function () use ($registration): void {
                $registration->forceFill([
                    'status' => ClinicRegistrationRequest::STATUS_PROVISIONED,
                    'payment_status' => 'paid',
                ])->save();
            });
        $this->app->instance(BillingInvoiceService::class, $billing);

        $provisioning = Mockery::mock(RegistrationProvisioningService::class);
        $provisioning->shouldReceive('issueTenantAccessUrl')
            ->once()
            ->andReturn('https://tenant.sanaresys.localhost/tenant/impersonate/trial-token');
        $this->app->instance(RegistrationProvisioningService::class, $provisioning);

        $url = URL::temporarySignedRoute(
            'clinica.registro.verify',
            now()->addMinutes(10),
            ['publicId' => $registration->public_id]
        );

        $response = $this->get($url);
        $registration->refresh();

        $response->assertRedirect('https://tenant.sanaresys.localhost/tenant/impersonate/trial-token');
        $this->assertSame(ClinicRegistrationRequest::STATUS_PROVISIONED, $registration->status);
        $this->assertSame('paid', $registration->payment_status);
    }

    public function test_create_billing_order_for_provisioned_registration_keeps_provisioned_status(): void
    {
        $centro = Centros_Medico::query()->create([
            'nombre_centro' => 'Clinica Salud Total',
            'slug' => 'clinica-salud-total',
            'tenancy_mode' => 'domain',
            'billing_status' => 'past_due',
            'direccion' => 'Colonia Palmira',
            'telefono' => '9999-9999',
            'rtn' => '08011999123456',
        ]);

        $registration = $this->createPendingRegistration([
            'status' => ClinicRegistrationRequest::STATUS_PROVISIONED,
            'payment_status' => 'paid',
            'centro_id' => $centro->id,
        ]);

        $invoice = BillingInvoice::query()->create([
            'public_id' => (string) str()->uuid(),
            'centro_id' => $centro->id,
            'kind' => 'renewal',
            'status' => 'open',
            'currency' => 'USD',
            'subtotal' => 89.99,
            'total' => 89.99,
            'due_at' => now(),
            'billing_starts_at' => now()->subMonth(),
            'billing_ends_at' => now(),
            'billing_renews_at' => now(),
        ]);

        $billing = Mockery::mock(BillingInvoiceService::class);
        $billing->shouldReceive('openBasePlanInvoiceForCentro')
            ->once()
            ->andReturn($invoice);
        $billing->shouldReceive('createOrReuseAttempt')
            ->once()
            ->andReturn((object) ['paypal_order_id' => 'ORDER-RENEW-1']);
        $this->app->instance(BillingInvoiceService::class, $billing);

        $response = $this->postJson(route('clinica.registro.billing.order', [
            'publicId' => $registration->public_id,
        ]), [
            'consent' => true,
        ]);

        $response->assertOk();
        $response->assertJson([
            'orderId' => 'ORDER-RENEW-1',
        ]);

        $registration->refresh();
        $this->assertSame(ClinicRegistrationRequest::STATUS_PROVISIONED, $registration->status);
        $this->assertSame('paid', $registration->payment_status);
    }

    public function test_capture_billing_order_returns_provisioning_redirect_after_paid_invoice(): void
    {
        $registration = $this->createPendingRegistration([
            'status' => ClinicRegistrationRequest::STATUS_PENDING_PAYMENT,
            'payment_status' => 'pending',
        ]);

        $invoice = BillingInvoice::query()->create([
            'public_id' => (string) str()->uuid(),
            'clinic_registration_request_id' => $registration->id,
            'kind' => 'onboarding',
            'status' => 'open',
            'currency' => 'USD',
            'subtotal' => 89.99,
            'total' => 89.99,
            'due_at' => now(),
            'billing_starts_at' => now(),
            'billing_ends_at' => now()->addMonth(),
            'billing_renews_at' => now()->addMonth(),
        ]);

        $registration->forceFill([
            'billing_invoice_id' => $invoice->id,
        ])->save();

        $billing = Mockery::mock(BillingInvoiceService::class);
        $billing->shouldReceive('captureAttemptFromReturn')
            ->once()
            ->andReturnUsing(function () use ($registration): void {
                $registration->forceFill([
                    'status' => ClinicRegistrationRequest::STATUS_PROVISIONED,
                    'payment_status' => 'paid',
                ])->save();
            });
        $this->app->instance(BillingInvoiceService::class, $billing);

        $provisioning = Mockery::mock(RegistrationProvisioningService::class);
        $provisioning->shouldReceive('issueTenantAccessUrl')
            ->once()
            ->andReturn('https://tenant.sanaresys.localhost/tenant/impersonate/token456');
        $this->app->instance(RegistrationProvisioningService::class, $provisioning);

        $response = $this->postJson(route('clinica.registro.billing.capture', [
            'publicId' => $registration->public_id,
        ]), [
            'order_id' => 'ORDER-123',
        ]);

        $response->assertOk();
        $response->assertJson([
            'redirect_url' => 'https://tenant.sanaresys.localhost/tenant/impersonate/token456',
        ]);
    }

    public function test_enter_tenant_generates_fresh_redirect_for_provisioned_registration(): void
    {
        $registration = $this->createPendingRegistration([
            'status' => ClinicRegistrationRequest::STATUS_PROVISIONED,
            'payment_status' => 'paid',
            'tenant_id' => 'centro_99',
            'primary_domain' => 'tenant.sanaresys.localhost',
        ]);

        $provisioning = Mockery::mock(RegistrationProvisioningService::class);
        $provisioning->shouldReceive('issueTenantAccessUrl')
            ->once()
            ->andReturn('https://tenant.sanaresys.localhost/tenant/impersonate/fresh-token');
        $this->app->instance(RegistrationProvisioningService::class, $provisioning);

        $response = $this->get(route('clinica.registro.tenant.enter', [
            'publicId' => $registration->public_id,
        ]));

        $response->assertRedirect('https://tenant.sanaresys.localhost/tenant/impersonate/fresh-token');
    }

    protected function createPendingRegistration(array $overrides = []): ClinicRegistrationRequest
    {
        return ClinicRegistrationRequest::query()->create(array_merge([
            'public_id' => (string) str()->uuid(),
            'status' => ClinicRegistrationRequest::STATUS_PENDING_VERIFICATION,
            'payment_status' => 'pending',
            'plan_code' => 'monthly',
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
            $table->string('billing_status')->default('inactive');
            $table->string('billing_plan_code')->nullable();
            $table->timestamp('billing_renews_at')->nullable();
            $table->timestamp('billing_last_sync_at')->nullable();
            $table->string('billing_override')->nullable();
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->integer('onboarding_current_step')->default(0);
            $table->boolean('onboarding_skipped_cai')->default(false);
            $table->string('direccion');
            $table->string('telefono');
            $table->string('email')->nullable();
            $table->string('rtn')->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        $schema->create('clinic_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('status', 40)->index();
            $table->string('payment_status', 32)->default('pending');
            $table->string('nombre_centro');
            $table->string('slug', 63);
            $table->string('plan_code', 32)->nullable();
            $table->string('direccion');
            $table->string('telefono', 50);
            $table->string('rtn', 100)->nullable()->index();
            $table->string('owner_name');
            $table->string('owner_email')->index();
            $table->text('password_encrypted')->nullable();
            $table->timestamp('verification_sent_at')->nullable();
            $table->timestamp('verification_expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('payment_approved_at')->nullable();
            $table->timestamp('consent_at')->nullable();
            $table->string('consent_text_version')->nullable();
            $table->string('consent_ip')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->unsignedInteger('resend_count')->default(0);
            $table->unsignedBigInteger('centro_id')->nullable();
            $table->string('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('billing_invoice_id')->nullable();
            $table->string('primary_domain')->nullable();
            $table->text('onboarding_redirect_url')->nullable();
            $table->string('failure_code', 100)->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamps();
        });

        $schema->create('billing_invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('centro_id')->nullable();
            $table->unsignedBigInteger('clinic_registration_request_id')->nullable();
            $table->string('kind', 32);
            $table->string('status', 32);
            $table->string('currency', 3);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamp('due_at')->nullable();
            $table->timestamp('grace_until')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('billing_starts_at')->nullable();
            $table->timestamp('billing_ends_at')->nullable();
            $table->timestamp('billing_renews_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }
}
