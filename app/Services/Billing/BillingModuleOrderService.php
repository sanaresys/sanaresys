<?php

namespace App\Services\Billing;

use App\Models\BillingModule;
use App\Models\BillingModuleOrder;
use App\Models\Centros_Medico;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingModuleOrderService
{
    public function __construct(
        protected PayPalService $payPalService,
        protected BillingModuleCatalogService $catalogService,
        protected BillingModuleSubscriptionService $subscriptionService,
    ) {
    }

    public function startCheckout(
        Centros_Medico $centro,
        User $requestedBy,
        string $moduleCode,
        string $returnUrl,
        string $cancelUrl
    ): BillingModuleOrder {
        $module = $this->catalogService->getByCodeOrFail($moduleCode);

        $reusable = $this->findReusablePendingOrder($centro->id, $module->id);
        if ($reusable && $reusable->approve_url) {
            return $reusable;
        }

        $customId = sprintf(
            'module:%d:%s:%d:%d',
            $centro->id,
            $module->code,
            $requestedBy->id,
            now()->timestamp
        );

        $result = $this->payPalService->createOrder(
            amount: (float) $module->price_monthly,
            currency: (string) $module->currency,
            description: "Modulo {$module->name}",
            customId: $customId,
            returnUrl: $returnUrl,
            cancelUrl: $cancelUrl,
        );

        return BillingModuleOrder::query()->create([
            'centro_id' => $centro->id,
            'billing_module_id' => $module->id,
            'requested_by_user_id' => $requestedBy->id,
            'provider' => 'paypal',
            'paypal_order_id' => (string) $result['id'],
            'custom_id' => $customId,
            'status' => $this->normalizeOrderStatus((string) ($result['status'] ?? '')),
            'currency' => (string) $module->currency,
            'amount' => (float) $module->price_monthly,
            'approve_url' => (string) ($result['approve_url'] ?? ''),
            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            'order_created_at' => now(),
            'payload' => (array) ($result['raw'] ?? []),
        ]);
    }

    public function captureFromReturn(
        string $paypalOrderId,
        Centros_Medico $centro
    ): BillingModuleOrder {
        $order = BillingModuleOrder::query()
            ->where('paypal_order_id', $paypalOrderId)
            ->where('centro_id', $centro->id)
            ->firstOrFail();

        if ($order->status === 'captured' && $order->captured_at) {
            return $order;
        }

        $capturePayload = $this->payPalService->captureOrder($paypalOrderId);

        return $this->applyCapturedOrderPayload($order, $capturePayload);
    }

    public function handleCaptureCompleted(
        ?string $paypalOrderId,
        ?string $paypalCaptureId,
        array $payload
    ): ?BillingModuleOrder {
        $order = $this->findOrderForCapture($paypalOrderId, $paypalCaptureId);
        if (! $order) {
            return null;
        }

        if ($order->status === 'captured' && $order->captured_at) {
            return $order;
        }

        if ($paypalOrderId) {
            try {
                $providerOrder = $this->payPalService->getOrder($paypalOrderId);
                return $this->applyCapturedOrderPayload($order, $providerOrder);
            } catch (\Throwable $e) {
                Log::warning('No se pudo consultar orden en webhook de captura; se usa payload recibido.', [
                    'paypal_order_id' => $paypalOrderId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->applyCapturedOrderPayload($order, $payload);
    }

    public function handleRefundReview(
        string $paypalCaptureId,
        array $payload
    ): ?BillingModuleOrder {
        $order = BillingModuleOrder::query()
            ->where('paypal_capture_id', $paypalCaptureId)
            ->first();

        if (! $order) {
            return null;
        }

        $order->status = 'refund_review';
        $order->refunded_at = $this->toCarbon(
            Arr::get($payload, 'resource.update_time')
                ?? Arr::get($payload, 'resource.create_time')
        ) ?? now();
        $order->capture_payload = $this->mergePayload($order->capture_payload, [
            'latest_refund_event' => $payload,
        ]);
        $order->save();

        $this->subscriptionService->markRefundReviewFromOrder($order, $payload);

        return $order;
    }

    public function findReusablePendingOrder(int $centroId, int $moduleId): ?BillingModuleOrder
    {
        $order = BillingModuleOrder::query()
            ->where('centro_id', $centroId)
            ->where('billing_module_id', $moduleId)
            ->whereIn('status', ['created', 'approved'])
            ->orderByDesc('id')
            ->first();

        if (! $order) {
            return null;
        }

        try {
            $providerOrder = $this->payPalService->getOrder($order->paypal_order_id);
            $providerStatus = $this->normalizeOrderStatus((string) Arr::get($providerOrder, 'status', ''));
            $order->status = $providerStatus;
            $order->approve_url = $this->payPalService->extractApproveUrl($providerOrder) ?: $order->approve_url;
            $order->payload = $this->mergePayload($order->payload, ['latest_provider_order' => $providerOrder]);
            $order->order_approved_at = $providerStatus === 'approved'
                ? ($order->order_approved_at ?: now())
                : $order->order_approved_at;
            $order->save();
        } catch (\Throwable $e) {
            Log::warning('No se pudo validar orden pendiente de modulo, se reutiliza registro local.', [
                'order_id' => $order->id,
                'paypal_order_id' => $order->paypal_order_id,
                'error' => $e->getMessage(),
            ]);
        }

        return in_array($order->status, ['created', 'approved'], true)
            ? $order
            : null;
    }

    protected function applyCapturedOrderPayload(
        BillingModuleOrder $order,
        array $payload
    ): BillingModuleOrder {
        return DB::connection('mysql')->transaction(function () use ($order, $payload): BillingModuleOrder {
            $captureId = $this->payPalService->extractOrderCaptureId($payload) ?: $order->paypal_capture_id;
            $capturedAt = $this->toCarbon($this->payPalService->extractOrderCaptureTime($payload)) ?? now();
            $amount = $this->payPalService->extractOrderAmount($payload);

            $order->status = 'captured';
            $order->paypal_capture_id = $captureId;
            $order->captured_at = $capturedAt;
            $order->order_approved_at = $order->order_approved_at ?: $capturedAt;
            $order->amount = $amount ?? $order->amount;
            $order->capture_payload = $payload;
            $order->save();

            $this->subscriptionService->activateOrRenewFromOrder($order, $capturedAt);

            return $order->refresh();
        });
    }

    protected function findOrderForCapture(
        ?string $paypalOrderId,
        ?string $paypalCaptureId
    ): ?BillingModuleOrder {
        if ($paypalOrderId) {
            $order = BillingModuleOrder::query()
                ->where('paypal_order_id', $paypalOrderId)
                ->first();

            if ($order) {
                return $order;
            }
        }

        if ($paypalCaptureId) {
            return BillingModuleOrder::query()
                ->where('paypal_capture_id', $paypalCaptureId)
                ->first();
        }

        return null;
    }

    protected function normalizeOrderStatus(?string $providerStatus): string
    {
        return match (strtoupper((string) $providerStatus)) {
            'CREATED', 'PAYER_ACTION_REQUIRED' => 'created',
            'APPROVED' => 'approved',
            'COMPLETED' => 'captured',
            'VOIDED' => 'canceled',
            default => 'created',
        };
    }

    protected function toCarbon(?string $value): ?Carbon
    {
        if (! $value || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function mergePayload(mixed $current, array $extra): array
    {
        $base = is_array($current) ? $current : [];
        return array_merge($base, $extra);
    }
}

