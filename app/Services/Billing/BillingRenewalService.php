<?php

namespace App\Services\Billing;

use App\Models\BillingInvoice;
use App\Models\BillingInvoiceItem;
use App\Models\BillingModuleSubscription;
use App\Models\BillingTenantSubscription;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BillingRenewalService
{
    public function __construct(
        protected BillingInvoiceService $invoiceService,
        protected BillingPeriodService $periodService,
        protected BillingStateService $billingStateService,
        protected BillingAuditService $auditService,
        protected BillingNotificationService $notificationService,
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function processDaily(?Carbon $now = null): array
    {
        $now ??= $this->periodService->now();

        $stats = [
            'upcoming_notifications' => 0,
            'tenant_opened' => 0,
            'tenant_suspended' => 0,
            'tenant_canceled' => 0,
            'module_opened' => 0,
            'module_suspended' => 0,
        ];

        $stats['upcoming_notifications'] += $this->sendUpcomingChargeNotifications($now);

        BillingTenantSubscription::query()
            ->with('centro')
            ->get()
            ->each(function (BillingTenantSubscription $subscription) use ($now, &$stats): void {
                if (! $subscription->centro) {
                    return;
                }

                if ($this->handleScheduledCancellation($subscription, $now)) {
                    $stats['tenant_canceled']++;

                    return;
                }

                if (! $subscription->next_charge_at || $subscription->next_charge_at->startOfDay()->gt($now->copy()->startOfDay())) {
                    return;
                }

                if ($subscription->status === 'active') {
                    $dueModules = $this->dueModuleSubscriptionsForCentro(
                        centroId: $subscription->centro_id,
                        dueDate: $subscription->next_charge_at,
                    );

                    $invoice = $this->invoiceService->createRenewalInvoice(
                        tenantSubscription: $subscription,
                        moduleSubscriptions: $dueModules,
                        kind: 'renewal',
                        includeBasePlan: true,
                        dueAt: $subscription->next_charge_at,
                    );

                    $this->markTenantPastDue($subscription, $invoice, $now);

                    if ($dueModules->isNotEmpty()) {
                        $this->markModulesPastDue($dueModules, $invoice, $now);
                    }

                    $stats['tenant_opened']++;

                    return;
                }

                if (in_array($subscription->status, ['past_due', 'grace'], true)) {
                    if ($this->handleTenantGraceProgress($subscription, $now)) {
                        $stats['tenant_suspended']++;
                    }
                }
            });

        BillingModuleSubscription::query()
            ->with('module')
            ->whereIn('status', ['active', 'past_due', 'grace'])
            ->get()
            ->groupBy('centro_id')
            ->each(function (Collection $subscriptions, int $centroId) use ($now, &$stats): void {
                /** @var BillingTenantSubscription|null $tenantSubscription */
                $tenantSubscription = BillingTenantSubscription::query()
                    ->where('centro_id', $centroId)
                    ->first();

                $baseDueToday = $tenantSubscription?->next_charge_at
                    && $tenantSubscription->next_charge_at->startOfDay()->lte($now->copy()->startOfDay());

                $moduleOnlyDue = $subscriptions
                    ->filter(fn (BillingModuleSubscription $subscription) => $subscription->next_charge_at
                        && $subscription->next_charge_at->startOfDay()->lte($now->copy()->startOfDay()))
                    ->filter(fn (BillingModuleSubscription $subscription) => ! $baseDueToday
                        || ! $tenantSubscription
                        || ! $tenantSubscription->next_charge_at
                        || $subscription->next_charge_at?->toDateString() !== $tenantSubscription->next_charge_at?->toDateString());

                if ($moduleOnlyDue->isEmpty() || ! $tenantSubscription) {
                    return;
                }

                $dueTimestamp = $moduleOnlyDue->min(
                    fn (BillingModuleSubscription $subscription) => $subscription->next_charge_at?->timestamp ?? PHP_INT_MAX
                );

                $invoice = $this->invoiceService->createRenewalInvoice(
                    tenantSubscription: $tenantSubscription,
                    moduleSubscriptions: $moduleOnlyDue->values(),
                    kind: 'renewal',
                    includeBasePlan: false,
                    dueAt: $dueTimestamp && $dueTimestamp !== PHP_INT_MAX
                        ? Carbon::createFromTimestamp($dueTimestamp)
                        : $now,
                );

                $this->markModulesPastDue($moduleOnlyDue, $invoice, $now);
                $stats['module_opened']++;
            });

        BillingModuleSubscription::query()
            ->whereIn('status', ['past_due', 'grace'])
            ->get()
            ->each(function (BillingModuleSubscription $subscription) use ($now, &$stats): void {
                if ($this->handleModuleGraceProgress($subscription, $now)) {
                    $stats['module_suspended']++;
                }
            });

        return $stats;
    }

    public function sendUpcomingChargeNotifications(?Carbon $now = null): int
    {
        $now ??= $this->periodService->now();
        $tomorrow = $now->copy()->addDay()->toDateString();
        $count = 0;

        BillingTenantSubscription::query()
            ->with('centro')
            ->where('status', 'active')
            ->whereDate('next_charge_at', $tomorrow)
            ->get()
            ->each(function (BillingTenantSubscription $subscription) use (&$count): void {
                if (! $subscription->centro) {
                    return;
                }

                $count += $this->notificationService->notifyTenantAdmins(
                    centro: $subscription->centro,
                    eventKey: 'billing.charge_tomorrow',
                    channels: ['database'],
                    payload: [
                        'title' => 'Cobro programado para manana',
                        'body' => 'Tu siguiente renovacion vence manana. Revisa tu panel de billing para evitar bloqueos.',
                        'level' => 'info',
                        'action_url' => '/billing',
                        'action_label' => 'Ver billing',
                    ],
                    tenantSubscription: $subscription,
                );
            });

        return $count;
    }

    /**
     * @return EloquentCollection<int, BillingModuleSubscription>
     */
    protected function dueModuleSubscriptionsForCentro(int $centroId, Carbon $dueDate): EloquentCollection
    {
        return BillingModuleSubscription::query()
            ->where('centro_id', $centroId)
            ->whereIn('status', ['active', 'past_due', 'grace'])
            ->whereDate('next_charge_at', $dueDate->toDateString())
            ->get();
    }

    protected function handleScheduledCancellation(BillingTenantSubscription $subscription, Carbon $now): bool
    {
        if (! $subscription->cancel_at_period_end
            || ! $subscription->current_period_ends_at
            || $now->lt($subscription->current_period_ends_at)) {
            return false;
        }

        $subscription->forceFill([
            'status' => 'canceled',
            'canceled_at' => $now,
            'next_charge_at' => null,
        ])->save();

        $this->billingStateService->syncCentroSnapshotFromTenantSubscription($subscription->fresh());

        $this->auditService->log(
            eventType: 'billing.subscription.canceled',
            centro: $subscription->centro,
            tenantSubscription: $subscription,
            reason: 'Cancelacion al final del periodo ejecutada automaticamente.',
        );

        return true;
    }

    protected function markTenantPastDue(BillingTenantSubscription $subscription, BillingInvoice $invoice, Carbon $now): void
    {
        if ($this->processedToday($subscription->meta, $now)) {
            return;
        }

        $subscription->forceFill([
            'status' => 'past_due',
            'grace_until' => $invoice->grace_until,
            'last_failed_charge_at' => $now,
            'dunning_attempts' => max(1, (int) $subscription->dunning_attempts),
            'last_invoice_id' => $invoice->id,
            'meta' => $this->markProcessedToday($subscription->meta, $now),
        ])->save();

        $this->billingStateService->syncCentroSnapshotFromTenantSubscription($subscription->fresh());

        $this->notificationService->notifyTenantAdmins(
            centro: $subscription->centro,
            eventKey: 'billing.invoice_opened',
            channels: ['database'],
            payload: [
                'title' => 'Factura pendiente',
                'body' => 'Tu renovacion vencio y ya puedes pagarla desde el portal de billing.',
                'level' => 'warning',
                'action_url' => '/billing',
                'action_label' => 'Pagar ahora',
            ],
            invoice: $invoice,
            tenantSubscription: $subscription,
        );
    }

    protected function markModulesPastDue(Collection $subscriptions, BillingInvoice $invoice, Carbon $now): void
    {
        foreach ($subscriptions as $subscription) {
            if (! $subscription instanceof BillingModuleSubscription || $this->processedToday($subscription->meta, $now)) {
                continue;
            }

            $subscription->forceFill([
                'status' => 'past_due',
                'grace_until' => $invoice->grace_until,
                'last_failed_charge_at' => $now,
                'dunning_attempts' => max(1, (int) $subscription->dunning_attempts),
                'last_invoice_id' => $invoice->id,
                'meta' => $this->markProcessedToday($subscription->meta, $now),
            ])->save();

            $this->auditService->log(
                eventType: 'billing.module.past_due',
                centro: $subscription->centro,
                invoice: $invoice,
                moduleSubscription: $subscription,
                reason: 'Modulo paso a pendiente por falta de pago.',
            );
        }
    }

    protected function handleTenantGraceProgress(BillingTenantSubscription $subscription, Carbon $now): bool
    {
        if ($subscription->grace_until && $now->gt($subscription->grace_until)) {
            $subscription->forceFill([
                'status' => 'suspended',
                'meta' => $this->markProcessedToday($subscription->meta, $now),
            ])->save();

            $this->billingStateService->syncCentroSnapshotFromTenantSubscription($subscription->fresh());

            $this->notificationService->notifyTenantAdmins(
                centro: $subscription->centro,
                eventKey: 'billing.account_suspended',
                channels: ['database', 'mail'],
                payload: [
                    'title' => 'Cuenta suspendida',
                    'body' => 'El periodo de gracia termino y el tenant fue suspendido por impago.',
                    'level' => 'danger',
                    'action_url' => '/billing',
                    'action_label' => 'Reactivar',
                ],
                invoice: $subscription->lastInvoice,
                tenantSubscription: $subscription,
            );

            return true;
        }

        if ($this->processedToday($subscription->meta, $now)) {
            return false;
        }

        $attempts = min($this->periodService->maxDunningAttempts(), ((int) $subscription->dunning_attempts) + 1);
        $subscription->forceFill([
            'status' => 'grace',
            'dunning_attempts' => $attempts,
            'last_failed_charge_at' => $now,
            'meta' => $this->markProcessedToday($subscription->meta, $now),
        ])->save();

        $channels = $subscription->grace_until && $now->copy()->addDay()->gt($subscription->grace_until)
            ? ['database', 'mail']
            : ['database'];
        $eventKey = $channels === ['database', 'mail']
            ? 'billing.before_suspension'
            : 'billing.payment_overdue';

        $this->notificationService->notifyTenantAdmins(
            centro: $subscription->centro,
            eventKey: $eventKey,
            channels: $channels,
            payload: [
                'title' => $channels === ['database', 'mail'] ? 'Suspension proxima' : 'Pago pendiente',
                'body' => $channels === ['database', 'mail']
                    ? 'Tu cuenta sera suspendida si no completas el pago hoy.'
                    : 'Tu cuenta sigue en gracia. Realiza el pago para evitar suspension.',
                'level' => 'warning',
                'action_url' => '/billing',
                'action_label' => 'Pagar ahora',
            ],
            invoice: $subscription->lastInvoice,
            tenantSubscription: $subscription,
        );

        return false;
    }

    protected function handleModuleGraceProgress(BillingModuleSubscription $subscription, Carbon $now): bool
    {
        if ($subscription->grace_until && $now->gt($subscription->grace_until)) {
            $subscription->forceFill([
                'status' => 'suspended',
                'meta' => $this->markProcessedToday($subscription->meta, $now),
            ])->save();

            return true;
        }

        if ($this->processedToday($subscription->meta, $now)) {
            return false;
        }

        $subscription->forceFill([
            'status' => 'grace',
            'dunning_attempts' => min($this->periodService->maxDunningAttempts(), ((int) $subscription->dunning_attempts) + 1),
            'last_failed_charge_at' => $now,
            'meta' => $this->markProcessedToday($subscription->meta, $now),
        ])->save();

        return false;
    }

    /**
     * @param array<string, mixed>|null $meta
     */
    protected function processedToday(?array $meta, Carbon $now): bool
    {
        return (string) ($meta['last_dunning_processed_on'] ?? '') === $now->toDateString();
    }

    /**
     * @param array<string, mixed>|null $meta
     * @return array<string, mixed>
     */
    protected function markProcessedToday(?array $meta, Carbon $now): array
    {
        $meta = $meta ?: [];
        $meta['last_dunning_processed_on'] = $now->toDateString();

        return $meta;
    }
}
