<?php

namespace App\Services\Billing;

use App\Models\BillingOverrideAudit;
use App\Models\BillingSubscription;
use App\Models\Centros_Medico;
use App\Models\User;

class BillingStateService
{
    public function syncCentroSnapshotFromSubscription(
        Centros_Medico $centro,
        BillingSubscription $subscription
    ): void {
        $derivedStatus = $subscription->isActive() ? 'active' : 'inactive';

        $centro->billing_plan_code = $subscription->plan_code ?: $centro->billing_plan_code;
        $centro->billing_renews_at = $subscription->renews_at;
        $centro->billing_last_sync_at = now();
        $centro->billing_status = $this->applyOverrideToStatus($derivedStatus, $centro->billing_override);
        $centro->save();
    }

    public function refreshCentroFromLatestSubscription(Centros_Medico $centro): void
    {
        $latest = $centro->billingSubscriptions()
            ->orderByDesc('last_synced_at')
            ->orderByDesc('id')
            ->first();

        if (! $latest) {
            $centro->billing_status = $this->applyOverrideToStatus('inactive', $centro->billing_override);
            $centro->billing_plan_code = null;
            $centro->billing_renews_at = null;
            $centro->billing_last_sync_at = now();
            $centro->save();
            return;
        }

        $this->syncCentroSnapshotFromSubscription($centro, $latest);
    }

    public function applyOverride(
        Centros_Medico $centro,
        ?string $newOverride,
        string $reason,
        ?User $performedBy = null
    ): void {
        $oldOverride = $centro->billing_override;

        BillingOverrideAudit::query()->create([
            'centro_id' => $centro->id,
            'performed_by_user_id' => $performedBy?->id,
            'old_override' => $oldOverride,
            'new_override' => $newOverride,
            'reason' => $reason,
            'metadata' => [
                'old_status' => $centro->billing_status,
                'old_plan_code' => $centro->billing_plan_code,
                'old_renews_at' => optional($centro->billing_renews_at)?->toIso8601String(),
            ],
        ]);

        $centro->billing_override = $newOverride;
        $this->refreshCentroFromLatestSubscription($centro);
    }

    public function applyOverrideToStatus(string $derivedStatus, ?string $override): string
    {
        return match ($override) {
            'force_active' => 'active',
            'force_inactive' => 'inactive',
            default => $derivedStatus,
        };
    }
}
