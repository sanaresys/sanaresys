<?php

namespace App\Services\Billing;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayPalService
{
    public function createOrder(
        float $amount,
        string $currency,
        string $description,
        string $customId,
        string $returnUrl,
        string $cancelUrl
    ): array {
        $response = $this->http()
            ->post('/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => 'billing_order',
                    'custom_id' => $customId,
                    'description' => $description,
                    'amount' => [
                        'currency_code' => strtoupper($currency),
                        'value' => number_format($amount, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'brand_name' => (string) config('app.name', 'Sanare'),
                    'locale' => 'es-HN',
                    'user_action' => 'PAY_NOW',
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                ],
            ])
            ->throw()
            ->json();

        $approveUrl = $this->extractApproveUrl($response);

        if (! $approveUrl) {
            throw new RuntimeException('PayPal no devolvio URL de aprobacion para orden.');
        }

        return [
            'id' => (string) ($response['id'] ?? ''),
            'status' => (string) ($response['status'] ?? ''),
            'approve_url' => (string) $approveUrl,
            'raw' => $response,
        ];
    }

    public function getOrder(string $orderId): array
    {
        return $this->http()
            ->get("/v2/checkout/orders/{$orderId}")
            ->throw()
            ->json();
    }

    public function captureOrder(string $orderId): array
    {
        return $this->http()
            ->post("/v2/checkout/orders/{$orderId}/capture", new \stdClass())
            ->throw()
            ->json();
    }

    public function createSubscription(
        string $paypalPlanId,
        string $customId,
        string $returnUrl,
        string $cancelUrl
    ): array {
        $response = $this->http()
            ->post('/v1/billing/subscriptions', [
                'plan_id' => $paypalPlanId,
                'custom_id' => $customId,
                'application_context' => [
                    'brand_name' => (string) config('app.name', 'Sanare'),
                    'locale' => 'es-HN',
                    'user_action' => 'SUBSCRIBE_NOW',
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                ],
            ])
            ->throw()
            ->json();

        $approveUrl = $this->extractApproveUrl($response);

        if (! $approveUrl) {
            throw new RuntimeException('PayPal no devolvio URL de aprobacion.');
        }

        return [
            'id' => (string) ($response['id'] ?? ''),
            'status' => (string) ($response['status'] ?? ''),
            'approve_url' => (string) $approveUrl,
            'raw' => $response,
        ];
    }

    public function getSubscription(string $subscriptionId): array
    {
        return $this->http()
            ->get("/v1/billing/subscriptions/{$subscriptionId}")
            ->throw()
            ->json();
    }

    public function verifyWebhookSignature(Request $request, array $eventPayload): bool
    {
        $webhookId = trim((string) config('services.paypal.webhook_id', ''));
        if ($webhookId === '') {
            return false;
        }

        $verification = $this->http()
            ->post('/v1/notifications/verify-webhook-signature', [
                'transmission_id' => (string) $request->header('PAYPAL-TRANSMISSION-ID', ''),
                'transmission_time' => (string) $request->header('PAYPAL-TRANSMISSION-TIME', ''),
                'cert_url' => (string) $request->header('PAYPAL-CERT-URL', ''),
                'auth_algo' => (string) $request->header('PAYPAL-AUTH-ALGO', ''),
                'transmission_sig' => (string) $request->header('PAYPAL-TRANSMISSION-SIG', ''),
                'webhook_id' => $webhookId,
                'webhook_event' => $eventPayload,
            ])
            ->throw()
            ->json();

        return strtoupper((string) Arr::get($verification, 'verification_status', '')) === 'SUCCESS';
    }

    public function normalizeStatus(?string $providerStatus): string
    {
        return strtoupper((string) $providerStatus) === 'ACTIVE'
            ? 'active'
            : 'inactive';
    }

    public function extractOrderCaptureId(array $payload): ?string
    {
        $captureId = Arr::get($payload, 'purchase_units.0.payments.captures.0.id');
        if (is_string($captureId) && $captureId !== '') {
            return $captureId;
        }

        $captureId = Arr::get($payload, 'purchase_units.0.payments.authorizations.0.id');
        if (is_string($captureId) && $captureId !== '') {
            return $captureId;
        }

        return null;
    }

    public function extractOrderCaptureTime(array $payload): ?string
    {
        $time = Arr::get($payload, 'purchase_units.0.payments.captures.0.create_time')
            ?? Arr::get($payload, 'purchase_units.0.payments.captures.0.update_time')
            ?? Arr::get($payload, 'create_time')
            ?? Arr::get($payload, 'update_time');

        return is_string($time) && $time !== '' ? $time : null;
    }

    public function extractOrderAmount(array $payload): ?float
    {
        $value = Arr::get($payload, 'purchase_units.0.payments.captures.0.amount.value')
            ?? Arr::get($payload, 'purchase_units.0.amount.value');

        if (! is_scalar($value) || trim((string) $value) === '') {
            return null;
        }

        return (float) $value;
    }

    public function extractApproveUrl(array $response): ?string
    {
        foreach ((array) ($response['links'] ?? []) as $link) {
            if (($link['rel'] ?? null) === 'approve') {
                return isset($link['href']) ? (string) $link['href'] : null;
            }
        }

        return null;
    }

    protected function http(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->asJson()
            ->withToken($this->accessToken());
    }

    protected function accessToken(): string
    {
        $cacheKey = 'paypal:access-token:' . $this->mode();

        return Cache::remember($cacheKey, now()->addMinutes(50), function (): string {
            $response = Http::asForm()
                ->withBasicAuth(
                    (string) config('services.paypal.client_id', ''),
                    (string) config('services.paypal.client_secret', ''),
                )
                ->post($this->baseUrl() . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ])
                ->throw()
                ->json();

            $token = (string) Arr::get($response, 'access_token', '');
            if ($token === '') {
                throw new RuntimeException('No se pudo obtener access token de PayPal.');
            }

            return $token;
        });
    }

    protected function baseUrl(): string
    {
        return $this->mode() === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    protected function mode(): string
    {
        return strtolower((string) config('services.paypal.mode', 'sandbox')) === 'live'
            ? 'live'
            : 'sandbox';
    }
}
