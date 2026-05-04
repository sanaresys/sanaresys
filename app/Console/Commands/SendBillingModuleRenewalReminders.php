<?php

namespace App\Console\Commands;

use App\Mail\BillingModuleRenewalReminderMail;
use App\Models\BillingModuleReminderLog;
use App\Models\BillingModuleSubscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class SendBillingModuleRenewalReminders extends Command
{
    protected $signature = 'billing:send-module-renewal-reminders';

    protected $description = 'Envia recordatorios de renovacion de modulos por vencer.';

    public function handle(): int
    {
        if (! $this->moduleTablesAvailable()) {
            $this->warn('Facturacion modular no disponible: faltan tablas de billing de modulos.');
            return self::SUCCESS;
        }

        $offsets = array_values(array_unique(array_map(
            'intval',
            (array) config('billing.module_billing.reminder_offsets', [7, 3, 1])
        )));
        $tz = (string) config('billing.module_billing.schedule_timezone', 'America/Tegucigalpa');
        $today = now($tz)->toDateString();

        $subscriptions = BillingModuleSubscription::query()
            ->with(['module', 'centro'])
            ->where('status', 'active')
            ->whereNotNull('renews_at')
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($subscriptions as $subscription) {
            $daysBeforeExpiry = now($tz)->startOfDay()->diffInDays(
                $subscription->renews_at?->copy()->timezone($tz)->startOfDay(),
                false
            );

            if (! in_array((int) $daysBeforeExpiry, $offsets, true)) {
                $skipped++;
                continue;
            }

            $emailRecipients = $this->resolveRecipients($subscription);
            foreach ($emailRecipients as $recipient) {
                $alreadySent = BillingModuleReminderLog::query()
                    ->where('billing_module_subscription_id', $subscription->id)
                    ->where('days_before_expiry', (int) $daysBeforeExpiry)
                    ->where('channel', 'email')
                    ->where('recipient', strtolower($recipient))
                    ->whereDate('scheduled_for_date', $today)
                    ->exists();

                if ($alreadySent) {
                    $skipped++;
                    continue;
                }

                Mail::to($recipient)->queue(
                    new BillingModuleRenewalReminderMail($subscription, (int) $daysBeforeExpiry)
                );

                BillingModuleReminderLog::query()->create([
                    'billing_module_subscription_id' => $subscription->id,
                    'centro_id' => $subscription->centro_id,
                    'billing_module_id' => $subscription->billing_module_id,
                    'days_before_expiry' => (int) $daysBeforeExpiry,
                    'channel' => 'email',
                    'recipient' => strtolower($recipient),
                    'scheduled_for_date' => $today,
                    'sent_at' => now(),
                    'meta' => [
                        'source' => 'scheduled_command',
                    ],
                ]);

                $sent++;
            }

            $inAppAlreadyLogged = BillingModuleReminderLog::query()
                ->where('billing_module_subscription_id', $subscription->id)
                ->where('days_before_expiry', (int) $daysBeforeExpiry)
                ->where('channel', 'in_app')
                ->where('recipient', 'tenant_admin')
                ->whereDate('scheduled_for_date', $today)
                ->exists();

            if (! $inAppAlreadyLogged) {
                BillingModuleReminderLog::query()->create([
                    'billing_module_subscription_id' => $subscription->id,
                    'centro_id' => $subscription->centro_id,
                    'billing_module_id' => $subscription->billing_module_id,
                    'days_before_expiry' => (int) $daysBeforeExpiry,
                    'channel' => 'in_app',
                    'recipient' => 'tenant_admin',
                    'scheduled_for_date' => $today,
                    'sent_at' => now(),
                    'meta' => [
                        'source' => 'scheduled_command',
                    ],
                ]);
            }
        }

        $this->info("Recordatorios enviados: {$sent}");
        $this->info("Omitidos: {$skipped}");

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    protected function resolveRecipients(BillingModuleSubscription $subscription): array
    {
        $recipients = [];

        $ownerEmail = trim((string) ($subscription->centro?->email ?? ''));
        if ($ownerEmail !== '') {
            $recipients[] = strtolower($ownerEmail);
        }

        $tenant = Tenant::query()
            ->where('centro_id', $subscription->centro_id)
            ->first();

        if (! $tenant) {
            return array_values(array_unique($recipients));
        }

        try {
            tenancy()->initialize($tenant);

            $adminEmails = User::query()
                ->role('administrador')
                ->pluck('email')
                ->filter()
                ->map(fn (string $email) => strtolower(trim($email)))
                ->values()
                ->all();

            $recipients = array_merge($recipients, $adminEmails);
        } catch (\Throwable $e) {
            Log::warning('No se pudieron resolver admins tenant para recordatorio de modulo.', [
                'tenant_id' => $tenant->id,
                'centro_id' => $subscription->centro_id,
                'error' => $e->getMessage(),
            ]);
        } finally {
            tenancy()->end();
        }

        return array_values(array_unique(array_filter($recipients)));
    }

    protected function moduleTablesAvailable(): bool
    {
        $schema = Schema::connection('mysql');

        return $schema->hasTable('billing_module_subscriptions')
            && $schema->hasTable('billing_module_reminder_logs')
            && $schema->hasTable('billing_modules');
    }
}
