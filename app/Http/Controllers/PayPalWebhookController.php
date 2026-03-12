<?php

namespace App\Http\Controllers;

use App\Models\BillingWebhookEvent;
use App\Models\ClinicRegistrationRequest;
use App\Services\Billing\BillingSubscriptionService;
use App\Services\Billing\PayPalService;
use App\Services\Billing\RegistrationProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

class PayPalWebhookController extends Controller
{
    public function __construct(
        protected PayPalService $payPalService,
        protected BillingSubscriptionService $billingSubscriptionService,
        protected RegistrationProvisioningService $registrationProvisioningService,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $payload = (array) $request->json()->all();
        $eventId = (string) Arr::get($payload, 'id', '');
        $eventType = (string) Arr::get($payload, 'event_type', '');
        $resourceType = (string) Arr::get($payload, 'resource_type', '');

        if ($eventId === '') {
            return response()->json(['message' => 'Event ID requerido.'], 422);
        }

        $event = BillingWebhookEvent::query()->firstOrCreate(
            [
                'provider' => 'paypal',
                'event_id' => $eventId,
            ],
            [
                'event_type' => $eventType ?: null,
                'resource_type' => $resourceType ?: null,
                'status' => 'pending',
                'payload' => $payload,
            ]
        );

        if ($event->status === 'processed') {
            return response()->json(['message' => 'Duplicate event ignored.'], 200);
        }

        try {
            if (! $this->payPalService->verifyWebhookSignature($request, $payload)) {
                $event->update([
                    'status' => 'failed',
                    'error_message' => 'Firma invalida de webhook PayPal.',
                ]);

                return response()->json(['message' => 'Invalid signature.'], 400);
            }

            $subscriptionId = $this->extractSubscriptionId($payload);
            if ($subscriptionId === null) {
                $event->update([
                    'status' => 'ignored',
                    'processed_at' => now(),
                    'payload' => $payload,
                ]);

                return response()->json(['message' => 'No subscription id in webhook payload.'], 200);
            }

            $subscriptionData = $this->payPalService->getSubscription($subscriptionId);
            $registration = ClinicRegistrationRequest::query()
                ->where('paypal_subscription_id', $subscriptionId)
                ->first();

            $subscription = $this->billingSubscriptionService->syncFromPayPalSubscription(
                paypalSubscription: $subscriptionData,
                registration: $registration,
                centroId: $registration?->centro_id
            );

            if ($registration) {
                $normalized = $this->payPalService->normalizeStatus((string) ($subscriptionData['status'] ?? ''));

                $registration->forceFill([
                    'status' => $registration->isProvisioned()
                        ? ClinicRegistrationRequest::STATUS_PROVISIONED
                        : ClinicRegistrationRequest::STATUS_PENDING_PAYMENT,
                    'payment_status' => $normalized === 'active' ? 'active' : 'pending',
                    'paypal_subscription_id' => $subscriptionId,
                    'paypal_plan_id' => (string) ($subscriptionData['plan_id'] ?? $registration->paypal_plan_id),
                    'payment_approved_at' => $normalized === 'active'
                        ? ($registration->payment_approved_at ?? now())
                        : $registration->payment_approved_at,
                ])->save();

                if (! $registration->isProvisioned() && $normalized === 'active') {
                    $this->registrationProvisioningService->provisionFromPaidRegistration($registration);
                }
            }

            $event->update([
                'event_type' => $eventType ?: $event->event_type,
                'resource_type' => $resourceType ?: $event->resource_type,
                'status' => 'processed',
                'processed_at' => now(),
                'payload' => $payload,
                'error_message' => null,
            ]);

            Log::info('Webhook PayPal procesado.', [
                'event_id' => $eventId,
                'event_type' => $eventType,
                'subscription_id' => $subscriptionId,
                'subscription_status' => $subscription->status,
            ]);

            return response()->json(['message' => 'Processed'], 200);
        } catch (Throwable $e) {
            $event->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'payload' => $payload,
            ]);

            Log::error('Error procesando webhook PayPal.', [
                'event_id' => $eventId,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return response()->json(['message' => 'Webhook processing failed.'], 500);
        }
    }

    protected function extractSubscriptionId(array $payload): ?string
    {
        $candidates = [
            Arr::get($payload, 'resource.id'),
            Arr::get($payload, 'resource.subscription_id'),
            Arr::get($payload, 'resource.billing_agreement_id'),
            Arr::get($payload, 'resource.links.0.href'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                if (str_contains($candidate, '/v1/billing/subscriptions/')) {
                    $segments = explode('/', trim($candidate, '/'));
                    return end($segments) ?: null;
                }

                return $candidate;
            }
        }

        return null;
    }
}
