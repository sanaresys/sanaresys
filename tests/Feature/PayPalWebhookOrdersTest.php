<?php

namespace Tests\Feature;

use App\Models\BillingModule;
use App\Models\BillingModuleOrder;
use App\Models\BillingModuleSubscription;
use App\Models\BillingWebhookEvent;
use App\Models\Centros_Medico;
use App\Services\Billing\PayPalService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class PayPalWebhookOrdersTest extends TestCase
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
        config()->set('billing.module_billing.renewal_days', 30);

        DB::purge('mysql');
        DB::setDefaultConnection('mysql');
        DB::reconnect('mysql');

        $this->createSchema();
    }

    public function test_webhook_capture_completed_marks_order_captured_and_module_active(): void
    {
        [$centro, $module] = $this->createBaseModuleContext();

        $order = BillingModuleOrder::query()->create([
            'centro_id' => $centro->id,
            'billing_module_id' => $module->id,
            'provider' => 'paypal',
            'paypal_order_id' => 'ORDER-WH-1',
            'status' => 'approved',
            'currency' => 'USD',
            'amount' => 29.90,
            'approve_url' => 'https://paypal.test/approve',
            'order_created_at' => now()->subMinute(),
            'order_approved_at' => now()->subMinute(),
        ]);

        $payPal = Mockery::mock(PayPalService::class)->makePartial();
        $payPal->shouldReceive('verifyWebhookSignature')->once()->andReturn(true);
        $payPal->shouldReceive('getOrder')
            ->once()
            ->with('ORDER-WH-1')
            ->andReturn([
                'id' => 'ORDER-WH-1',
                'status' => 'COMPLETED',
                'purchase_units' => [[
                    'amount' => ['currency_code' => 'USD', 'value' => '29.90'],
                    'payments' => [
                        'captures' => [[
                            'id' => 'CAPTURE-WH-1',
                            'status' => 'COMPLETED',
                            'amount' => ['currency_code' => 'USD', 'value' => '29.90'],
                            'create_time' => now()->toIso8601String(),
                        ]],
                    ],
                ]],
            ]);
        $this->app->instance(PayPalService::class, $payPal);

        $response = $this->postJson(route('webhooks.paypal'), [
            'id' => 'WH-ORDER-1',
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => [
                'id' => 'CAPTURE-WH-1',
                'supplementary_data' => [
                    'related_ids' => [
                        'order_id' => 'ORDER-WH-1',
                    ],
                ],
            ],
        ], $this->validWebhookHeaders());

        $response->assertOk();
        $order->refresh();

        $subscription = BillingModuleSubscription::query()
            ->where('centro_id', $centro->id)
            ->where('billing_module_id', $module->id)
            ->first();

        $this->assertSame('captured', $order->status);
        $this->assertSame('CAPTURE-WH-1', $order->paypal_capture_id);
        $this->assertNotNull($subscription);
        $this->assertSame('active', $subscription->status);
        $this->assertDatabaseHas('billing_webhook_events', [
            'event_id' => 'WH-ORDER-1',
            'status' => 'processed',
        ], 'mysql');
    }

    public function test_webhook_refunded_marks_order_and_subscription_in_refund_review(): void
    {
        [$centro, $module] = $this->createBaseModuleContext();

        $subscription = BillingModuleSubscription::query()->create([
            'centro_id' => $centro->id,
            'billing_module_id' => $module->id,
            'status' => 'active',
            'currency' => 'USD',
            'amount' => 29.90,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(20),
            'renews_at' => now()->addDays(20),
            'last_payment_at' => now()->subDays(10),
            'last_paypal_order_id' => 'ORDER-WH-2',
            'last_paypal_capture_id' => 'CAPTURE-WH-2',
        ]);

        $order = BillingModuleOrder::query()->create([
            'centro_id' => $centro->id,
            'billing_module_id' => $module->id,
            'billing_module_subscription_id' => $subscription->id,
            'provider' => 'paypal',
            'paypal_order_id' => 'ORDER-WH-2',
            'paypal_capture_id' => 'CAPTURE-WH-2',
            'status' => 'captured',
            'currency' => 'USD',
            'amount' => 29.90,
            'captured_at' => now()->subDays(10),
            'order_created_at' => now()->subDays(10),
        ]);

        $payPal = Mockery::mock(PayPalService::class)->makePartial();
        $payPal->shouldReceive('verifyWebhookSignature')->once()->andReturn(true);
        $this->app->instance(PayPalService::class, $payPal);

        $response = $this->postJson(route('webhooks.paypal'), [
            'id' => 'WH-ORDER-2',
            'event_type' => 'PAYMENT.CAPTURE.REFUNDED',
            'resource' => [
                'id' => 'CAPTURE-WH-2',
                'create_time' => now()->toIso8601String(),
            ],
        ], $this->validWebhookHeaders());

        $response->assertOk();
        $order->refresh();
        $subscription->refresh();

        $this->assertSame('refund_review', $order->status);
        $this->assertSame('refund_review', $subscription->status);
        $this->assertDatabaseHas('billing_webhook_events', [
            'event_id' => 'WH-ORDER-2',
            'status' => 'processed',
        ], 'mysql');
    }

    protected function createBaseModuleContext(): array
    {
        $centro = Centros_Medico::query()->create([
            'nombre_centro' => 'Clinica Test',
            'slug' => 'clinica-test',
            'tenancy_mode' => 'domain',
            'billing_status' => 'active',
            'direccion' => 'Dir',
            'telefono' => '9999-9999',
            'rtn' => '08011999123456',
        ]);

        $module = BillingModule::query()->create([
            'code' => 'nomina',
            'name' => 'Modulo nomina',
            'description' => 'Modulo de prueba',
            'price_monthly' => 29.90,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        return [$centro, $module];
    }

    protected function validWebhookHeaders(): array
    {
        return [
            'PAYPAL-TRANSMISSION-ID' => 'test-transmission',
            'PAYPAL-TRANSMISSION-TIME' => now()->toIso8601String(),
            'PAYPAL-CERT-URL' => 'https://api-m.sandbox.paypal.com/certs/cert.pem',
            'PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
            'PAYPAL-TRANSMISSION-SIG' => 'test-signature',
        ];
    }

    protected function createSchema(): void
    {
        $schema = Schema::connection('mysql');

        $schema->create('centros_medicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_centro');
            $table->string('slug')->nullable();
            $table->string('tenancy_mode')->default('domain');
            $table->string('billing_status')->default('inactive');
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('rtn')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->timestamps();
            $table->softDeletes();
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

        $schema->create('billing_modules', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        $schema->create('billing_module_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('centro_id');
            $table->unsignedBigInteger('billing_module_id');
            $table->string('status', 32)->default('inactive');
            $table->string('currency', 3)->default('USD');
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('renews_at')->nullable();
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamp('last_refund_at')->nullable();
            $table->string('last_paypal_order_id')->nullable();
            $table->string('last_paypal_capture_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        $schema->create('billing_module_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('centro_id');
            $table->unsignedBigInteger('billing_module_id');
            $table->unsignedBigInteger('billing_module_subscription_id')->nullable();
            $table->unsignedBigInteger('requested_by_user_id')->nullable();
            $table->string('provider', 32)->default('paypal');
            $table->string('paypal_order_id')->unique();
            $table->string('paypal_capture_id')->nullable();
            $table->string('custom_id')->nullable();
            $table->string('status', 32)->default('created');
            $table->string('currency', 3)->default('USD');
            $table->decimal('amount', 10, 2);
            $table->text('approve_url')->nullable();
            $table->text('return_url')->nullable();
            $table->text('cancel_url')->nullable();
            $table->timestamp('order_created_at')->nullable();
            $table->timestamp('order_approved_at')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->json('payload')->nullable();
            $table->json('capture_payload')->nullable();
            $table->timestamps();
        });
    }
}

