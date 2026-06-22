<?php

namespace App\Services\Billing;

use Illuminate\Validation\ValidationException;

class BillingPlanService
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return (array) config('billing.plans', []);
    }

    public function defaultPlanCode(): string
    {
        return array_key_first($this->all()) ?? 'monthly';
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $planCode): array
    {
        $plans = $this->all();
        $plan = $plans[$planCode] ?? null;

        if (! is_array($plan)) {
            throw ValidationException::withMessages([
                'plan_code' => 'El plan seleccionado no es valido.',
            ]);
        }

        return $plan;
    }

    public function getPayPalPlanIdOrFail(string $planCode): string
    {
        $plan = $this->get($planCode);
        $paypalPlanId = trim((string) ($plan['paypal_plan_id'] ?? ''));

        if ($paypalPlanId === '') {
            throw ValidationException::withMessages([
                'plan_code' => "No hay PAYPAL_PLAN_ID configurado para el plan {$planCode}.",
            ]);
        }

        return $paypalPlanId;
    }

    public function getByPayPalPlanId(?string $paypalPlanId): ?string
    {
        if (! $paypalPlanId) {
            return null;
        }

        foreach ($this->all() as $code => $plan) {
            if ((string) ($plan['paypal_plan_id'] ?? '') === $paypalPlanId) {
                return (string) $code;
            }
        }

        return null;
    }

    public function priceFor(string $planCode): float
    {
        $plan = $this->get($planCode);

        return (float) ($plan['price'] ?? 0);
    }

    public function intervalFor(string $planCode): string
    {
        $plan = $this->get($planCode);

        return (string) ($plan['interval'] ?? 'monthly');
    }
}
