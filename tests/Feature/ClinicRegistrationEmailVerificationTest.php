<?php

namespace Tests\Feature;

use App\Mail\ClinicRegistrationVerificationMail;
use App\Models\ClinicRegistrationRequest;
use App\Services\Billing\BillingSubscriptionService;
use App\Services\Billing\PayPalService;
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
        config()->set('billing.plans', [
            'monthly' => [
                'code' => 'monthly',
                'name' => 'Plan mensual',
                'price' => 89.99,
                'paypal_plan_id' => 'P-MONTHLY',
            ],
            'annual' => [
                'code' => 'annual',
                'name' => 'Plan anual',
                'price' => 599.00,
                'paypal_plan_id' => 'P-ANNUAL',
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
        $this->assertSame('P-MONTHLY', $registration->paypal_plan_id);
        Mail::assertSent(ClinicRegistrationVerificationMail::class, 1);
    }

    public function test_verify_transitions_to_pending_payment_and_redirects_to_paypal(): void
    {
        $registration = $this->createPendingRegistration();

        $payPal = Mockery::mock(PayPalService::class);
        $payPal->shouldReceive('createSubscription')
            ->once()
            ->andReturn([
                'id' => 'I-SUBSCRIPTION-123',
                'status' => 'APPROVAL_PENDING',
                'approve_url' => 'https://www.sandbox.paypal.com/checkoutnow?token=I-SUBSCRIPTION-123',
                'raw' => [
                    'id' => 'I-SUBSCRIPTION-123',
                    'status' => 'APPROVAL_PENDING',
                    'plan_id' => 'P-MONTHLY',
                    'links' => [
                        ['rel' => 'approve', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=I-SUBSCRIPTION-123'],
                    ],
                ],
            ]);
        $this->app->instance(PayPalService::class, $payPal);

        $billingSync = Mockery::mock(BillingSubscriptionService::class);
        $billingSync->shouldReceive('syncFromPayPalSubscription')->once();
        $this->app->instance(BillingSubscriptionService::class, $billingSync);

        $provisioning = Mockery::mock(RegistrationProvisioningService::class);
        $provisioning->shouldNotReceive('provisionFromPaidRegistration');
        $this->app->instance(RegistrationProvisioningService::class, $provisioning);

        $url = URL::temporarySignedRoute(
            'clinica.registro.verify',
            now()->addMinutes(10),
            ['publicId' => $registration->public_id]
        );

        $response = $this->get($url);
        $registration->refresh();

        $response->assertRedirect('https://www.sandbox.paypal.com/checkoutnow?token=I-SUBSCRIPTION-123');
        $this->assertSame('pending_payment', $registration->status);
        $this->assertSame('pending', $registration->payment_status);
        $this->assertSame('I-SUBSCRIPTION-123', $registration->paypal_subscription_id);
    }

    public function test_payment_return_active_calls_provisioning_and_redirects_to_tenant(): void
    {
        $registration = $this->createPendingRegistration([
            'status' => ClinicRegistrationRequest::STATUS_PENDING_PAYMENT,
            'paypal_subscription_id' => 'I-SUBSCRIPTION-123',
            'payment_status' => 'pending',
        ]);

        $payPal = Mockery::mock(PayPalService::class);
        $payPal->shouldReceive('getSubscription')
            ->once()
            ->with('I-SUBSCRIPTION-123')
            ->andReturn([
                'id' => 'I-SUBSCRIPTION-123',
                'status' => 'ACTIVE',
                'plan_id' => 'P-MONTHLY',
                'billing_info' => [
                    'next_billing_time' => now()->addMonth()->toIso8601String(),
                ],
            ]);
        $payPal->shouldReceive('normalizeStatus')->andReturn('active');
        $this->app->instance(PayPalService::class, $payPal);

        $billingSync = Mockery::mock(BillingSubscriptionService::class);
        $billingSync->shouldReceive('syncFromPayPalSubscription')->once();
        $this->app->instance(BillingSubscriptionService::class, $billingSync);

        $provisioning = Mockery::mock(RegistrationProvisioningService::class);
        $provisioning->shouldReceive('provisionFromPaidRegistration')
            ->once()
            ->andReturn('https://tenant.sanaresys.localhost/tenant/impersonate/token123');
        $this->app->instance(RegistrationProvisioningService::class, $provisioning);

        $response = $this->get(route('clinica.registro.payment.return', [
            'publicId' => $registration->public_id,
            'subscription_id' => 'I-SUBSCRIPTION-123',
        ]));

        $registration->refresh();

        $response->assertRedirect('https://tenant.sanaresys.localhost/tenant/impersonate/token123');
        $this->assertSame('active', $registration->payment_status);
        $this->assertSame('pending_payment', $registration->status);
    }

    protected function createPendingRegistration(array $overrides = []): ClinicRegistrationRequest
    {
        return ClinicRegistrationRequest::query()->create(array_merge([
            'public_id' => (string) str()->uuid(),
            'status' => ClinicRegistrationRequest::STATUS_PENDING_VERIFICATION,
            'payment_status' => 'pending',
            'plan_code' => 'monthly',
            'paypal_plan_id' => 'P-MONTHLY',
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
            $table->string('rtn', 100)->index();
            $table->string('owner_name');
            $table->string('owner_email')->index();
            $table->text('password_encrypted')->nullable();
            $table->timestamp('verification_sent_at')->nullable();
            $table->timestamp('verification_expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('payment_approved_at')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->unsignedInteger('resend_count')->default(0);
            $table->foreignId('centro_id')->nullable()->constrained('centros_medicos')->nullOnDelete();
            $table->string('tenant_id')->nullable()->index();
            $table->string('paypal_subscription_id')->nullable()->index();
            $table->string('paypal_plan_id')->nullable();
            $table->string('primary_domain')->nullable();
            $table->text('onboarding_redirect_url')->nullable();
            $table->string('failure_code', 100)->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamps();
        });
    }
}
