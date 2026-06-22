<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_registration_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('clinic_registration_requests', 'plan_code')) {
                $table->string('plan_code', 32)->nullable()->after('slug');
            }

            if (! Schema::hasColumn('clinic_registration_requests', 'payment_status')) {
                $table->string('payment_status', 32)->default('pending')->after('status');
            }

            if (! Schema::hasColumn('clinic_registration_requests', 'paypal_subscription_id')) {
                $table->string('paypal_subscription_id')->nullable()->index()->after('tenant_id');
            }

            if (! Schema::hasColumn('clinic_registration_requests', 'paypal_plan_id')) {
                $table->string('paypal_plan_id')->nullable()->after('paypal_subscription_id');
            }

            if (! Schema::hasColumn('clinic_registration_requests', 'payment_approved_at')) {
                $table->timestamp('payment_approved_at')->nullable()->after('verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinic_registration_requests', function (Blueprint $table) {
            if (Schema::hasColumn('clinic_registration_requests', 'payment_approved_at')) {
                $table->dropColumn('payment_approved_at');
            }

            if (Schema::hasColumn('clinic_registration_requests', 'paypal_plan_id')) {
                $table->dropColumn('paypal_plan_id');
            }

            if (Schema::hasColumn('clinic_registration_requests', 'paypal_subscription_id')) {
                $table->dropColumn('paypal_subscription_id');
            }

            if (Schema::hasColumn('clinic_registration_requests', 'payment_status')) {
                $table->dropColumn('payment_status');
            }

            if (Schema::hasColumn('clinic_registration_requests', 'plan_code')) {
                $table->dropColumn('plan_code');
            }
        });
    }
};
