<?php

namespace App\Services\Billing;

use App\Models\BillingSubscription;
use App\Models\BillingTenantSubscription;
use App\Models\Centros_Medico;
use App\Models\User;

class BillingStateService
{
    public function __construct(
        protected BillingAuditService $auditService,
        protected BillingPeriodService $periodService,
    ) {
    }

    public function syncCentroSnapshotFromTenantSubscription(BillingTenantSubscription $subscription): void
    {
        $subscription->loadMissing('centro');

        if (! $subscription->centro) {
            return;
        }

        $centro = $subscription->centro;
        $derivedStatus = $this->deriveTenantStatus($subscription);

        $centro->billing_plan_code = $subscription->plan_code ?: $centro->billing_plan_code;
        $centro->billing_renews_at = $subscription->current_period_ends_at ?: $subscription->next_charge_at;
        $centro->billing_last_sync_at = $this->periodService->now();
        $centro->billing_status = $this->applyOverrideToStatus($derivedStatus, $centro->billing_override);
        $centro->save();
    }

    public function syncCentroSnapshotFromSubscription(
        Centros_Medico $centro,
        BillingSubscription $subscription,
    ): void {
        $derivedStatus = $subscription->isActive() ? 'active' : 'suspended';

        $centro->billing_plan_code = $subscription->plan_code ?: $centro->billing_plan_code;
        $centro->billing_renews_at = $subscription->renews_at;
        $centro->billing_last_sync_at = $this->periodService->now();
        $centro->billing_status = $this->applyOverrideToStatus($derivedStatus, $centro->billing_override);
        $centro->save();
    }

    public function refreshCentroState(Centros_Medico $centro): void
    {
        $tenantSubscription = $centro->billingTenantSubscription()
            ->latest('id')
            ->first();

        if ($tenantSubscription) {
            $this->syncCentroSnapshotFromTenantSubscription($tenantSubscription);

            return;
        }

        $this->refreshCentroFromLatestSubscription($centro);
    }

    public function refreshCentroFromLatestSubscription(Centros_Medico $centro): void
    {
        $latest = $centro->billingSubscriptions()
            ->orderByDesc('last_synced_at')
            ->orderByDesc('id')
            ->first();

        if (! $latest) {
            $centro->billing_status = $this->applyOverrideToStatus('suspended', $centro->billing_override);
            $centro->billing_plan_code = $centro->billing_plan_code;
            $centro->billing_renews_at = $centro->billing_renews_at;
            $centro->billing_last_sync_at = $this->periodService->now();
            $centro->save();

            return;
        }

        $this->syncCentroSnapshotFromSubscription($centro, $latest);
    }

    public function applyOverride(
        Centros_Medico $centro,
        ?string $newOverride,
        string $reason,
        ?User $performedBy = null,
    ): void {
        $oldOverride = $centro->billing_override;
        $oldStatus = $centro->billing_status;

        $centro->billing_override = $newOverride;
        $centro->save();

        $this->refreshCentroState($centro);

        $this->auditService->log(
            eventType: 'billing.override.updated',
            centro: $centro,
            actor: $performedBy,
            actorType: $performedBy ? 'user' : 'system',
            reason: $reason,
            oldValues: [
                'billing_override' => $oldOverride,
                'billing_status' => $oldStatus,
            ],
            newValues: [
                'billing_override' => $centro->billing_override,
                'billing_status' => $centro->fresh()->billing_status,
            ],
        );
    }

    public function applyOverrideToStatus(string $derivedStatus, ?string $override): string
    {
        return match ($override) {
            'force_active' => 'active',
            'force_inactive' => 'suspended',
            default => $derivedStatus,
        };
    }

    public function deriveTenantStatus(BillingTenantSubscription $subscription): string
    {
        $now = $this->periodService->now();

        if ($subscription->cancel_at_period_end
            && $subscription->current_period_ends_at
            && $now->gte($subscription->current_period_ends_at)) {
            return 'canceled';
        }

        if ($subscription->status === 'canceled') {
            return 'canceled';
        }

        if ($subscription->grace_until && $now->gt($subscription->grace_until)) {
            return 'suspended';
        }

        return match ($subscription->status) {
            'active', 'past_due', 'grace', 'suspended' => $subscription->status,
            default => 'suspended',
        };
    }
}
