<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_module_subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('billing_module_subscriptions', 'billing_interval')) {
                $table->string('billing_interval', 16)->default('monthly')->after('status');
            }

            if (! Schema::hasColumn('billing_module_subscriptions', 'anchor_at')) {
                $table->timestamp('anchor_at')->nullable()->after('amount');
            }

            if (! Schema::hasColumn('billing_module_subscriptions', 'current_period_starts_at')) {
                $table->timestamp('current_period_starts_at')->nullable()->after('anchor_at');
            }

            if (! Schema::hasColumn('billing_module_subscriptions', 'current_period_ends_at')) {
                $table->timestamp('current_period_ends_at')->nullable()->after('current_period_starts_at');
            }

            if (! Schema::hasColumn('billing_module_subscriptions', 'next_charge_at')) {
                $table->timestamp('next_charge_at')->nullable()->after('current_period_ends_at');
            }

            if (! Schema::hasColumn('billing_module_subscriptions', 'grace_until')) {
                $table->timestamp('grace_until')->nullable()->after('next_charge_at');
            }

            if (! Schema::hasColumn('billing_module_subscriptions', 'cancel_at_period_end')) {
                $table->boolean('cancel_at_period_end')->default(false)->after('grace_until');
            }

            if (! Schema::hasColumn('billing_module_subscriptions', 'dunning_attempts')) {
                $table->unsignedInteger('dunning_attempts')->default(0)->after('cancel_at_period_end');
            }

            if (! Schema::hasColumn('billing_module_subscriptions', 'last_successful_charge_at')) {
                $table->timestamp('last_successful_charge_at')->nullable()->after('dunning_attempts');
            }

            if (! Schema::hasColumn('billing_module_subscriptions', 'last_failed_charge_at')) {
                $table->timestamp('last_failed_charge_at')->nullable()->after('last_successful_charge_at');
            }

            if (! Schema::hasColumn('billing_module_subscriptions', 'last_invoice_id')) {
                $table->foreignId('last_invoice_id')
                    ->nullable()
                    ->after('last_failed_charge_at')
                    ->constrained('billing_invoices')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing_module_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('billing_module_subscriptions', 'last_invoice_id')) {
                $table->dropConstrainedForeignId('last_invoice_id');
            }

            $columns = [
                'last_failed_charge_at',
                'last_successful_charge_at',
                'dunning_attempts',
                'cancel_at_period_end',
                'grace_until',
                'next_charge_at',
                'current_period_ends_at',
                'current_period_starts_at',
                'anchor_at',
                'billing_interval',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('billing_module_subscriptions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
