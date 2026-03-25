<?php

namespace Tests\Feature;

use App\Models\BillingModule;
use App\Models\BillingModuleOrder;
use App\Models\BillingModuleSubscription;
use App\Models\Centros_Medico;
use App\Models\User;
use App\Services\Billing\BillingModuleCatalogService;
use App\Services\Billing\BillingModuleOrderService;
use App\Services\Billing\BillingModuleSubscriptionService;
use App\Services\Billing\PayPalService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class BillingModuleOrderServiceTest extends TestCase
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
        config()->set('billing.module_billing.renewal_days', 30);

        DB::purge('mysql');
        DB::setDefaultConnection('mysql');
        DB::reconnect('mysql');

        $this->createSchema();
    }

    public function test_start_checkout_reuses_pending_order(): void
    {
        [$centro, $user, $module] = $this->baseData();

        $existing = BillingModuleOrder::query()->create([
            'centro_id' => $centro->id,
            'billing_module_id' => $module->id,
            'requested_by_user_id' => $user->id,
            'provider' => 'paypal',
            'paypal_order_id' => 'ORDER-REUSE-1',
            'status' => 'created',
            'currency' => 'USD',
            'amount' => 29.90,
            'approve_url' => 'https://paypal.example/old-approve',
            'return_url' => 'https://tenant.test/return',
            'cancel_url' => 'https://tenant.test/cancel',
            'order_created_at' => now(),
        ]);

        $payPal = Mockery::mock(PayPalService::class)->makePartial();
        $payPal->shouldReceive('getOrder')
            ->once()
            ->with('ORDER-REUSE-1')
            ->andReturn([
                'id' => 'ORDER-REUSE-1',
                'status' => 'CREATED',
                'links' => [
                    ['rel' => 'approve', 'href' => 'https://paypal.example/new-approve'],
                ],
            ]);
        $payPal->shouldNotReceive('createOrder');

        $service = new BillingModuleOrderService(
            payPalService: $payPal,
            catalogService: new BillingModuleCatalogService(),
            subscriptionService: new BillingModuleSubscriptionService(),
        );

        $result = $service->startCheckout(
            centro: $centro,
            requestedBy: $user,
            moduleCode: 'nomina',
            returnUrl: 'https://tenant.test/return',
            cancelUrl: 'https://tenant.test/cancel'
        );

        $this->assertSame($existing->id, $result->id);
        $this->assertSame('https://paypal.example/new-approve', $result->approve_url);
    }

    public function test_capture_from_return_extends_from_current_renewal(): void
    {
        [$centro, $user, $module] = $this->baseData();

        $oldRenew = Carbon::now()->addDays(10)->startOfMinute();

        $subscription = BillingModuleSubscription::query()->create([
            'centro_id' => $centro->id,
            'billing_module_id' => $module->id,
            'status' => 'active',
            'currency' => 'USD',
            'amount' => 29.90,
            'starts_at' => now()->subDays(20),
            'ends_at' => $oldRenew,
            'renews_at' => $oldRenew,
        ]);

        $order = BillingModuleOrder::query()->create([
            'centro_id' => $centro->id,
            'billing_module_id' => $module->id,
            'billing_module_subscription_id' => $subscription->id,
            'requested_by_user_id' => $user->id,
            'provider' => 'paypal',
            'paypal_order_id' => 'ORDER-CAPTURE-1',
            'status' => 'approved',
            'currency' => 'USD',
            'amount' => 29.90,
            'approve_url' => 'https://paypal.example/approve',
            'return_url' => 'https://tenant.test/return',
            'cancel_url' => 'https://tenant.test/cancel',
            'order_created_at' => now()->subMinute(),
            'order_approved_at' => now()->subMinute(),
        ]);

        $capturedAt = now()->toIso8601String();
        $payPal = Mockery::mock(PayPalService::class)->makePartial();
        $payPal->shouldReceive('captureOrder')
            ->once()
            ->with('ORDER-CAPTURE-1')
            ->andReturn([
                'id' => 'ORDER-CAPTURE-1',
                'status' => 'COMPLETED',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => '29.90',
                    ],
                    'payments' => [
                        'captures' => [[
                            'id' => 'CAPTURE-1',
                            'status' => 'COMPLETED',
                            'amount' => [
                                'currency_code' => 'USD',
                                'value' => '29.90',
                            ],
                            'create_time' => $capturedAt,
                        ]],
                    ],
                ]],
            ]);

        $service = new BillingModuleOrderService(
            payPalService: $payPal,
            catalogService: new BillingModuleCatalogService(),
            subscriptionService: new BillingModuleSubscriptionService(),
        );

        $capturedOrder = $service->captureFromReturn('ORDER-CAPTURE-1', $centro);
        $subscription->refresh();

        $this->assertSame('captured', $capturedOrder->status);
        $this->assertSame('CAPTURE-1', $capturedOrder->paypal_capture_id);
        $this->assertSame(
            $oldRenew->copy()->addDays(30)->toDateTimeString(),
            $subscription->renews_at?->toDateTimeString()
        );
    }

    protected function baseData(): array
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

        $user = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'secret123',
        ]);

        $module = BillingModule::query()->create([
            'code' => 'nomina',
            'name' => 'Modulo nomina',
            'description' => 'Modulo de prueba',
            'price_monthly' => 29.90,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        return [$centro, $user, $module];
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

