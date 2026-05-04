<?php

namespace App\Services\Billing;

use App\Models\BillingSubscription;
use App\Models\Centros_Medico;
use App\Models\ClinicRegistrationRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class BillingSubscriptionService
{
    public function __construct(
        protected PayPalService $payPalService,
        protected BillingPlanService $planService,
        protected BillingStateService $billingStateService,
    ) {
    }

    public function syncFromPayPalSubscription(
        array $paypalSubscription,
        ?ClinicRegistrationRequest $registration = null,
        ?int $centroId = null
    ): BillingSubscription {
        $subscriptionId = (string) Arr::get($paypalSubscription, 'id', '');
        $providerStatus = (string) Arr::get($paypalSubscription, 'status', '');
        $paypalPlanId = (string) Arr::get($paypalSubscription, 'plan_id', '');
        $planCode = $registration?->plan_code
            ?? $this->planService->getByPayPalPlanId($paypalPlanId);

        $subscription = BillingSubscription::query()->firstOrNew([
            'paypal_subscription_id' => $subscriptionId,
        ]);

        $subscription->fill([
            'provider' => 'paypal',
            'centro_id' => $centroId ?? $subscription->centro_id,
            'clinic_registration_request_id' => $registration?->id
                ?? $subscription->clinic_registration_request_id,
            'paypal_plan_id' => $paypalPlanId ?: null,
            'plan_code' => $planCode,
            'provider_status' => $providerStatus ?: null,
            'status' => $this->payPalService->normalizeStatus($providerStatus),
            'currency' => (string) config('billing.currency', 'USD'),
            'amount' => $this->resolveAmountFromPlan($planCode),
            'starts_at' => $this->toCarbon(Arr::get($paypalSubscription, 'start_time')),
            'current_period_start_at' => $this->toCarbon(Arr::get($paypalSubscription, 'billing_info.last_payment.time')),
            'current_period_end_at' => $this->toCarbon(Arr::get($paypalSubscription, 'billing_info.next_billing_time')),
            'renews_at' => $this->toCarbon(Arr::get($paypalSubscription, 'billing_info.next_billing_time')),
            'canceled_at' => $this->isInactiveProviderStatus($providerStatus) ? now() : null,
            'last_synced_at' => now(),
            'meta' => $paypalSubscription,
        ]);

        $subscription->save();

        if ($subscription->centro_id) {
            $centro = Centros_Medico::query()->find($subscription->centro_id);
            if ($centro) {
                $this->billingStateService->syncCentroSnapshotFromSubscription($centro, $subscription);
            }
        }

        return $subscription;
    }

    public function linkSubscriptionToCentro(string $subscriptionId, int $centroId): void
    {
        $subscription = BillingSubscription::query()
            ->where('paypal_subscription_id', $subscriptionId)
            ->first();

        if (! $subscription) {
            return;
        }

        $subscription->centro_id = $centroId;
        $subscription->save();

        $centro = Centros_Medico::query()->find($centroId);
        if ($centro) {
            $this->billingStateService->syncCentroSnapshotFromSubscription($centro, $subscription);
        }
    }

    protected function resolveAmountFromPlan(?string $planCode): ?float
    {
        if (! $planCode) {
            return null;
        }

        $plan = config("billing.plans.{$planCode}");
        if (! is_array($plan)) {
            return null;
        }

        return isset($plan['price']) ? (float) $plan['price'] : null;
    }

    protected function toCarbon(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function isInactiveProviderStatus(string $providerStatus): bool
    {
        return in_array(strtoupper($providerStatus), ['CANCELLED', 'SUSPENDED', 'EXPIRED'], true);
    }
}
