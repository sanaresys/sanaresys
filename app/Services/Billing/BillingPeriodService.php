<?php

namespace App\Services\Billing;

use App\Models\BillingModule;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class BillingPeriodService
{
    public function __construct(
        protected BillingPlanService $planService,
    ) {
    }

    public function now(): Carbon
    {
        return now($this->timezone());
    }

    public function timezone(): string
    {
        return (string) config('billing.engine.timezone', 'America/Tegucigalpa');
    }

    public function graceDays(): int
    {
        return max(1, (int) config('billing.engine.grace_days', 3));
    }

    public function maxDunningAttempts(): int
    {
        return max(1, (int) config('billing.engine.max_dunning_attempts', 3));
    }

    public function addInterval(Carbon $date, string $interval): Carbon
    {
        $copy = $date->copy();

        return match ($interval) {
            'annual' => $copy->addYearNoOverflow(),
            'monthly' => $copy->addMonthNoOverflow(),
            default => throw new InvalidArgumentException("Unsupported interval [{$interval}]."),
        };
    }

    public function subtractInterval(Carbon $date, string $interval): Carbon
    {
        $copy = $date->copy();

        return match ($interval) {
            'annual' => $copy->subYearNoOverflow(),
            'monthly' => $copy->subMonthNoOverflow(),
            default => throw new InvalidArgumentException("Unsupported interval [{$interval}]."),
        };
    }

    /**
     * @return array{starts_at: Carbon, ends_at: Carbon}
     */
    public function currentCycleForAnchor(Carbon $anchor, string $interval, ?Carbon $reference = null): array
    {
        $reference ??= $this->now();
        $start = $anchor->copy();
        $end = $this->addInterval($start, $interval);

        while ($reference->gte($end)) {
            $start = $end->copy();
            $end = $this->addInterval($start, $interval);
        }

        return [
            'starts_at' => $start,
            'ends_at' => $end,
        ];
    }

    /**
     * @return array{starts_at: Carbon, ends_at: Carbon}
     */
    public function nextCycleFrom(Carbon $startsAt, string $interval): array
    {
        return [
            'starts_at' => $startsAt->copy(),
            'ends_at' => $this->addInterval($startsAt, $interval),
        ];
    }

    public function proratedAmount(
        float $fullAmount,
        Carbon $periodStartsAt,
        Carbon $periodEndsAt,
        ?Carbon $chargeAt = null,
    ): float {
        $chargeAt ??= $this->now();
        $totalSeconds = max(1, $periodStartsAt->diffInSeconds($periodEndsAt, false));
        $remainingSeconds = max(0, $chargeAt->diffInSeconds($periodEndsAt, false));

        if ($remainingSeconds <= 0) {
            return round($fullAmount, 2);
        }

        return round(($fullAmount * $remainingSeconds) / $totalSeconds, 2);
    }

    public function graceUntil(Carbon $dueAt): Carbon
    {
        return $dueAt->copy()
            ->startOfDay()
            ->addDays($this->graceDays() - 1)
            ->endOfDay();
    }

    public function planPrice(string $planCode): float
    {
        return $this->planService->priceFor($planCode);
    }

    public function planInterval(string $planCode): string
    {
        return $this->planService->intervalFor($planCode);
    }

    public function modulePrice(BillingModule $module, string $interval): float
    {
        return $interval === 'annual'
            ? (float) ($module->price_annual ?: ((float) $module->price_monthly * 12))
            : (float) $module->price_monthly;
    }
}
