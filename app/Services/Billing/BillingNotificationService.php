<?php

namespace App\Services\Billing;

use App\Models\BillingInvoice;
use App\Models\BillingModuleSubscription;
use App\Models\BillingNotificationLog;
use App\Models\BillingTenantSubscription;
use App\Models\Centros_Medico;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\BillingStatusNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BillingNotificationService
{
    /**
     * @param array<int, string> $channels
     * @param array<string, mixed> $payload
     */
    public function notifyTenantAdmins(
        Centros_Medico $centro,
        string $eventKey,
        array $channels,
        array $payload,
        ?BillingInvoice $invoice = null,
        ?BillingTenantSubscription $tenantSubscription = null,
        ?BillingModuleSubscription $moduleSubscription = null,
        ?Carbon $scheduledForDate = null,
    ): int {
        $tenant = $centro->tenant ?: Tenant::query()->where('centro_id', $centro->id)->first();
        if (! $tenant) {
            return 0;
        }

        $scheduledForDate ??= now((string) config('billing.engine.timezone', 'America/Tegucigalpa'));
        $channels = collect($channels)
            ->filter(fn (mixed $channel) => in_array($channel, ['database', 'mail'], true))
            ->unique()
            ->values()
            ->all();

        if ($channels === []) {
            return 0;
        }

        $payload['event_key'] = $eventKey;
        $sent = 0;

        $this->runInTenantContext($tenant, function () use (
            $centro,
            $eventKey,
            $channels,
            $payload,
            $invoice,
            $tenantSubscription,
            $moduleSubscription,
            $scheduledForDate,
            &$sent
        ): void {
            $recipients = $this->resolveBillingRecipients();

            foreach ($recipients as $recipient) {
                $recipientKey = strtolower((string) ($recipient->email ?: 'user:' . $recipient->id));

                foreach ($channels as $channel) {
                    $log = $this->reserveNotificationLog(
                        centro: $centro,
                        eventKey: $eventKey,
                        channel: $channel,
                        recipientKey: $recipientKey,
                        scheduledForDate: $scheduledForDate,
                        invoice: $invoice,
                        tenantSubscription: $tenantSubscription,
                        moduleSubscription: $moduleSubscription,
                        userId: $recipient->id,
                    );

                    if (! $log) {
                        continue;
                    }

                    $recipient->notify(new BillingStatusNotification([$channel], $payload));

                    $log->forceFill([
                        'sent_at' => now(),
                    ])->save();

                    $sent++;
                }
            }
        });

        return $sent;
    }

    /**
     * @return Collection<int, User>
     */
    protected function resolveBillingRecipients(): Collection
    {
        return User::query()
            ->get()
            ->filter(function (User $user): bool {
                if ($user->hasRole('root')) {
                    return false;
                }

                return $user->hasRole('administrador') || $user->can('billing.manage');
            })
            ->values();
    }

    protected function runInTenantContext(Tenant $tenant, callable $callback): mixed
    {
        $currentTenant = tenancy()->initialized ? tenancy()->tenant : null;

        if ($currentTenant && $currentTenant->getTenantKey() === $tenant->getTenantKey()) {
            return $callback();
        }

        if ($currentTenant) {
            tenancy()->end();
        }

        tenancy()->initialize($tenant);

        try {
            return $callback();
        } finally {
            tenancy()->end();

            if ($currentTenant) {
                tenancy()->initialize($currentTenant);
            }
        }
    }

    protected function reserveNotificationLog(
        Centros_Medico $centro,
        string $eventKey,
        string $channel,
        string $recipientKey,
        Carbon $scheduledForDate,
        ?BillingInvoice $invoice,
        ?BillingTenantSubscription $tenantSubscription,
        ?BillingModuleSubscription $moduleSubscription,
        int $userId,
    ): ?BillingNotificationLog {
        $dedupeKey = $this->buildDedupeKey(
            centroId: $centro->id,
            invoiceId: $invoice?->id,
            tenantSubscriptionId: $tenantSubscription?->id,
            moduleSubscriptionId: $moduleSubscription?->id,
            eventKey: $eventKey,
            channel: $channel,
            recipientKey: $recipientKey,
            scheduledForDate: $scheduledForDate,
        );

        try {
            return BillingNotificationLog::query()->create([
                'centro_id' => $centro->id,
                'billing_invoice_id' => $invoice?->id,
                'billing_tenant_subscription_id' => $tenantSubscription?->id,
                'billing_module_subscription_id' => $moduleSubscription?->id,
                'dedupe_key' => $dedupeKey,
                'event_key' => $eventKey,
                'channel' => $channel,
                'recipient' => $recipientKey,
                'scheduled_for_date' => $scheduledForDate->toDateString(),
                'sent_at' => null,
                'meta' => [
                    'user_id' => $userId,
                ],
            ]);
        } catch (QueryException $e) {
            if (! $this->isDuplicateKey($e)) {
                throw $e;
            }

            return null;
        }
    }

    protected function buildDedupeKey(
        int|string|null $centroId,
        int|string|null $invoiceId,
        int|string|null $tenantSubscriptionId,
        int|string|null $moduleSubscriptionId,
        string $eventKey,
        string $channel,
        string $recipientKey,
        Carbon $scheduledForDate,
    ): string {
        return hash('sha256', implode('|', [
            'centro:' . ($centroId ?: 0),
            'invoice:' . ($invoiceId ?: 0),
            'tenant_subscription:' . ($tenantSubscriptionId ?: 0),
            'module_subscription:' . ($moduleSubscriptionId ?: 0),
            'event:' . $eventKey,
            'channel:' . $channel,
            'recipient:' . strtolower($recipientKey),
            'date:' . $scheduledForDate->toDateString(),
        ]));
    }

    protected function isDuplicateKey(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? null;
        $driverCode = $e->errorInfo[1] ?? null;

        return $sqlState === '23000' || (int) $driverCode === 1062;
    }
}
