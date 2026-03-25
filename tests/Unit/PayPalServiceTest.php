<?php

namespace Tests\Unit;

use App\Services\Billing\PayPalService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayPalServiceTest extends TestCase
{
    public function test_capture_order_sends_empty_json_object(): void
    {
        config()->set('services.paypal.mode', 'sandbox');
        config()->set('services.paypal.client_id', 'test-client');
        config()->set('services.paypal.client_secret', 'test-secret');

        Cache::flush();

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'sandbox-token',
            ], 200),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders/ORDER-123/capture' => Http::response([
                'id' => 'ORDER-123',
                'status' => 'COMPLETED',
            ], 200),
        ]);

        $service = app(PayPalService::class);
        $response = $service->captureOrder('ORDER-123');

        $this->assertSame('COMPLETED', $response['status']);

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'POST'
                && $request->url() === 'https://api-m.sandbox.paypal.com/v2/checkout/orders/ORDER-123/capture'
                && $request->body() === '{}';
        });
    }
}
