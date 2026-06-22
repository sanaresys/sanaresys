<?php

namespace App\Services\Billing;

use App\Models\BillingInvoice;
use App\Models\Centros_Medico;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class BillingAdminService
{
    public function __construct(
        protected BillingInvoiceService $invoiceService,
        protected BillingPeriodService $periodService,
        protected BillingStateService $billingStateService,
        protected BillingAuditService $auditService,
    ) {
    }

    public function markInvoicePaid(BillingInvoice $invoice, ?User $actor = null, ?string $reason = null): void
    {
        $this->invoiceService->recordOfflinePayment($invoice, $actor, $reason);
    }

    public function extendTenantPeriod(
        Centros_Medico $centro,
        int $days,
        ?User $actor = null,
        ?string $reason = null,
    ): void {
        if ($days < 1) {
            throw ValidationException::withMessages([
                'days' => 'La extension debe ser al menos de 1 dia.',
            ]);
        }

        $subscription = $this->invoiceService->ensureTenantSubscriptionForCentro($centro);
        $oldValues = [
            'current_period_ends_at' => optional($subscription->current_period_ends_at)?->toIso8601String(),
            'next_charge_at' => optional($subscription->next_charge_at)?->toIso8601String(),
        ];

        $subscription->current_period_ends_at = ($subscription->current_period_ends_at ?: $this->periodService->now())
            ->copy()
            ->addDays($days);
        $subscription->next_charge_at = $subscription->current_period_ends_at->copy();
        $subscription->status = 'active';
        $subscription->grace_until = null;
        $subscription->save();

        $this->billingStateService->syncCentroSnapshotFromTenantSubscription($subscription->fresh());

        $this->auditService->log(
            eventType: 'billing.subscription.extended',
            centro: $centro,
            tenantSubscription: $subscription,
            actor: $actor,
            actorType: $actor ? 'user' : 'system',
            reason: $reason ?: sprintf('Vigencia extendida manualmente %d dias.', $days),
            oldValues: $oldValues,
            newValues: [
                'current_period_ends_at' => optional($subscription->current_period_ends_at)->toIso8601String(),
                'next_charge_at' => optional($subscription->next_charge_at)->toIso8601String(),
            ],
        );
    }

    public function setTenantStatus(
        Centros_Medico $centro,
        string $status,
        ?User $actor = null,
        ?string $reason = null,
    ): void {
        if (! in_array($status, ['active', 'past_due', 'grace', 'suspended', 'canceled'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Estado de billing no valido.',
            ]);
        }

        $subscription = $this->invoiceService->ensureTenantSubscriptionForCentro($centro);
        $oldStatus = $subscription->status;

        $subscription->status = $status;
        if ($status === 'active') {
            $subscription->grace_until = null;
            $subscription->canceled_at = null;
        }
        if ($status === 'canceled') {
            $subscription->canceled_at = $this->periodService->now();
        }
        $subscription->save();

        $this->billingStateService->syncCentroSnapshotFromTenantSubscription($subscription->fresh());

        $this->auditService->log(
            eventType: 'billing.subscription.status_changed',
            centro: $centro,
            tenantSubscription: $subscription,
            actor: $actor,
            actorType: $actor ? 'user' : 'system',
            reason: $reason ?: 'Cambio manual de estado.',
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $status],
        );
    }

    public function setCancelAtPeriodEnd(
        Centros_Medico $centro,
        bool $enabled,
        ?User $actor = null,
        ?string $reason = null,
    ): void {
        $subscription = $this->invoiceService->ensureTenantSubscriptionForCentro($centro);
        $oldValue = (bool) $subscription->cancel_at_period_end;

        $subscription->cancel_at_period_end = $enabled;
        if (! $enabled) {
            $subscription->canceled_at = null;
        }
        $subscription->save();

        $this->auditService->log(
            eventType: $enabled ? 'billing.subscription.cancel_scheduled' : 'billing.subscription.cancel_resumed',
            centro: $centro,
            tenantSubscription: $subscription,
            actor: $actor,
            actorType: $actor ? 'user' : 'system',
            reason: $reason ?: ($enabled ? 'Cancelacion programada manualmente.' : 'Cancelacion programada revertida.'),
            oldValues: ['cancel_at_period_end' => $oldValue],
            newValues: ['cancel_at_period_end' => $enabled],
        );
    }
}
