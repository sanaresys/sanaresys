<?php

namespace App\Services\Billing;

use App\Models\BillingModuleOrder;
use App\Models\BillingModuleSubscription;
use Illuminate\Support\Carbon;

class BillingModuleSubscriptionService
{
    public function activateOrRenewFromOrder(
        BillingModuleOrder $order,
        ?Carbon $capturedAt = null
    ): BillingModuleSubscription {
        $capturedAt ??= now();
        $renewalDays = (int) config('billing.module_billing.renewal_days', 30);

        $subscription = BillingModuleSubscription::query()->firstOrNew([
            'centro_id' => $order->centro_id,
            'billing_module_id' => $order->billing_module_id,
        ]);

        $anchor = $capturedAt->copy();
        if ($subscription->renews_at && $subscription->renews_at->isFuture()) {
            $anchor = $subscription->renews_at->copy();
        }

        $subscription->fill([
            'status' => 'active',
            'currency' => $order->currency,
            'amount' => $order->amount,
            'starts_at' => $subscription->starts_at ?: $capturedAt,
            'ends_at' => $anchor->copy()->addDays($renewalDays),
            'renews_at' => $anchor->copy()->addDays($renewalDays),
            'last_payment_at' => $capturedAt,
            'last_paypal_order_id' => $order->paypal_order_id,
            'last_paypal_capture_id' => $order->paypal_capture_id,
            'meta' => $this->mergeMeta(
                $subscription->meta,
                [
                    'last_status_reason' => 'captured_order',
                    'last_order_id' => $order->paypal_order_id,
                ]
            ),
        ]);
        $subscription->save();

        $order->billing_module_subscription_id = $subscription->id;
        $order->save();

        return $subscription;
    }

    public function markRefundReviewFromOrder(
        BillingModuleOrder $order,
        array $refundPayload
    ): ?BillingModuleSubscription {
        $subscription = BillingModuleSubscription::query()
            ->where('centro_id', $order->centro_id)
            ->where('billing_module_id', $order->billing_module_id)
            ->first();

        if (! $subscription) {
            return null;
        }

        $subscription->status = 'refund_review';
        $subscription->last_refund_at = now();
        $subscription->meta = $this->mergeMeta($subscription->meta, [
            'last_status_reason' => 'refund_review',
            'last_refund_payload' => $refundPayload,
        ]);
        $subscription->save();

        return $subscription;
    }

    protected function mergeMeta(mixed $current, array $extra): array
    {
        $base = is_array($current) ? $current : [];

        return array_merge($base, $extra);
    }
}

