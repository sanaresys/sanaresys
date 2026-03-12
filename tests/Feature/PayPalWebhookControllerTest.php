<?php

namespace Tests\Feature;

use App\Models\BillingWebhookEvent;
use App\Models\ClinicRegistrationRequest;
use App\Services\Billing\BillingSubscriptionService;
use App\Services\Billing\PayPalService;
use App\Services\Billing\RegistrationProvisioningService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class PayPalWebhookControllerTest extends TestCase
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

        DB::purge('mysql');
        DB::setDefaultConnection('mysql');
        DB::reconnect('mysql');

        $this->createMinimalSchema();
    }

    public function test_webhook_is_idempotent_for_processed_event(): void
    {
        BillingWebhookEvent::query()->create([
            'provider' => 'paypal',
            'event_id' => 'WH-123',
            'status' => 'processed',
            'payload' => ['id' => 'WH-123'],
        ]);

        $response = $this->postJson(route('webhooks.paypal'), [
            'id' => 'WH-123',
            'event_type' => 'BILLING.SUBSCRIPTION.ACTIVATED',
        ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Duplicate event ignored.']);
        $this->assertSame(1, BillingWebhookEvent::query()->count());
    }

    public function test_webhook_updates_registration_to_active_when_subscription_active(): void
    {
        $registration = ClinicRegistrationRequest::query()->create([
            'public_id' => (string) str()->uuid(),
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'nombre_centro' => 'Clinica Test',
            'slug' => 'clinica-test',
            'direccion' => 'Dir',
            'telefono' => '9999',
            'rtn' => '08011999123456',
            'owner_name' => 'Owner',
            'owner_email' => 'owner@example.com',
            'paypal_subscription_id' => 'I-ABC',
        ]);

        $payPal = Mockery::mock(PayPalService::class);
        $payPal->shouldReceive('verifyWebhookSignature')->once()->andReturn(true);
        $payPal->shouldReceive('getSubscription')->once()->with('I-ABC')->andReturn([
            'id' => 'I-ABC',
            'status' => 'ACTIVE',
            'plan_id' => 'P-MONTHLY',
        ]);
        $payPal->shouldReceive('normalizeStatus')->once()->andReturn('active');
        $this->app->instance(PayPalService::class, $payPal);

        $billingSync = Mockery::mock(BillingSubscriptionService::class);
        $billingSync->shouldReceive('syncFromPayPalSubscription')->once()->andReturn((object) ['status' => 'active']);
        $this->app->instance(BillingSubscriptionService::class, $billingSync);

        $provisioning = Mockery::mock(RegistrationProvisioningService::class);
        $provisioning->shouldReceive('provisionFromPaidRegistration')->once();
        $this->app->instance(RegistrationProvisioningService::class, $provisioning);

        $response = $this->postJson(route('webhooks.paypal'), [
            'id' => 'WH-NEW-1',
            'event_type' => 'BILLING.SUBSCRIPTION.ACTIVATED',
            'resource' => [
                'id' => 'I-ABC',
            ],
        ], [
            'PAYPAL-TRANSMISSION-ID' => 'test-transmission',
            'PAYPAL-TRANSMISSION-TIME' => now()->toIso8601String(),
            'PAYPAL-CERT-URL' => 'https://api-m.sandbox.paypal.com/certs/cert.pem',
            'PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
            'PAYPAL-TRANSMISSION-SIG' => 'test-signature',
        ]);

        $response->assertOk();
        $registration->refresh();
        $this->assertSame('active', $registration->payment_status);
        $this->assertSame('pending_payment', $registration->status);
        $this->assertDatabaseHas('billing_webhook_events', [
            'event_id' => 'WH-NEW-1',
            'status' => 'processed',
        ], 'mysql');
    }

    protected function createMinimalSchema(): void
    {
        $schema = Schema::connection('mysql');

        $schema->create('centros_medicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_centro');
            $table->string('slug')->nullable();
            $table->string('tenancy_mode')->default('domain');
            $table->string('billing_status')->default('inactive');
            $table->string('billing_plan_code')->nullable();
            $table->timestamp('billing_renews_at')->nullable();
            $table->timestamp('billing_last_sync_at')->nullable();
            $table->string('billing_override')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('rtn')->nullable();
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
            $table->unsignedBigInteger('centro_id')->nullable();
            $table->string('tenant_id')->nullable()->index();
            $table->string('paypal_subscription_id')->nullable()->index();
            $table->string('paypal_plan_id')->nullable();
            $table->string('primary_domain')->nullable();
            $table->text('onboarding_redirect_url')->nullable();
            $table->string('failure_code', 100)->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamps();
        });

        $schema->create('billing_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32)->default('paypal');
            $table->string('event_id')->index();
            $table->string('event_type', 120)->nullable()->index();
            $table->string('resource_type', 120)->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->longText('payload');
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'event_id']);
        });
    }
}
