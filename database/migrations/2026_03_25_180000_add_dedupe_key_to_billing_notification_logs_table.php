<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_notification_logs', function (Blueprint $table) {
            $table->string('dedupe_key', 64)->nullable()->after('billing_module_subscription_id');
        });

        DB::table('billing_notification_logs')
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    $scheduledDate = date('Y-m-d', strtotime((string) $row->scheduled_for_date));

                    $dedupeKey = hash('sha256', implode('|', [
                        'centro:' . ((int) ($row->centro_id ?? 0)),
                        'invoice:' . ((int) ($row->billing_invoice_id ?? 0)),
                        'tenant_subscription:' . ((int) ($row->billing_tenant_subscription_id ?? 0)),
                        'module_subscription:' . ((int) ($row->billing_module_subscription_id ?? 0)),
                        'event:' . (string) ($row->event_key ?? ''),
                        'channel:' . (string) ($row->channel ?? ''),
                        'recipient:' . strtolower((string) ($row->recipient ?? '')),
                        'date:' . $scheduledDate,
                    ]));

                    DB::table('billing_notification_logs')
                        ->where('id', $row->id)
                        ->update([
                            'dedupe_key' => $dedupeKey,
                        ]);
                }
            });

        Schema::table('billing_notification_logs', function (Blueprint $table) {
            $table->dropUnique('billing_notification_logs_unique');
            $table->unique('dedupe_key', 'billing_notification_logs_dedupe_unique');
        });
    }

    public function down(): void
    {
        Schema::table('billing_notification_logs', function (Blueprint $table) {
            $table->dropUnique('billing_notification_logs_dedupe_unique');
            $table->dropColumn('dedupe_key');
            $table->unique(
                ['event_key', 'channel', 'recipient', 'scheduled_for_date'],
                'billing_notification_logs_unique'
            );
        });
    }
};
